<?php
require_once(__DIR__ . '/../../common.php');

class JudgeActionHandler {
  
  public function load_contest($in, &$out) {
    $contest_id = $in['contest_id'];
    $contest = DBManager::getContest($contest_id);
    if ($contest) {
      $out = array_merge($out, $contest);
      $out['metadata'] = json_decode($out['metadata'], true);
    }
    else {
      $out['success'] = false;
    }
  }
  
  public function save_contest($in, &$out) {
    global $g_curr_contest;
    global $k_contest_inactive;
    global $k_contest_active;
    $new_contest = $in['contest_id'] == 0;
    $contest_type = $in['contest_type'];
    $contest_name = $in['contest_name'];
    $time_start = $in['time_start'];
    $time_length = $in['time_length'];
    $tag = $in['tag'];
    $metadata = json_encode($in['metadata']);
    if ($new_contest) {
      $contest_id = DBManager::addContest($contest_type, $contest_name, $time_start, $time_length, $tag, $metadata);
      if ($contest_id) {
        $out['contest_id'] = $contest_id;
        $out['success'] = true;
      }
      else {
        $out['success'] = false;
      }
    }
    else {
      $contest_id = $in['contest_id'];
      if (DBManager::modifyContest($contest_id, $contest_type, $contest_name, $time_start, $time_length, $tag, $metadata) == 1) {
        $out['contest_id'] = $contest_id;
      }
      else {
        $out['success'] = false;
      }
    }
  }
  
  public function delete_contest($in, &$out) {
    $contest_id = $in['contest_id'];
    $out['success'] = (DBManager::deleteContest($contest_id) == 1);
  }
  
  public function clone_contest($in, &$out) {
    $contest_id = $in['contest_id'];
    $contest_name = $in['contest_name'];
    $contest_id = DBManager::cloneContest($contest_id, $contest_name);
    if ($contest_id) {
      $out['contest_id'] = $contest_id;
      $out['success'] = true;
    }
    else {
      $out['success'] = false;
    }
  }
}
?>