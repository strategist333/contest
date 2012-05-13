<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class TeamProblemsListing {
  public function render() {
    global $g_curr_contest;
    global $k_problems_grace_period;
    $now = time();
    if ($now < $g_curr_contest['time_start'] - $k_problems_grace_period) {
      die();
    }
    $contest_id = $_SESSION['login']['contest_id'];
    $division_id = $_SESSION['login']['division_id'];
    
    $problems = DBManager::getContestDivisionProblems($contest_id, $division_id);
    
    foreach($problems as $problem) {
      $metadata = json_decode($problem['metadata'], true);
      if (!$metadata) {
        $metadata = array();
      }
      $division_metadata = json_decode($problem['division_metadata'], true);
      if (!$division_metadata) {
        $division_metadata = array();
      }
      loadWithPrefix(currentContestType(), $problem['problem_type'], 'ProblemTeamDisplay', $problem['problem_id'], $problem['title'], $problem['url'], $problem['alias'], $metadata, $division_metadata)->render();
    }
  }
}
?>