<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'common.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'session.php');

if (!isset($_SESSION['login']) || !$g_curr_contest || $_SESSION['login']['contest_id'] != $g_curr_contest['contest_id']) {
  unset($_SESSION['login']);
  header('Location: login.php');
}

$json = json_decode(file_get_contents('php://input'), true);

$ret = array('success' => false);
if (isset($json['action'])) {
  $action = $json['action'];
  
  try {
    $handler = load(currentContestType(), 'TeamActionHandler');
    if (method_exists($handler, $action)) { 
      $ret['success'] = true;
      call_user_func_array(array($handler, $action), array($json, &$ret));
    }
  } catch (Exception $e) {
    $ret['success'] = false;
    $ret['error'] = $e->getMessage();
  }
}

echo json_encode($ret);
?>
