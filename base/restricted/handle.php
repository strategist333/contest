<?php
require_once(__DIR__ . '/../../class/common.php');

$json = json_decode(file_get_contents('php://input'), true);

$ret = array('success' => false);
if (isset($json['action'])) {
  $action = $json['action'];  

  $contest_type = $g_curr_contest ? $g_curr_contest['contest_type'] : 'default';
  try {
    $ret['success'] = true;
    call_user_func_array(array(load($contest_type, 'JudgeActionHandler'), $action), array($json, &$ret));
  } catch (Exception $e) {
    $ret['success'] = false;
    $ret['error'] = $e->getMessage();
    
  }
}

echo json_encode($ret);
?>
