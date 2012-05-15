<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'common.php');

$json = json_decode(file_get_contents('php://input'), true);

$ret = array('success' => false);
if (isset($json['action'])) {
  $action = $json['action'];  
  
  try {
    $handler = load(currentContestType(), 'JudgeActionHandler');
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
