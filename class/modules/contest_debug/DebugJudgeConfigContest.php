<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class DebugJudgeConfigContest extends JudgeConfigContest {
  
  public function renderMetadataLoadDefaultJS() {
    parent::renderMetadataLoadJS();
?>
            $("#freeze_hour").val(1);
            $("#freeze_minute").val(30);
<?php
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
      <td>Scoreboard Freeze when</td>
      <td>
        <input type="text" id="freeze_hour" size="2" value="1" /> hours&nbsp;
        <input type="text" id="freeze_minute" size="2" value="30" /> minutes into contest
      </td>
    </tr>
<?php
  }
}
?>

