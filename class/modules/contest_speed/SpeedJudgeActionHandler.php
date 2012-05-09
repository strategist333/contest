<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class SpeedJudgeActionHandler extends JudgeActionHandler {

  private function generate_scoreboard($contest_id, $division_id) {
    global $k_judgment_none;
    global $k_judgment_correct;
    global $k_judgment_incorrect;
    try {
      DBManager::begin();
      $info = DBManager::getContestDivisionJudgments($contest_id, $division_id);
      $teams = $info['teams'];
      $problems = $info['problems'];
      $judgments = $info['judgments'];
      
      $division_scoreboard = array('teams' => array(), 'problems' => array());
      
      $problem_map = array();
      $empty_judgments = array();
      foreach ($problems as $problem) {
        $division_metadata = json_decode($problem['division_metadata'], true);
        $problem_map[$problem['problem_id']] = array('index' => count($division_scoreboard['problems']), 'point_value' => $division_metadata['points']);
        array_push($division_scoreboard['problems'], array('problem_id' => intval($problem['problem_id']), 'alias' => $problem['alias']));
        array_push($empty_judgments, $k_judgment_none);
      }
    
      $team_map = array();
      foreach ($teams as $team) {
        $team_map[$team['team_id']] = count($division_scoreboard['teams']);
        array_push($division_scoreboard['teams'], array('team_id' => intval($team['team_id']), 'alias' => $team['alias'], 'score' => 0, 'time' => 0, 'judgments' => $empty_judgments));
      }      
      foreach ($judgments as $judgment) {
        $problem_id = $judgment['problem_id'];
        $problem_index = $problem_map[$problem_id]['index'];
        $team_index = $team_map[$judgment['team_id']];
        if ($division_scoreboard['teams'][$team_index]['judgments'][$problem_index] < $judgment['judgment']) {
          if ($judgment['judgment'] == $k_judgment_correct) {
            $point_value = $problem_map[$problem_id]['point_value'];
            $division_scoreboard['teams'][$team_index]['score'] += $point_value;
            if ($judgment['time_submitted'] > $division_scoreboard['teams'][$team_index]['time']) {
              $division_scoreboard['teams'][$team_index]['time'] = intval($judgment['time_submitted']);
            }
          }
          $division_scoreboard['teams'][$team_index]['judgments'][$problem_index] = intval($judgment['judgment']);
        }
      }
      
      usort($division_scoreboard['teams'], function ($a, $b) {
        if ($a['score'] == $b['score']) {
          if ($a['time'] == $b['time']) {
            return strcmp($a['alias'], $b['time']);
          }
          return $a['time'] > $b['time'] ? 1 : -1;
        }
        return $a['score'] > $b['score'] ? -1 : 1;
      });
      $metadata = json_encode(array('scoreboard' => $division_scoreboard));
      DBManager::modifyContestDivisionMetadata($contest_id, $division_id, $metadata);
      DBManager::commit();
    }
    catch (Exception $e) {
      DBManager::rollback();
      throw $e;
    }
  }
  
  private function set_scoreboard_incorrect($contest_id, $division_id, $team_id, $problem_id) {
    global $k_judgment_incorrect;
    try {
      DBManager::begin();
      $metadata = json_decode(DBManager::getContestDivisionMetadata($contest_id, $division_id), true);
      $division_scoreboard = $metadata['scoreboard'];
      for ($problem_index = 0; $problem_index < count($division_scoreboard['problems']); $problem_index++) {
        if ($division_scoreboard['problems'][$problem_index]['problem_id'] == $problem_id) {
          break;
        }
      }
      if ($problem_index >= count($division_scoreboard['problems'])) {
        throw new Exception('Problem not found');
      }
      for ($team_index = 0; $team_index < count($division_scoreboard['teams']); $team_index++) {
        if ($division_scoreboard['teams'][$team_index]['team_id'] == $team_id) {
          break;
        }
      }
      if ($team_index >= count($division_scoreboard['teams'])) {
        throw new Exception('Team not found');
      }
      $division_scoreboard['teams'][$team_index]['judgments'][$problem_index] = $k_judgment_incorrect;
      $metadata = json_encode(array('scoreboard' => $division_scoreboard));
      if (DBManager::modifyContestDivisionMetadata($contest_id, $division_id, $metadata) != 1) {
        throw new Exception('Metadata not modified');
      }
      DBManager::commit();
    }
    catch (Exception $e) {
      DBManager::rollback();
      throw $e;
    }
  }
  
  public function submit_judgment($in, &$out) {
    parent::submit_judgment($in, $out);
    try {
      if ($in['correct']) {
        $this->generate_scoreboard($in['contest_id'], $in['division_id']);
      }
      else {
        $this->set_scoreboard_incorrect($in['contest_id'], $in['division_id'], $in['team_id'], $in['problem_id']);
      }
    } catch (Exception $e) {
      $out['error'] = $e->getMessage();
    }
  }
}
?>