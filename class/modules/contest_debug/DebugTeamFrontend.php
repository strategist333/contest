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
       <select name="problem_id">

       </select>
     </div>
     <div>
       Solution:
       <select name="solution_type">
         <option value="correct">Always Correct</option>
         <option value="sometimes">Sometimes Wrong</option>
         <option value="wrong">Always Wrong</option>
       </select>
     </div>
     <div>
        Good Input:
        <textarea style="width:95%;height:100px" name="good"></textarea>
     </div>
     <div>
        Bad Input:
        <textarea style="width:95%;height:100px" name="bad"></textarea>
     </div>
  </div>
</div>
<?php
	}

}

?>
