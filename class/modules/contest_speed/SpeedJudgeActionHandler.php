<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class SpeedJudgeActionHandler extends JudgeActionHandler {
  
  public function submit_judgment($in, &$out) {
    parent::submit_judgment($in, $out);
    try {
      if ($in['correct']) {
        SpeedScoreboardManager::generateScoreboard($in['contest_id'], $in['division_id']);
      }
      else {
        SpeedScoreboardManager::setIncorrect($in['contest_id'], $in['division_id'], $in['team_id'], $in['problem_id']);
      }
    } catch (Exception $e) {
      $out['error'] = $e->getMessage();
    }
  }
  
  public function clear_judgment($in, &$out) {
    parent::clear_judgment($in, $out);
    SpeedScoreboardManager::generateScoreboard($in['contest_id'], $in['division_id']);
  }
  
  public function load_scoreboard($in, &$out) {
    global $g_curr_contest;
    if ($g_curr_contest) {
      $contest_id = $g_curr_contest['contest_id'];
      $division_id = $in['division_id'];
      $metadata = DBManager::getContestDivisionMetadata($contest_id, $division_id);
      if ($metadata) {
        $metadata = json_decode($metadata, true);
        if (!(isset($metadata['judge_scoreboard']) && isset($metadata['problems']))) {
          SpeedScoreboardManager::generateScoreboard($contest_id, $division_id);
          $metadata = DBManager::getContestDivisionMetadata($contest_id, $division_id);
          $metadata = json_decode($metadata, true);
        }
        if (isset($metadata['judge_scoreboard']) && isset($metadata['problems'])) {
          $out['scoreboard'] = array('teams' => $metadata['judge_scoreboard'], 'problems' => $metadata['problems']);
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
}
?>