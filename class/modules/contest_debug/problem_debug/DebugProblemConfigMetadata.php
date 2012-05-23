<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class DebugProblemConfigMetadata extends ProblemConfigMetadata {

  public function __construct($problem_id, $division_id, $contest_id, $metadata, $division_metadata) {
    parent::__construct($problem_id, $division_id, $contest_id, $metadata, $division_metadata);
    $this->needs_update = false;
    if (!isset($this->division_metadata['points'])) {
      $this->division_metadata['points'] = 0;
      $this->needs_update = true;
    }
    if (!isset($this->division_metadata['type'])) {
      $this->division_metadata['type'] = 'correct';
      $this->needs_update = true;
    }
    if (!isset($this->metadata['judge_io'])) {
      $this->metadata['judge_io'] = array();
      $this->needs_update = true;
    }
  }

  public function render() {
    parent::render();
    $pointsID = $this->transformID('points');
    $points = $this->division_metadata['points'];
    $typeID = $this->transformID('type');
    $type = $this->division_metadata['type'];
    $downloadZipID = $this->transformID('download_zip');
    $downloadGraderID = $this->transformID('download_grader');
    $uploadFrameID = $this->transformID('upload_frame');
?>
<script type="text/javascript">
(function (problemID, divisionID, contestID, metadata, divisionMetadata) {
  $("#<?= $pointsID ?>, #<?= $typeID ?>").keydown(function() {
    $(this).addClass('updating');
  }).change(function() {
    var thisElem = $(this);
    divisionMetadata['points'] = $("#<?= $pointsID ?>").val();
    divisionMetadata['type'] = $("#<?= $typeID ?>").val();

    $.ajax({
      data: JSON.stringify({'action' : 'modify_problem', 'problem_id' : problemID, 'division_id' : divisionID, 'contest_id' : contestID, 'key' : 'division_metadata', 'value' : JSON.stringify(divisionMetadata)}),
      success: function(ret) {
        if (ret['success']) {
          thisElem.removeClass('updating').addClass('updated');
          setTimeout(function() { thisElem.removeClass('updated'); }, 500);
        }
      }
    });
  });
  
<?php
  if ($this->needs_update) {
?>
  $.ajax({
    data: JSON.stringify({'action' : 'modify_problem', 'problem_id' : problemID, 'division_id' : divisionID, 'contest_id' : contestID, 'key' : 'division_metadata', 'value' : JSON.stringify(divisionMetadata)})
  });
  $.ajax({
    data: JSON.stringify({'action' : 'modify_problem', 'problem_id' : problemID, 'division_id' : divisionID, 'contest_id' : contestID, 'key' : 'metadata', 'value' : JSON.stringify(metadata)})
  });
<?php
  }
?>

  $("#<?= $downloadZipID ?>").click(function() {
    window.location.href = "handlefile.php?action=download_debug_zip&problem_id=" + problemID + "&division_id=" + divisionID + "&contest_id=" + contestID;
  });
})(<?= $this->problem_id ?>, <?= $this->division_id ?>, <?= $this->contest_id ?>, <?= json_encode($this->metadata) ?>, <?= json_encode($this->division_metadata) ?>);
</script>
<table>
  <tr>
    <td>Point value:</td>
    <td><input id="<?= $pointsID ?>" type="text" value="<?= $points ?>"></input></td>
  </tr>
  <tr>
    <td>Type:</td>
    <td>
       <select name="<?=$typeID?>" id="<?=$typeID?>">
         <option <?=$type == 'correct' ? 'selected' : ''?> value="correct">Always Correct</option>
         <option <?=$type == 'sometimes' ? 'selected' : ''?> value="sometimes">Sometimes Wrong</option>
         <option <?=$type == 'wrong' ? 'selected' : ''?> value="wrong">Always Wrong</option>
       </select>
    </td>
  </tr>
  <tr>
    <td>Grader file:</td>
    <td>
      <button id="<?= $downloadGraderID ?>">Download</button>
      <form action="handlefile.php" method="post" enctype="multipart/form-data" target="<?= $uploadFrameID ?>">
        <input type="file" name="upload_file"></input> 
        <input type="submit" value="source upload"></input>
        <input type="hidden" name="problem_id" value="<?= $this->problem_id ?>"></input>
        <input type="hidden" name="division_id" value="<?= $this->division_id ?>"></input>
        <input type="hidden" name="contest_id" value="<?= $this->contest_id ?>"></input>
        <input type="hidden" name="action" value="upload_interactive_grader"></input>
        <br />
        <iframe name="<?= $uploadFrameID ?>" style="width: 300px; height: 30px; border: 0px;"></iframe>
      </form>
    </td>
  </tr>
</table>
<?php
// END RENDER
  }
}
?>
