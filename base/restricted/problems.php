<?php
require_once(__DIR__ . '/../../class/common.php');

load(currentContestType(), 'JudgeConfigProblem')->render();
?>
