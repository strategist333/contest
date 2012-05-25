<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'common.php');

if (isset($_POST['data'])) {
  $json = json_decode($_POST['data'], true);
  if (isset($json['problem_id']) && isset($json['division_id']) && isset($json['contest_id']) && isset($json['problem_type'])) {
    $contest_id = $json['contest_id'];
    $problem_id = $json['problem_id'];
    $division_id = $json['division_id'];
    $contest = DBManager::getContest($contest_id);
    $problem = DBManager::getContestProblem($contest_id, $problem_id);
    if ($contest && $problem) {
      $division_found = false;
      foreach ($problem as $division_problem) {
        if ($division_problem['division_id'] == $division_id) {
          $metadata = json_decode($division_problem['metadata'], true);
          if (!$metadata) {
            $metadata = array();
          }
          $division_metadata = json_decode($division_problem['division_metadata'], true);
          if (!$division_metadata) {
            $division_metadata = array();
          }
          $division_found = true;
          break;
        }
      }
      if ($division_found) {
        loadWithPrefix($contest['contest_type'], $json['problem_type'], 'ProblemConfigMetadata', $json['problem_id'], $json['division_id'], $json['contest_id'], $metadata, $division_metadata)->render();
      }
    }
  }
}
?>
