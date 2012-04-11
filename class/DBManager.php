<?php
require_once ('DBConstants.php');

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
  
  
  private static function querySelect($sql, $params) {
    $dbh = self::singleton();
    $stmt = $dbh->prepare($sql);
    $stmt->execute($params);
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    return $res;
  }
  
  private static function queryUpdate($sql, $params) {
    $dbh = self::singleton();
    $stmt = $dbh->prepare($sql);
    $stmt->execute($params);
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
        print "DB Error.\n";
        die();
      }
    }
    return self::$singleton;
  }
  
  public static function exec($func) {
    $dbh = self::singleton();
    try {
      $dbh->beginTransaction();
      $res = self::$func(array_slice(func_get_args(), 1));
      $dbh->commit();
    }
    catch (Exception $e) {
      $dbh->rollBack();
      $res = false;
    }
    return $res;
  }
  
  // Begin definition of procedures
  private static function getCurrentContestID($params) {
    $res = self::querySelect('select curr_contest_id from globals', $params);
    return $res[0]['curr_contest_id'];
  }
  
}
?>