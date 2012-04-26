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
  
  public static function getProblemTypes($curr_contest_type) {
    $contest_types = array($curr_contest_type);
    if ($curr_contest_type != 'default') {
      array_push($contest_types, 'default');
    }
    $problem_types = array();
    foreach ($contest_types as $contest_type) {
      foreach (glob(__DIR__ . '/modules/contest.' . $contest_type . '/problem.*') as $problem_full_type) {
        array_push($problem_types, substr($problem_full_type, strrpos($problem_full_type, 'problem.') + strlen('problem.')));
      }
    }
    return $problem_types;
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
    return self::querySelect('select contest_id, contest_name, tag from contests where contest_type = ? and status = ? order by contest_id desc', $contest_type, $k_contest_active);
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
    return self::querySelect('select division_id, division_name from divisions order by division_name asc');
  }
  public static function addDivision($division_name) {
    return self::queryInsert('insert into divisions set division_name = ?', $division_name);
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
  
  public static function getContestDivisions($contest_id) {
    return self::querySelect('select division_id, division_name from contests_divisions join divisions using (division_id) where contest_id = ? order by division_name asc', $contest_id);
  }
  
  public static function modifyDivision($division_id, $division_name) {
    return self::queryUpdate('update divisions set division_name = ? where division_id = ?', $division_name, $division_id);
  }
  
  public static function getTeams($contest_id) {
    return self::querySelect('select team_id, username, password, alias, division_name from contests join contests_divisions using (contest_id) join divisions using (division_id) join teams using (tag, division_id) where contest_id = ?', $contest_id);
  }
  
  public static function setTeams($contest_id, $teams) {
    $dbh = self::singleton();
    try {
      $dbh->beginTransaction();
      $tags = self::querySelectUnique('select tag from contests where contest_id = ?', $contest_id);
      $tag = $tags['tag'];
      
      $divisions = self::querySelect('select division_id, division_name from divisions join contests_divisions using (division_id) where contest_id = ?', $contest_id);
      $division_map = array();
      foreach ($divisions as $division) {
        $division_map[$division['division_name']] = $division['division_id'];
      }
      
      $valid_team_ids = array();
      
      $update_stmt = $dbh->prepare('update teams set username = ?, password = ?, alias = ?, tag = ?, division_id = ? where team_id = ?');
      $insert_stmt = $dbh->prepare('insert into teams set username = ?, password = ?, alias = ?, tag = ?, division_id = ?');
      
      $update_count = 0;
      $insert_count = 0;
      
      $success = true;
      foreach ($teams as $team) {
        $username = $team['username'];
        $password = $team['password'];
        $alias = $team['alias'];
        if (!isset($division_map[$team['division_name']])) {
          throw new Exception('Invalid division ' . $team['division_name']);
        }
        $division_id = $division_map[$team['division_name']];
        if (isset($team['team_id'])) {
          $team_id = $team['team_id'];
          $update_stmt->execute(array($username, $password, $alias, $tag, $division_id, $team_id));
          $update_count += $update_stmt->rowCount();
          array_push($valid_team_ids, $team_id);
        }
        else {
          $insert_stmt->execute(array($username, $password, $alias, $tag, $division_id));
          $insert_count += $insert_stmt->rowCount();
          $team_id = $dbh->lastInsertID();
          array_push($valid_team_ids, $team_id);
        }
      }
      $update_stmt->closeCursor();
      $insert_stmt->closeCursor();
      
      $delete_stmt = $dbh->prepare('delete from teams where division_id in (select division_id from contests_divisions where contest_id = ?) and tag = (select tag from contests where contest_id = ?) and team_id not in (' . implode(',', $valid_team_ids) . ')');
      $delete_stmt->execute(array($contest_id, $contest_id));
      $delete_count = $delete_stmt->rowCount();
      
      $dbh->commit();
      $res = array('success' => true, 'update' => $update_count, 'insert' => $insert_count, 'delete' => $delete_count);
    }
    catch (Exception $e) {
      $dbh->rollBack();
      $res = array('success' => false, 'error' => $e->getMessage());
    }
    return $res;
  }
  
  public static function getContestTeams($contest_id) {
    return self::querySelect('select team_id, tag, username, password, alias, division_name from teams join tags using (tag) join divisions using (division_id) join contests_divisions using (division_id) join contests using (tag, contest_id) where contest_id = ? order by division_name asc, username asc', $contest_id);
  }
  
  public static function getContestProblems($contest_id) {
    return self::querySelect('select problem_id, problem_type, title, status, division_id, url, alias, display_alias, metadata, division_metadata from problems join contests_divisions_problems using (problem_id) where contest_id = ? order by order_seq asc, division_id asc', $contest_id);
  }
  
  public static function modifyProblem($problem_id, $key, $value) {
    return self::queryUpdate('update problems set ' . $key . ' = ? where problem_id = ?', $value, $problem_id);
  }
  
  public static function modifyContestDivisionProblem($problem_id, $division_id, $contest_id, $key, $value) {
    return self::queryUpdate('update contests_divisions_problems set ' . $key . ' = ? where problem_id = ? and division_id = ? and contest_id = ?', $value, $problem_id, $division_id, $contest_id);
  }
  
  public static function getContestDivisionProblem($problem_id, $division_id, $contest_id) {
    return self::querySelectUnique('select problem_id, problem_type, title, status, division_id, url, alias, display_alias, metadata, division_metadata from problems join contests_divisions_problems using (problem_id) where contest_id = ? and division_id = ? and problem_id = ?', $contest_id, $division_id, $problem_id);
  }
  
  public static function addContestDivisionProblem($problem_id, $division_id, $contest_id) {
    return self::queryUpdate('insert ignore into contests_divisions_problems set problem_id = ?, division_id = ?, contest_id = ?, division_metadata = ?', $problem_id, $division_id, $contest_id, '{}');
  }
  
  public static function removeContestDivisionProblem($problem_id, $division_id, $contest_id) {
    return self::queryUpdate('delete from contests_divisions_problems where problem_id = ? and division_id = ? and contest_id = ?', $problem_id, $division_id, $contest_id);
  }
  
  public static function addProblem() {
    global $k_problem_active;
    $dbh = self::singleton();
    try {
      $dbh->beginTransaction();
      $res = self::queryInsert('insert into problems set status = ?, metadata = ?, order_seq = (select next_order_seq from globals)', $k_problem_active, '{}');
      self::queryUpdate('update globals set next_order_seq = next_order_seq + 1');
      $dbh->commit();
    }
    catch (Exception $e) {
      $dbh->rollBack();
      $res = false;
    }
    return $res;
    
  }
  
  
}
?>