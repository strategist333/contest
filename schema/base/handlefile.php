<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'common.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'session.php');

if (!isset($_SESSION['login']) || !$g_curr_contest || $_SESSION['login']['contest_id'] != $g_curr_contest['contest_id']) {
  unset($_SESSION['login']);
  header('Location: login.php');
}

if (isset($_REQUEST['action'])) {
  $action = $_REQUEST['action'];  
  
  try {
    $handler = load(currentContestType(), 'TeamLoadHandler');
    if (method_exists($handler, $action)) { 
      call_user_func(array($handler, $action));
    }
  } catch (Exception $e) {
  }
}
?>
