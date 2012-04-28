<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'common.php');
session_start();

load(currentContestType(), 'TeamLogin')->render();
?>
