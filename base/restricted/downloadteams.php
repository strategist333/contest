<?php
require_once(__DIR__ . '/../../class/common.php');
require_once(__DIR__ . '/../../class/lib/parsecsv.lib.php');

if (isset($_REQUEST['contest_id'])) {
  $contest_id = $_REQUEST['contest_id'];
  $fields = array('team_id', 'username', 'password', 'alias', 'division_name');
  $teams = DBManager::getTeams($contest_id);
  $csv = new parseCSV();
  $csv->output('teams.csv', $teams, $fields, ',');
}

?>