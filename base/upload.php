<?php
require_once(__DIR__ . '/../class/common.php');
session_start();

if (!isset($_SESSION['login']) || !$g_curr_contest || $_SESSION['login']['contest_id'] != $g_curr_contest['contest_id']) {
  unset($_SESSION['login']);
  header('Location: login.php');
}

load(currentContestType(), 'TeamUpload', $_SESSION['login']['team_id'], $_SESSION['login']['division_id'], $_SESSION['login']['contest_id'])->render();
?>
