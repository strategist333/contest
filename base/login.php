<?php
require_once(__DIR__ . '/../class/common.php');
session_start();

load($g_curr_contest, 'TeamLogin')->render();
?>
