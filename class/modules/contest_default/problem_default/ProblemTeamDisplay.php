<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class ProblemTeamDisplay {
  
  protected $problem_id;
  protected $title;
  protected $url;
  protected $alias;
  protected $metadata;
  protected $division_metadata;
  
  public function __construct($problem_id, $title, $url, $alias, $metadata, $division_metadata) {
    $this->problem_id = $problem_id;
    $this->title = $title;
    $this->url = $url;
    $this->alias = $alias;
    $this->metadata = $metadata;
    $this->division_metadata = $division_metadata;
  }
  
  protected function transformID($id) {
    return $id . '_' . $this->problem_id;
  }
  
  public function render() {
// BEGIN RENDER
?>
<div class="problem_link"><a href="<?= $this->url ?>" target="_blank"><?= $this->alias . ' - ' . $this->title ?></a></div>
<?php
// END RENDER
  }
}
?>