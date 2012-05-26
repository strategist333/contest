<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class SpeedJudgeViewJudgments extends JudgeViewJudgments {
  
  protected function renderPayloadTDBody() {
    global $g_curr_contest;
?>
    td.append($("<a>").attr("href", "handlefile.php?action=view_submission&contest_id=<?= $g_curr_contest['contest_id'] ?>&run_id=" + run['run_id']).text("View source"));
<?php
  }
}
?>