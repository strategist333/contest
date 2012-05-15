<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class SpeedProblemConfigMetadata extends ProblemConfigMetadata {

  public function __construct($problem_id, $division_id, $contest_id, $metadata, $division_metadata) {
    parent::__construct($problem_id, $division_id, $contest_id, $metadata, $division_metadata);
    if (!isset($this->division_metadata['points'])) {
      $this->division_metadata['points'] = 0;
    }
    if (!isset($this->metadata['judge_io'])) {
      $this->metadata['judge_io'] = array();
    }
  }

}

