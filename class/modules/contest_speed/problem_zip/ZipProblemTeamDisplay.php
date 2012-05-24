<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class ZipProblemTeamDisplay extends ProblemTeamDisplay {
  
  public function render() {
    $linkID = parent::transformID('link');
// BEGIN RENDER
?>
<div class="problem_link"><a id="<?= $linkID ?>" href="<?= $this->url ?>">Download archive</a><div class="zip_line"></div></div>
<?php
// END RENDER
  }
}
?>