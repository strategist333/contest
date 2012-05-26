<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'parsecsv.lib.php');

class JudgeLoadHandler {
  
  protected function writeDownloadHeader($filename, $length) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . $length);
    ob_clean();
    flush();
  }
  
  public function download_teams() {
    global $k_teams_fields;
    if (isset($_REQUEST['contest_id'])) {
      $contest_id = $_REQUEST['contest_id'];
      $teams = DBManager::getTeams($contest_id);
      $csv = new parseCSV();
      $csv->output('teams.csv', $teams, $k_teams_fields, ',');
    }
  }
  
  public function upload_teams() {
    global $k_teams_fields;
    if (isset($_FILES['upload_file']) && $_FILES['upload_file']['size'] > 0) {
      if (isset($_REQUEST['contest_id'])) {
        $contest_id = $_REQUEST['contest_id'];
        $csv = new parseCSV();
        $csv->parse($_FILES['upload_file']['tmp_name']);

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
          print 'success.<br />' . $ret['update'] . ' teams updated, ' . $ret['insert'] . ' teams inserted, ' . $ret['delete'] . ' teams deleted<br />';
          print 'Automatically refreshing...';
          print '<script type="text/javascript">window.top.location = window.top.location;</script>';
        }
        else {
          print 'failed.<br />' . $ret['error'];
        }
      }
      unlink($_FILES['upload_file']['tmp_name']);
    }
  }
}
?>
