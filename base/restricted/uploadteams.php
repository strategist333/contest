<?php
require_once(__DIR__ . '/../../class/common.php');
require_once(__DIR__ . '/../../class/lib/parsecsv.lib.php');
?>

<html>
<head>
<title>Upload teams</title>
</head>
<body>

<?php
$success = false;
$body_html = "";
if (isset($_FILES['upload_teams_file']) && !$_FILES['upload_teams_file']['error'] && isset($_REQUEST['contest_id'])) {
  $contest_id = $_REQUEST['contest_id'];
  $csv = new parseCSV();
  $csv->parse($_FILES['upload_teams_file']['tmp_name']);

  $teams = array();
  foreach ($csv->data as $key => $csv_row) {
    $row = array();
    foreach ($csv_row as $field_utr => $value_utr) {
      $field = trim($field_utr);
      $value = trim($value_utr);
      if (in_array($field, $k_teams_fields) && $value) {
        $row[$field] = $value;
      }
    }
    if (isset($row['username']) && isset($row['password']) && isset($row['division_name'])) {
      array_push($teams, $row);
    }
  }
  print count($teams) . ' teams found.. ';
  $ret = DBManager::setTeams($contest_id, $teams);
  if ($ret['success']) {
    print 'success.<br />' . $ret['update'] . ' teams updated, ' . $ret['insert'] . ' teams inserted, ' . $ret['delete'] . ' teams deleted';
    $success = true;
  }
  else {
    print 'failed.<br />' . $ret['error'];
  }
}
else {
  print 'Invalid parameters';
}

if (isset($_FILES['upload_teams_file']) && $_FILES['upload_teams_file']['tmp_name']) {
  unlink($_FILES['upload_teams_file']['tmp_name']);
}
?>
</body>
</html>