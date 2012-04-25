<?php
require_once(__DIR__ . '/../../../common.php');

class SpeedProblemConfigMetadata extends ProblemConfigMetadata {
  
  public function __construct($problem_id, $division_id, $contest_id, $metadata) {
    if (!isset($metadata['points'])) {
      $metadata['points'] = 0;
    }
    parent::__construct($problem_id, $division_id, $contest_id, $metadata);
  }
  
  public function render() {
    parent::render();
    $metadataID = $this->transformID('metadata');
    $metadata = json_encode($this->metadata);
    $pointsID = $this->transformID('points');
    $points = $this->metadata['points'];
    
?>
<script type="text/javascript">
(function (problemID, divisionID, contestID, metadata) {
  $("#<?= $pointsID ?>").keydown(function() {
    $(this).addClass('updating');
  }).change(function() {
    var thisElem = $(this);
    metadata['points'] = thisElem.val();
    $.ajax({
      data: JSON.stringify({'action' : 'modify_problem', 'problem_id' : problemID, 'division_id' : divisionID, 'contest_id' : contestID, 'key' : 'metadata', 'value' : JSON.stringify(metadata)}),
      success: function(ret) {
        if (ret['success']) {
          thisElem.removeClass('updating').addClass('updated');
          setTimeout(function() { thisElem.removeClass('updated'); }, 500);
        }
      }
    });
  });
})(<?php print $this->problem_id ?>, <?= $this->division_id ?>, <?= $this->contest_id ?>, <?= json_encode($this->metadata) ?>);
</script>
Point value: <input id="<?= $pointsID ?>" type="text" value="<?= $points ?>"></input><br />
<?php
// END RENDER
  }
}
?>