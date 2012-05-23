<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class SpeedTeamActionHandler extends TeamActionHandler {
  
  public function load_scoreboard($in, &$out) {
    $team_id = $_SESSION['login']['team_id'];
    $contest_id = $_SESSION['login']['contest_id'];
    $division_id = $_SESSION['login']['division_id'];
    $metadata = DBManager::getContestDivisionMetadata($contest_id, $division_id);
    if ($metadata) {
      $metadata = json_decode($metadata, true);
      if (isset($metadata['team_scoreboard']) && isset($metadata['problems'])) {
        $out['scoreboard'] = array('teams' => $metadata['team_scoreboard'], 'problems' => $metadata['problems']);
        foreach ($out['scoreboard']['teams'] as &$teams) {
          if ($teams['team_id'] == $team_id) {
            $teams['me'] = true;
          }
          unset($teams['team_id']);
        }
      }
      else {
        $team_scoreboard = SpeedScoreboardManager::generateScoreboard($contest_id, $division_id);
        if ($team_scoreboard !== false) {
          $out['scoreboard'] = $team_scoreboard;
          foreach ($out['scoreboard']['teams'] as &$teams) {
            if ($teams['team_id'] == $team_id) {
              $teams['me'] = true;
            }
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
}
?>