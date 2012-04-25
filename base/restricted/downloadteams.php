<?php
require_once(__DIR__ . '/../../class/common.php');
require_once(__DIR__ . '/../../class/lib/parsecsv.lib.php');

if (isset($_REQUEST['contest_id'])) {
  $contest_id = $_REQUEST['contest_id'];
  $teams = DBManager::getTeams($contest_id);
  $csv = new parseCSV();
  $csv->output('teams.csv', $teams, $k_teams_fields, ',');
}

?>