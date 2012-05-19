<?php

require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class DebugTeamFrontend extends TeamFrontend {

	protected function renderUpload() {
?>
<div id="upload">
  <div class="div_padding">
     <div class="div_title">Submit Solution</div>
     <div>
       Problem:
       <select id="problem_id">
<?php
$contest_id = $_SESSION['login']['contest_id'];
$division_id = $_SESSION['login']['division_id'];

$problems = DBManager::getContestDivisionProblems($contest_id, $division_id);
foreach($problems as $problem) {
?>
        <option value="<?=$problem['alias']?>"><?=$problem['alias']?> <?=$problem['title']?></option>
<?php
}
?>
       </select>
     </div>
     <div>
       Solution:
       <select id="soln_type">
         <option value="correct">Always Correct</option>
         <option value="sometimes">Sometimes Wrong</option>
         <option value="wrong">Always Wrong</option>
       </select>
     </div>
     <div>
        Good Input:
        <textarea style="width:95%;height:100px" id="soln_good"></textarea>
     </div>
     <div>
        Bad Input:
        <textarea style="width:95%;height:100px" id="soln_bad"></textarea>
     </div>
     <div style="text-align:center;font-size:13px;margin:6px;padding:6px;background-color:#ddffff" id="submissions_status_ajax"></div>
     <div style="text-align:center">
        <input type="submit" value="Submit Solution" onClick="submitDebugSolution()">
     </div>
  </div>
</div>
<script type="text/javascript">

function submitDebugSolution() {
  $("#submissions_status_ajax").text("Submitting solution...");
  $.ajax({
    data: $.stringifyJSON({
     'action': 'submit_debug_solution',
     'problem_id': $('#problem_id').val(),
     'type': $('#soln_type').val(),
     'good': $('#soln_good').val(),
     'bad': $('#soln_bad').val(),
    }),
    success: function(response) {
      if(response['success']) {
        $("#submissions_status_ajax").text("Solution submitted. Waiting for judging.");
        setTimeout('$("#submissions_status_ajax").text("")', 5000);
      } else {
        $("#submissions_status_ajax").text("Solution submission failed. Please contact contest staff.");
      }
    }
  });
}

</script>
<?php
	}

}

?>
