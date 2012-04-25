<?php
require_once(__DIR__ . '/../class/common.php');
session_start();

load(currentContestType(), 'TeamLogin')->render();
?>
