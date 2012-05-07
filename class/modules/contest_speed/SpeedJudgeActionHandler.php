<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

function cmp($a, $b) {
  if ($b[2] == $a[2]) { // scores match
    return $a[3] - $b[3]; // time tie-break
  }
  else {
    return $b[2] - $a[2];
  }
}

class SpeedJudgeActionHandler extends JudgeActionHandler {


  private function generate_scoreboard() {
    global $g_curr_contest;
    global $k_judgment_none;
    global $k_judgment_correct;
    global $k_judgment_incorrect;
    $contest_id = $g_curr_contest['contest_id'];
    $info = DBManager::getContestJudgments($contest_id);
    $teams = $info['teams'];
    $problems = $info['problems'];
    $judgments = $info['judgments'];
    $divisions = DBManager::getContestDivisions($contest_id);
    
    $scoreboard = array();
    foreach ($divisions as $division) {
      $scoreboard[$division['division_id']] = array('teams' => array(), 'problems' => array());
    }
    
    $problem_map = array();
    foreach ($problems as $problem) {
      $division_metadata = json_decode($problem['division_metadata'], true);
      if (!isset($problem_map[$problem['problem_id']])) {
        $problem_map[$problem['problem_id']] = array();
      }
      $problem_map[$problem['problem_id']][$problem['division_id']] = array('index' => count($scoreboard[$problem['division_id']]['problems']), 'point_value' => $division_metadata['points']);
      array_push($scoreboard[$problem['division_id']]['problems'], array('problem_id' => intval($problem['problem_id']), 'alias' => $problem['alias']));
    }
  
    $team_map = array();
    foreach ($teams as $team) {
      $team_map[$team['team_id']] = array('index' => count($scoreboard[$team['division_id']]['teams']), 'division_id' => $team['division_id']);
      array_push($scoreboard[$team['division_id']]['teams'], array('team_id' => intval($team['team_id']), 'alias' => $team['alias'], 'score' => 0, 'time' => 0));
    }
    
    foreach ($divisions as $division) {
      $division_id = $division['division_id'];
      $num_problems = count($scoreboard[$division_id]['problems']);
      $empty_judgments = array();
      for ($i = 0; $i < $num_problems; $i++) {
        array_push($empty_judgments, $k_judgment_none);
      }
      for ($i = 0; $i < count($scoreboard[$division_id]['teams']); $i++) {
        $scoreboard[$division_id]['teams'][$i]['judgments'] = $empty_judgments;
      }
    }
    
    foreach ($judgments as $judgment) {
      $division_id = $team_map[$judgment['team_id']]['division_id'];
      $problem_id = $judgment['problem_id'];
      $problem_index = $problem_map[$problem_id][$division_id]['index'];
      $team_index = $team_map[$judgment['team_id']]['index'];
      if ($scoreboard[$division_id]['teams'][$team_index]['judgments'][$problem_index] < $judgment['judgment']) {
        if ($judgment['judgment'] == $k_judgment_correct) {
          $point_value = $problem_map[$problem_id][$division_id]['point_value'];
          $scoreboard[$division_id]['teams'][$team_index]['score'] += $point_value;
          if ($judgment['time_submitted'] > $scoreboard[$division_id]['teams'][$team_index]['time']) {
            $scoreboard[$division_id]['teams'][$team_index]['time'] = intval($judgment['time_submitted']);
          }
        }
        $scoreboard[$division_id]['teams'][$team_index]['judgments'][$problem_index] = intval($judgment['judgment']);
      }
    }
    
    foreach ($scoreboard as $division_id => &$division_scoreboard) {
      usort($division_scoreboard['teams'], function ($a, $b) {
        if ($a['score'] == $b['score']) {
          return $a['time'] > $b['time'] ? 1 : -1;
        }
        return $a['score'] > $b['score'] ? -1 : 1;
      });
    }
    
    foreach ($divisions as $division) {
      $metadata = json_encode(array('scoreboard' => $scoreboard[$division['division_id']]));
      DBManager::modifyContestDivisionMetadata($contest_id, $division['division_id'], $metadata);
    }
  }
  
  public function submit_judgment($in, &$out) {
    parent::submit_judgment($in, $out);
    try {
      $this->generate_scoreboard();
    } catch (Exception $e) {
      $out['e'] = $e->getMessage();
    }
  }
}
?>