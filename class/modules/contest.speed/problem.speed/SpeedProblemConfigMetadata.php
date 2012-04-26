<?php
require_once(__DIR__ . '/../../../common.php');

class SpeedProblemConfigMetadata extends ProblemConfigMetadata {
  
  public function __construct($problem_id, $division_id, $contest_id, $metadata, $division_metadata) {
    parent::__construct($problem_id, $division_id, $contest_id, $metadata, $division_metadata);
    if (!isset($this->division_metadata['points'])) {
      $this->division_metadata['points'] = 0;
    }
  }
  
  public function render() {
    parent::render();
    $pointsID = $this->transformID('points');
    $points = $this->division_metadata['points'];
    
?>
<script type="text/javascript">
(function (problemID, divisionID, contestID, metadata, divisionMetadata) {
  $("#<?= $pointsID ?>").keydown(function() {
    $(this).addClass('updating');
  }).change(function() {
    var thisElem = $(this);
    divisionMetadata['points'] = thisElem.val();
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
})(<?php print $this->problem_id ?>, <?= $this->division_id ?>, <?= $this->contest_id ?>, <?= json_encode($this->metadata) ?>, <?= json_encode($this->division_metadata) ?>);
</script>
Point value: <input id="<?= $pointsID ?>" type="text" value="<?= $points ?>"></input><br />
<?php
// END RENDER
  }
}
?>