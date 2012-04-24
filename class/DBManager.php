<?php
require_once(__DIR__ . '/DBConstants.php');

class DBManager {
  private static $singleton;

  private function __construct() {
  }
  
  public function __clone() {
    trigger_error('Clone is not allowed.', E_USER_ERROR);
  }
  
  public function __wakeup() {
    trigger_error('Deserialization is not allowed.', E_USER_ERROR);
  }
  
  private static function querySelectUnique($sql) {
    $dbh = self::singleton();
    $stmt = $dbh->prepare($sql);
    $stmt->execute(array_slice(func_get_args(), 1));
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    if (count($res) == 1) {
      return $res[0];
    }
    return false;
  }
  
  private static function querySelect($sql) {
    $dbh = self::singleton();
    $stmt = $dbh->prepare($sql);
    $stmt->execute(array_slice(func_get_args(), 1));
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    return $res;
  }
  
  private static function queryInsert($sql) {
    $dbh = self::singleton();
    $stmt = $dbh->prepare($sql);
    $stmt->execute(array_slice(func_get_args(), 1));
    $res = $dbh->lastInsertID();
    $stmt->closeCursor();
    return $res;
  }
  
  private static function queryUpdate($sql) {
    $dbh = self::singleton();
    $stmt = $dbh->prepare($sql);
    $stmt->execute(array_slice(func_get_args(), 1));
    $res = $stmt->rowCount();
    $stmt->closeCursor();
    return $res;
  }
  
  private static function singleton() {
    if (!isset(self::$singleton)) {
      try {
        self::$singleton = new PDO('mysql:host=localhost;dbname=contest', 'contest', 'proco', array(PDO::ATTR_PERSISTENT => true));
        self::$singleton->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      }
      catch (PDOException $e) {
        die();
      }
    }
    return self::$singleton;
  }
  
  public static function transact($func) {
    $dbh = self::singleton();
    try {
      $dbh->beginTransaction();
      $res = $func();
      $dbh->commit();
    }
    catch (Exception $e) {
      $dbh->rollBack();
      $res = false;
    }
    return $res;
  }
  
  // Begin definition of procedures
  public static function getContestTypes() {
    $contest_types = array();
    foreach (glob(__DIR__ . '/modules/contest.*') as $contest_full_type) {
      array_push($contest_types, substr($contest_full_type, strrpos($contest_full_type, 'contest.') + strlen('contest.')));
    }
    return $contest_types;
  }

  public static function getCurrentContest() {
    return self::querySelectUnique('select contest_id, contest_type, contest_name, time_start, time_length, metadata, status from globals join contests on globals.curr_contest_id = contests.contest_id');
  }
  
  public static function login($username, $password) {
    return self::querySelectUnique('select team_id, alias, division_id, contest_id from globals join contests on globals.curr_contest_id = contests.contest_id join contests_divisions using (contest_id) join tags using (tag) join divisions using (division_id) join teams using (division_id, tag) where username = ?, password = ?', $username, $password);
  }
  
  public static function getContest($contest_id) {
    global $k_contest_active;
    return self::querySelectUnique('select contest_id, contest_type, contest_name, time_start, time_length, tag, metadata, status from contests where contest_id = ? and status = ?', $contest_id, $k_contest_active);
  }
  
  public static function getContestsOfType($contest_type) {
    global $k_contest_active;
    return self::querySelect('select contest_id, contest_name from contests where contest_type = ? and status = ? order by contest_id desc', $contest_type, $k_contest_active);
  }
  
  public static function addContest($contest_type, $contest_name, $time_start, $time_length, $tag, $metadata) {
    global $k_contest_active;
    self::queryUpdate('insert into tags set tag = ? on duplicate key update tag=tag', $tag);
    return self::queryInsert('insert into contests set contest_type = ?, contest_name = ?, time_start = ?, time_length = ?, tag = ?, metadata = ?, status = ?', $contest_type, $contest_name, $time_start, $time_length, $tag, $metadata, $k_contest_active);
  }
  
  public static function modifyContest($contest_id, $contest_type, $contest_name, $time_start, $time_length, $tag, $metadata) {
    global $k_contest_active;
    self::queryUpdate('insert into tags set tag = ? on duplicate key update tag=tag', $tag);
    return self::queryUpdate('update contests set contest_type = ?, contest_name = ?, time_start = ?, time_length = ?, tag = ?, metadata = ? where contest_id = ? and status = ?', $contest_type, $contest_name, $time_start, $time_length, $tag, $metadata, $contest_id, $k_contest_active);
  }
  
  public static function cloneContest($contest_id, $contest_name) {
    $contest = self::getContest($contest_id);
    if ($contest) {
      return self::queryInsert('insert into contests set contest_type = ?, contest_name = ?, time_start = ?, time_length = ?, tag = ?, metadata = ?, status = ?', $contest['contest_type'], $contest_name, $contest['time_start'], $contest['time_length'], $contest['tag'], $contest['metadata'], $contest['status']);
    }
    return false;
  }  
  
  public static function deleteContest($contest_id) {
    global $k_contest_active;
    global $k_contest_inactive;
    self::queryUpdate('update globals set curr_contest_id = 0 where curr_contest_id = ?', $contest_id);
    return self::queryUpdate('update contests set status = ? where contest_id = ? and status = ?', $k_contest_inactive, $contest_id, $k_contest_active);
  }
  
  public static function setCurrentContest($contest_id) {
    return self::queryUpdate('update globals set curr_contest_id = ?', $contest_id);
  }
  
  public static function getDivisions() {
    return self::querySelect('select division_id, name from divisions order by name asc');
  }
  public static function addDivision($name) {
    return self::queryInsert('insert into divisions set name = ?', $name);
  }
  
  public static function linkContestsDivisions($contest_id, $division_ids) {
    $dbh = self::singleton();
    try {
      $dbh->beginTransaction();
      self::queryUpdate('delete from contests_divisions where contest_id = ?', $contest_id);
      $res = self::queryUpdate('insert into contests_divisions (contest_id, division_id) values ' . implode(',', array_map(function($division_id) use ($contest_id) { return '(' . $contest_id . ',' . $division_id . ')'; }, $division_ids)));
      $dbh->commit();
    }
    catch (Exception $e) {
      $dbh->rollBack();
      $res = false;
    }
    return $res;
  }
  
  public static function getContestsDivisions($contest_id) {
    $divisions = self::querySelect('select division_id from contests_divisions where contest_id = ?', $contest_id);
    return array_map(function ($division) { return $division['division_id']; }, $divisions);
  }
  
  public static function modifyDivision($division_id, $name) {
    return self::queryUpdate('update divisions set name = ? where division_id = ?', $name, $division_id);
  }
  
  
}
?>