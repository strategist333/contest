<?php
require_once(__DIR__ . '/../../../common.php');

class ProblemConfigMetadata {

  protected $problem_id;
  protected $division_id;
  protected $contest_id;
  protected $metadata;
  
  public function __construct($problem_id, $division_id, $contest_id, $metadata) {
    $this->problem_id = $problem_id;
    $this->division_id = $division_id;
    $this->contest_id = $contest_id;
    $this->metadata = $metadata;
  }
  
  protected function transformID($id) {
    return $id . '_' . $this->problem_id . '_' . $this->division_id;
  }
  
  public function render() { }
}
?>