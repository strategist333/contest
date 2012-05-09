<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class TeamActionHandler {
  
  public function load_submissions($in, &$out) {
    global $k_judgment_incorrect;
    global $k_judgment_correct;
    $contest_id = $_SESSION['login']['contest_id'];
    $team_id = $_SESSION['login']['team_id'];
    $division_id = $_SESSION['login']['division_id'];
    $submissions = DBManager::getTeamSubmissions($contest_id, $team_id, $division_id);
    if ($submissions) {
      $out['submissions'] = array();
      foreach ($submissions as $submission) {
        $message = "Pending";
        if ($submission['judgment'] == $k_judgment_correct) {
          $message = "Correct";
        }
        else if ($submission['judgment'] == $k_judgment_incorrect) {
          $judgment_metadata = json_decode($submission['judgment_metadata'], true);
          if (isset($judgment_metadata['error'])) {
            $message = $judgment_metadata['error'];
          }
          else {
            $message = "Incorrect";
          }
        }
        array_push($out['submissions'], array('time_submitted' => $submission['time_submitted'], 'alias' => $submission['alias'], 'title' => $submission['title'], 'judgment' => $submission['judgment'], 'message' => $message));
      }
    }
    else {
      $out['success'] = false;
    }
  }
}
?>