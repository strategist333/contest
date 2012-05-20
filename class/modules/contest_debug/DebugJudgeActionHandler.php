<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class DebugJudgeActionHandler extends JudgeActionHandler {

  public function submit_judgment($in, &$out) {
    parent::submit_judgment($in, $out);
    DebugScoreboardManager::generateScoreboard($in['contest_id'], $in['division_id']);
  }

}
?>
