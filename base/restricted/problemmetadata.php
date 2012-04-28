<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'common.php');

if (isset($_POST['data'])) {
  $json = json_decode($_POST['data'], true);
  if (isset($json['problem_id']) && isset($json['division_id']) && isset($json['contest_id']) && isset($json['metadata']) && isset($json['division_metadata']) && isset($json['problem_type'])) {
    $metadata = json_decode($json['metadata'], true);
    if (!$metadata) {
      $metadata = array();
    }
    $division_metadata = json_decode($json['division_metadata'], true);
    if (!$division_metadata) {
      $division_metadata = array();
    }
    loadWithPrefix(currentContestType(), $json['problem_type'], 'ProblemConfigMetadata', $json['problem_id'], $json['division_id'], $json['contest_id'], $metadata, $division_metadata)->render();
  }
}
?>
