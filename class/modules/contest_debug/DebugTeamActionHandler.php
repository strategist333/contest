<?php

require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class DebugTeamActionHandler extends TeamActionHandler {

  public function load_scoreboard($in, &$out) {
    $contest_id = $_SESSION['login']['contest_id'];
    $division_id = $_SESSION['login']['division_id'];
    $metadata = DBManager::getContestDivisionMetadata($contest_id, $division_id);
    if ($metadata) {
      $metadata = json_decode($metadata, true);
      if (isset($metadata['team_scoreboard']) && isset($metadata['problems'])) {
        $out['scoreboard'] = array('teams' => $metadata['team_scoreboard'], 'problems' => $metadata['problems']);
        foreach ($out['scoreboard']['teams'] as &$teams) {
          unset($teams['team_id']);
        }
      } else {
        $team_scoreboard = DebugScoreboardManager::generateScoreboard($contest_id, $division_id);
        if ($team_scoreboard !== false) {
          $out['scoreboard'] = $team_scoreboard;
          foreach ($out['scoreboard']['teams'] as &$teams) {
            unset($teams['team_id']);
          }
        }
        else {
          $out['success'] = false;
        }
      }
    }
    else {
      $out['success'] = false;
    }
  }

  public function submit_debug_solution($json, &$ret) {
    $ret = array('success' => false, 'error' => '');
    if(isset($json['problem_id']) && isset($json['type'])) {
      $team_id = $_SESSION['login']['team_id'];
      $division_id = $_SESSION['login']['division_id'];
      $contest_id = $_SESSION['login']['contest_id'];

      $filebase = $json['problem_id'];

      $info = DBManager::addRun($team_id, $division_id, $contest_id, $filebase, '', json_encode($json));
      if($info['success']) {
        $ret['success'] = true;
      } else {
        $ret['error'] = $info['error'];
      }
    }
  }

}

?>
