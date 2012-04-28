<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'common.php');

if (isset($_REQUEST['action'])) {
  $action = $_REQUEST['action'];  
  
  try {
    $handler = load(currentContestType(), 'JudgeLoadHandler');
    if (method_exists($handler, $action)) { 
      call_user_func(array($handler, $action));
    }
  } catch (Exception $e) {
  }
}
?>
