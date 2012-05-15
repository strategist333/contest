<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'common.php');


$contest_type = currentContestType();
if (isset($_REQUEST['contest_type'])) {
  $contest_type = $_REQUEST['contest_type'];
}
$contest_id = $g_curr_contest ? $g_curr_contest['contest_id'] : 0;
if (isset($_REQUEST['contest_id'])) {
  $contest_id = $_REQUEST['contest_id'];
}

load($contest_type, 'JudgeConfigContest', $contest_type, $contest_id)->render();
?>
