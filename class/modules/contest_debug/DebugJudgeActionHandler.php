<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class DebugJudgeActionHandler extends JudgeActionHandler {

  public function submit_judgment($in, &$out) {
    parent::submit_judgment($in, $out);
    /*try {
      if ($in['correct']) {
        DebugScoreboardManager::generateScoreboard($in['contest_id'], $in['division_id']);
      }
      else {
        DebugScoreboardManager::setIncorrect($in['contest_id'], $in['division_id'], $in['team_id'], $in['problem_id']);
      }
    } catch (Exception $e) {
      $out['error'] = $e->getMessage();
    }*/
  }

}
?>
