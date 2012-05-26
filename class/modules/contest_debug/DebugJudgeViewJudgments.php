<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class DebugJudgeViewJudgments extends JudgeViewJudgments {
  
  protected function renderPayloadTDBody() {
    global $g_curr_contest;
?>
    td.append($("<a>").attr("href", "#").text("View submission").click((function(contestID, runID) {
      return function() {
        window.open("handlefile.php?action=view_submission&contest_id=<?= $g_curr_contest['contest_id'] ?>&run_id=" + run['run_id']);
        return false;
      }
    })(<?= $g_curr_contest['contest_id'] ?>, run['run_id'])));
<?php
  }
}
?>