<?php

require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class DebugTeamFrontend extends TeamFrontend {

	protected function renderUpload() {
?>
<div id="upload">
  <div class="div_padding">
     <div class="div_title">Submit Solution</div>
     <select name="problem_id">

     </select>
     
  </div>
</div>
<?php
	}

}

?>
