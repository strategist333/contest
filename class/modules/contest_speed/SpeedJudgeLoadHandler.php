<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class SpeedJudgeLoadHandler extends JudgeLoadHandler {
  
  public function view_submission() {
    if (isset($_REQUEST['contest_id']) && isset($_REQUEST['run_id'])) {
      $contest_id = $_REQUEST['contest_id'];
      $run_id = $_REQUEST['run_id'];
      $run = DBManager::getRun($contest_id, $run_id);
      $payload = $run['payload'];
      $metadata = json_decode($run['run_metadata'], true);
      $filename = $run['problem_alias'] . '.' . $metadata['extension'];
      $this->writeDownloadHeader($filename, strlen($payload));
      print $payload;
    }
  }
  
  public function download_speed_zip() {
    if (isset($_REQUEST['contest_id']) && isset($_REQUEST['division_id']) && isset($_REQUEST['problem_id'])) {
      $contest_id = $_REQUEST['contest_id'];
      $division_id = $_REQUEST['division_id'];
      $problem_id = $_REQUEST['problem_id'];
      $problem = DBManager::getContestDivisionProblem($problem_id, $division_id, $contest_id);
      $filename = $problem['alias'];
      $metadata = json_decode($problem['metadata'], true);
      if (isset($metadata['judge_io']) && count($metadata['judge_io']) > 0) {
        $zip = new ZipArchive;
        $tmpname = tempnam('/tmp', 'speedzip');
        if ($zip->open($tmpname, ZipArchive::CREATE)) {
          for ($i = 1; $i <= count($metadata['judge_io']); $i++) {
            $io = $metadata['judge_io'][$i - 1];
            $name = sprintf('%s.in%02d', $filename, $i);
            $zip->addFromString($name, $io['input']);
            $name = sprintf('%s.out%02d', $filename, $i);
            $zip->addFromString($name, $io['output']);
          }
          $zip->close();
          $this->writeDownloadHeader($filename . '.zip', filesize($tmpname));
          readfile($tmpname);
        }
        unlink($tmpname);
      }
    }
  }
  
  public function upload_speed_zip() {
    if (isset($_FILES['upload_file']) && $_FILES['upload_file']['size'] > 0) {
      $tmpname = $_FILES['upload_file']['tmp_name'];
      if (isset($_REQUEST['contest_id']) && isset($_REQUEST['division_id']) && isset($_REQUEST['problem_id'])) {
        $contest_id = $_REQUEST['contest_id'];
        $division_id = $_REQUEST['division_id'];
        $problem_id = $_REQUEST['problem_id'];
        $problem = DBManager::getContestDivisionProblem($problem_id, $division_id, $contest_id);
        $filename = $problem['alias'];
        $metadata = json_decode($problem['metadata'], true);
        
        $judge_io = array();
        $zip = new ZipArchive;
        if ($zip->open($tmpname)) {
          for ($i = 1; ; $i++) {
            $input_name = sprintf('%s.in%02d', $filename, $i);
            $input = $zip->getFromName($input_name);
            $output_name = sprintf('%s.out%02d', $filename, $i);
            $output = $zip->getFromName($output_name);
            if ($input === false || $output === false) {
              if ($input === false && $output === false) {
                print ($i - 1) . ' judge cases read.<br />';
              }
              else if ($input === false) {
                print $input_name . ' missing.<br />';
              }
              else if ($output === false) {
                print $output_name . ' missing.<br />';
              }
              break;
            }
            else {
              array_push($judge_io, array('input' => $input, 'output' => $output));
            }
          }
          $metadata['judge_io'] = $judge_io;
          DBManager::modifyProblem($problem_id, 'metadata', json_encode($metadata));
        }
      }
      unlink($tmpname);
    }
  }
  
  public function download_interactive_zip() {
    if (isset($_REQUEST['contest_id']) && isset($_REQUEST['division_id']) && isset($_REQUEST['problem_id'])) {
      $contest_id = $_REQUEST['contest_id'];
      $division_id = $_REQUEST['division_id'];
      $problem_id = $_REQUEST['problem_id'];
      $problem = DBManager::getContestDivisionProblem($problem_id, $division_id, $contest_id);
      $filename = $problem['alias'];
      $metadata = json_decode($problem['metadata'], true);
      if (isset($metadata['judge_io']) && count($metadata['judge_io']) > 0) {
        $zip = new ZipArchive;
        $tmpname = tempnam('/tmp', 'interactivezip');
        if ($zip->open($tmpname, ZipArchive::CREATE)) {
          for ($i = 1; $i <= count($metadata['judge_io']); $i++) {
            $io = $metadata['judge_io'][$i - 1];
            $name = sprintf('%s.in%02d', $filename, $i);
            $zip->addFromString($name, $io['input']);
          }
          $zip->close();
          $this->writeDownloadHeader($filename . '.zip', filesize($tmpname));
          readfile($tmpname);
        }
        unlink($tmpname);
      }
    }
  }
  
  public function upload_interactive_zip() {
    if (isset($_FILES['upload_file']) && $_FILES['upload_file']['size'] > 0) {
      $tmpname = $_FILES['upload_file']['tmp_name'];
      if (isset($_REQUEST['contest_id']) && isset($_REQUEST['division_id']) && isset($_REQUEST['problem_id'])) {
        $contest_id = $_REQUEST['contest_id'];
        $division_id = $_REQUEST['division_id'];
        $problem_id = $_REQUEST['problem_id'];
        $problem = DBManager::getContestDivisionProblem($problem_id, $division_id, $contest_id);
        $filename = $problem['alias'];
        $metadata = json_decode($problem['metadata'], true);
        
        $judge_io = array();
        $zip = new ZipArchive;
        if ($zip->open($tmpname)) {
          for ($i = 1; ; $i++) {
            $input_name = sprintf('%s.in%02d', $filename, $i);
            $input = $zip->getFromName($input_name);
            if ($input === false) {
              print ($i - 1) . ' grader cases read.<br />';
              break;
            }
            else {
              array_push($judge_io, array('input' => $input));
            }
          }
          $metadata['judge_io'] = $judge_io;
          DBManager::modifyProblem($problem_id, 'metadata', json_encode($metadata));
        }
      }
      unlink($tmpname);
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
            print 'Grader must not be named ' . $filebase . '<br />';
          }
          else {
            $metadata = json_decode($problem[0]['metadata'], true);

            $file = fopen($tmpname, 'r');
            $payload = fread($file, filesize($tmpname));  
            fclose($file);
            
            $metadata['grader'] = array('valid' => true, 'src' => $payload, 'filebase' => $filebase, 'extension' => $extension);
            DBManager::modifyProblem($problem_id, 'metadata', json_encode($metadata));
            print 'Grader updated.<br />';
          }
        }
      }
      unlink($tmpname);
    }
  }
}
?>
