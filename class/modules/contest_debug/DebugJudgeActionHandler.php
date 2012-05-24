<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class DebugJudgeActionHandler extends JudgeActionHandler {

  public function submit_judgment($in, &$out) {
    parent::submit_judgment($in, $out);
    DebugScoreboardManager::generateScoreboard($in['contest_id'], $in['division_id']);
  }
  
  public function clear_judgment($in, &$out) {
    parent::clear_judgment($in, $out);
    DebugScoreboardManager::generateScoreboard($in['contest_id'], $in['division_id']);
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
          DebugScoreboardManager::generateScoreboard($contest_id, $division_id);
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
