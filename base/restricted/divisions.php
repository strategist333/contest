<?php
require_once(__DIR__ . '/../../class/common.php');

$contest_type = $g_curr_contest ? $g_curr_contest['contest_type'] : 'default';
if (isset($_REQUEST['contest_type'])) {
  $contest_type = $_REQUEST['contest_type'];
}
$contest_id = $g_curr_contest ? $g_curr_contest['contest_id'] : 0;
if (isset($_REQUEST['contest_id'])) {
  $contest_id = $_REQUEST['contest_id'];
}

load(array('contest_type' => $contest_type), 'JudgeConfigDivision', $contest_type, $contest_id)->render();
?>
