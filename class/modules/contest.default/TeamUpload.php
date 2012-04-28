<?php
require_once(__DIR__ . '/../../common.php');

class TeamUpload {

  protected $team_id;
  protected $division_id;
  protected $contest_id;
  
  public function __construct($team_id, $division_id, $contest_id) {
    $this->team_id = $team_id;
    $this->division_id = $division_id;
    $this->contest_id = $contest_id;
  }
  
  protected function getMetadata() {    
    $filename = $_FILES['team_file']['name'];
    $path_parts = pathinfo($filename); 
    $extension = $path_parts['extension'];
    return array('extension' => $extension);
  }

  public function render() {
// BEGIN RENDER
?>
<?php
$success = false;
if (isset($_FILES['team_file']) && $_FILES['team_file']['size'] > 0) {
  $tmpname = $_FILES['team_file']['tmp_name'];
  $filename = $_FILES['team_file']['name'];
  $path_parts = pathinfo($filename); 
  $filebase = $path_parts['filename'];
  
  $file = fopen($tmpname, 'r');
  $payload = fread($file, filesize($tmpname));  
  fclose($file);
  unlink($tmpname);
  
  $metadata = $this->getMetadata();
  
  $run_id = DBManager::addRun($this->team_id, $this->division_id, $this->contest_id, $filebase, $payload, json_encode($metadata));
  if ($run_id) {
    $success = true;
  }
}
?>
<script type="text/javascript">window.top.location.stopUpload(<?= $success ?>);</script>
<?php
// END RENDER
  }
}
?>