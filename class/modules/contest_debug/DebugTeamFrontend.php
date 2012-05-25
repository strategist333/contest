<?php

require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class DebugTeamFrontend extends TeamFrontend {

  protected function renderScripts() {
    parent::renderScripts();
    global $g_curr_contest;
    global $k_judgment_incorrect;
    global $k_judgment_correct;
    global $k_post_reply;
    global $k_post_broadcast;
    $metadata = json_decode($g_curr_contest['metadata'], true);
    
// BEGIN RENDER SCRIPTS
?>
<script type="text/javascript">
var contestStartTime = <?= $g_curr_contest['time_start'] ?>;
var contestEndTime = <?= $g_curr_contest['time_start'] + $g_curr_contest['time_length'] ?>;
var scoreboardFreezeTime = <?= $g_curr_contest['time_start'] + $metadata['time_freeze'] ?>;
var judgmentCorrect = <?= $k_judgment_correct ?>;
var judgmentIncorrect = <?= $k_judgment_incorrect ?>;
var postBroadcast = <?= $k_post_broadcast ?>;
var postReply = <?= $k_post_reply ?>;
</script>
<script type="text/javascript" src="/js/debug.min.js"></script>
<?php
// END RENDER SCRIPTS
  }
  
	protected function renderUpload() {
    $contest_id = $_SESSION['login']['contest_id'];
    $division_id = $_SESSION['login']['division_id'];
// BEGIN RENDER UPLOAD
?>
<div id="upload">
  <div class="div_padding">
    <div class="div_title">Submit Solution</div>
    <div id="problem_id_div">
      <div id="problem_id_label">Problem:</div>
      <div id="problem_id_select">
        <select id="problem_id">
<?php
  $problems = DBManager::getContestDivisionProblems($contest_id, $division_id);
  foreach($problems as $problem) {
?>
        <option value="<?=$problem['alias']?>"><?=$problem['alias']?>:  <?=$problem['title']?></option>
<?php
  }
?>
        </select>
      </div>
    </div>
    <div id="soln_type_div">
      <div id="soln_type_label">Solution:</div>
      <div id="soln_type_select">
        <select id="soln_type">
          <option value="correct">Always Correct</option>
          <option value="sometimes">Sometimes Wrong</option>
          <option value="wrong">Always Wrong</option>
        </select>
      </div>
    </div>
    <div id="soln_good_div">
      <div id="soln_good_label">Good Input:</div>
      <textarea id="soln_good"></textarea>
    </div>
    <div id="soln_bad_div">
      <div id="soln_bad_label">Bad Input:</div>
      <textarea id="soln_bad"></textarea>
    </div>
    <div id="submissions_status"></div>
    <button id="debug_submit">Submit solution</button>
  </div>
</div>
<?php
// END RENDER UPLOAD
	}

}

?>
