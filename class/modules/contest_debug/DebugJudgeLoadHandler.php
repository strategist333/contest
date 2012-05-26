<?php

require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class DebugJudgeLoadHandler extends JudgeLoadHandler {

  public function view_submission() {
    if (isset($_REQUEST['contest_id']) && isset($_REQUEST['run_id'])) {
      $contest_id = $_REQUEST['contest_id'];
      $run_id = $_REQUEST['run_id'];
      $run = DBManager::getRun($contest_id, $run_id);
      $payload = json_decode($run['payload'], true);
      $output = "";
      $output .= "<html><body><pre>";
      $output .= "Type\n";
      $output .= "---\n";
      $output .= $payload['type'] . "\n";
      $output .= "---\n";
      $output .= "Good\n";
      $output .= "---\n";
      $output .= $payload['good'] . "\n";
      $output .= "---\n";
      $output .= "Bad\n";
      $output .= "---\n";
      $output .= $payload['bad'] . "\n";
      $output .= "---\n"; 
      $output .= "</pre></body></html>";
      print $output;
    }
  }
  
  public function download_interactive_grader() {
    if (isset($_REQUEST['contest_id']) && isset($_REQUEST['division_id']) && isset($_REQUEST['problem_id'])) {
      $contest_id = $_REQUEST['contest_id'];
      $division_id = $_REQUEST['division_id'];
      $problem_id = $_REQUEST['problem_id'];
      $problem = DBManager::getContestDivisionProblem($problem_id, $division_id, $contest_id);
      $metadata = json_decode($problem['metadata'], true);
      if (isset($metadata['grader']) && isset($metadata['grader']['filebase']) && isset($metadata['grader']['extension'])) {
        $filename = $metadata['grader']['filebase'] . '.' . $metadata['grader']['extension'];
        $payload = $metadata['grader']['src'];
        $this->writeDownloadHeader($filename, strlen($payload));
        print $payload;
      }
    }
  }

  public function upload_interactive_grader() {
    if (isset($_FILES['upload_file']) && $_FILES['upload_file']['size'] > 0) {
      $tmpname = $_FILES['upload_file']['tmp_name'];
      if (isset($_REQUEST['contest_id']) && isset($_REQUEST['problem_id'])) {
        $contest_id = $_REQUEST['contest_id'];
        $problem_id = $_REQUEST['problem_id'];
        $problem = DBManager::getContestProblem($contest_id, $problem_id);
        if (count($problem) > 0) {
          $filename = $_FILES['upload_file']['name'];
          $path_parts = pathinfo($filename);
          $filebase = $path_parts['filename'];
          $extension = $path_parts['extension'];

          $conflict = false;
          for ($i = 0; $i < count($problem) && !$conflict; $i++) {
            if ($filebase == $problem[$i]['alias']) {
              $conflict = true;
            }
          }
          if ($conflict) {
            print 'Grader must not be named ' . $filebase . ' as there is another grader in the system with the same name.<br />';
          }
          else {
            $metadata = json_decode($problem[0]['metadata'], true);

            $file = fopen($tmpname, 'r');
            $payload = fread($file, filesize($tmpname));
            fclose($file);

            $metadata['grader'] = array('src' => $payload, 'filebase' => $filebase, 'extension' => $extension);
            DBManager::modifyProblem($problem_id, 'metadata', json_encode($metadata));
            print 'Grader updated.<br />';
          }
        }
      }
      unlink($tmpname);
    }
  }

}
