<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class PacketProblemTeamDisplay extends ProblemTeamDisplay {
  
  public function render() {
    $linkID = parent::transformID('link');
// BEGIN RENDER
?>
<div class="problem_link"><a id="<?= $linkID ?>" href="<?= $this->url ?>"><?= $this->title ?></a><div class="zip_line"></div></div>
<?php
// END RENDER
  }
}
?>