<?php

require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class DebugTeamActionHandler extends TeamActionHandler {

  function submit_debug_solution($json, &$ret) {
    $ret = array('success' => false, 'error' => '');
    if(isset($json['problem_id']) && isset($json['type'])) {
      $team_id = $_SESSION['login']['team_id'];
      $division_id = $_SESSION['login']['division_id'];
      $contest_id = $_SESSION['login']['contest_id'];

      $filebase = $json['problem_id'];

      $info = DBManager::addRun($team_id, $division_id, $contest_id, $filebase, '', json_encode($json));
      if($info['success']) {
        $ret['success'] = true;
      } else {
        $ret['error'] = $info['error'];
      }
    }
  }

}

?>
