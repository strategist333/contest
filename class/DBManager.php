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
      }
      catch (PDOException $e) {
        die();
      }
    }
    return self::$singleton;
  }
  
  public static function exec($func) {
    $dbh = self::singleton();
    try {
      $dbh->beginTransaction();
      $res = self::$func();
      $dbh->commit();
    }
    catch (Exception $e) {
      $dbh->rollBack();
      $res = false;
    }
    return $res;
  }
  
  // Begin definition of procedures
  public static function getCurrentContest() {
    return self::querySelectUnique('select contest_id, contest_type, contest_name, time_start, time_length, metadata, status from contests, globals where contests.contest_id = globals.curr_contest_id');
  }
  
}
?>