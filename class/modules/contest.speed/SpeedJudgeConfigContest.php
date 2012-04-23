<?php
require_once(__DIR__ . '/../../common.php');

class SpeedJudgeConfigContest extends JudgeConfigContest {

  public function __construct($contest_type, $contest_id) {
    parent::__construct($contest_type, $contest_id);
  }
  
  public function renderMetadataLoadJS() {
    parent::renderMetadataLoadJS();
?>
            $("#freeze_hour").val(Math.floor(metadata['time_freeze'] / 3600));
            $("#freeze_minute").val(Math.floor(metadata['time_freeze'] % 3600 / 60));
<?php
  }
  
  public function renderMetadataSubmitJS() {
    parent::renderMetadataSubmitJS();
?>
      metadata['time_freeze'] = parseInt($("#freeze_hour").val()) * 3600 + parseInt($("#freeze_minute").val()) * 60;
<?php
  }
  
  public function renderMetadataTR() {
    parent::renderMetadataTR();
?>
    <tr>
      <td>Scoreboard Freeze After</td>
      <td>
        <input type="text" id="freeze_hour" size="2" value="1" /> Hours&nbsp;
        <input type="text" id="freeze_minute" size="2" value="30" /> Minutes
      </td>
    </tr>
<?php
  }
}
?>