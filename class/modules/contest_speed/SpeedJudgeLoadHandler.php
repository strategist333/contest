<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class SpeedJudgeLoadHandler extends JudgeLoadHandler {
  
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
          $this->writeDownloadFile($filename . '.zip', $tmpname);
          unlink($tmpname);
        }
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
}
?>
