<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class TeamLoadHandler {
  
  protected function getSubmissionMetadata() {    
    $filename = $_FILES['team_file']['name'];
    $path_parts = pathinfo($filename); 
    $extension = $path_parts['extension'];
    return array('extension' => $extension);
  }

  public function upload_submission() {
    $ret = array('success' => false);
    if (isset($_FILES['team_file']) && $_FILES['team_file']['size'] > 0) {
      $team_id = $_SESSION['login']['team_id'];
      $division_id = $_SESSION['login']['division_id'];
      $contest_id = $_SESSION['login']['contest_id'];
      $tmpname = $_FILES['team_file']['tmp_name'];
      $filename = $_FILES['team_file']['name'];
      $path_parts = pathinfo($filename); 
      $filebase = $path_parts['filename'];
      
      $file = fopen($tmpname, 'r');
      $payload = fread($file, filesize($tmpname));  
      fclose($file);
      unlink($tmpname);
      
      $metadata = $this->getSubmissionMetadata();
      
      $info = DBManager::addRun($team_id, $division_id, $contest_id, $filebase, $payload, json_encode($metadata));
      if ($info['success']) {
        $ret['success'] = true;
      }
      else {
        $ret['error'] = $info['error'];
      }
    }
    else {
      $ret['error'] = 'Upload failed';
    }
    echo json_encode($ret);
  }
}
?>
