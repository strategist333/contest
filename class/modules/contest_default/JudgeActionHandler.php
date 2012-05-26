<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

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
    $metadata = $in['metadata'];
    if ($new_contest) {
      $contest_id = DBManager::addContest($contest_type, $contest_name, $time_start, $time_length, $tag, $metadata);
      if ($contest_id) {
        $out['contest_id'] = $contest_id;
      }
      $out['success'] = $contest_id ? true : false;
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
  
  public function toggle_current_contest($in, &$out) {
    global $g_curr_contest;
    $contest_id = $in['contest_id'];
    if ($g_curr_contest && $g_curr_contest['contest_id'] == $contest_id) {
      $contest_id = 0;
    }
    DBManager::setCurrentContest($contest_id);
  }
  
  public function add_division($in, &$out) {
    $division_name = $in['division_name'];
    $division_id = DBManager::addDivision($division_name);
    if ($division_id) {
      $out['division_id'] = $division_id;
    }
    $out['success'] = $division_id ? true : false;
  }
  
  public function link_divisions($in, &$out) {
    $contest_id = $in['contest_id'];
    $division_ids = $in['division_ids'];
    $out['success'] = DBManager::linkContestsDivisions($contest_id, $division_ids) == count($division_ids);
  }
  
  public function get_contest_divisions($in, &$out) {
    $contest_id = $in['contest_id'];
    $divisions = DBManager::getContestDivisions($contest_id);
    $out['division_ids'] = array_map(function ($division) { return $division['division_id']; }, $divisions);
  }
  
  public function rename_division($in, &$out) {
    $division_id = $in['division_id'];
    $division_name = $in['division_name'];
    $out['success'] = (DBManager::modifyDivision($division_id, $division_name) == 1);
  }
  
  public function get_contest_teams($in, &$out) {
    $contest_id = $in['contest_id'];
    $out['teams'] = DBManager::getContestTeams($contest_id);
  }
  
  public function get_contest_problems($in, &$out) {
    $contest_id = $in['contest_id'];
    $contest = DBManager::getContest($contest_id);
    $problem_types = DBManager::getProblemTypes($contest['contest_type']);
    $divisions = DBManager::getContestDivisions($contest_id);
    $problems = DBManager::getContestProblems($contest_id);
    $out['divisions'] = $divisions;
    $out['problems'] = $problems;
    $out['problem_types'] = $problem_types;
  }
  
  public function modify_problem($in, &$out) {
    global $k_problems_fields;
    global $k_contest_divisions_problems_fields;
    $key = $in['key'];
    $value = $in['value'];
    $problem_id = $in['problem_id'];
    $division_id = $in['division_id'];
    $contest_id = $in['contest_id'];
    if (in_array($key, $k_problems_fields)) {
      $out['success'] = (DBManager::modifyProblem($problem_id, $key, $value) == 1);
    }
    else if (in_array($key, $k_contest_divisions_problems_fields)) {
      $out['success'] = (DBManager::modifyContestDivisionProblem($problem_id, $division_id, $contest_id, $key, $value) == 1);
    }
    else {
      $out['success'] = false;
    }
  }
  
  public function enable_problem($in, &$out) {
    $problem_id = $in['problem_id'];
    $division_id = $in['division_id'];
    $contest_id = $in['contest_id'];
    DBManager::addContestDivisionProblem($problem_id, $division_id, $contest_id);
    $out['problem'] = DBManager::getContestDivisionProblem($problem_id, $division_id, $contest_id);
  }
  
  public function disable_problem($in, &$out) {
    $problem_id = $in['problem_id'];
    $division_id = $in['division_id'];
    $contest_id = $in['contest_id'];
    $out['success'] = (DBManager::removeContestDivisionProblem($problem_id, $division_id, $contest_id) == 1);
  }
  
  public function add_problem($in, &$out) {
    $division_id = $in['division_id'];
    $contest_id = $in['contest_id'];
    $problem_id = DBManager::addProblem();
    if ($problem_id) {
      DBManager::addContestDivisionProblem($problem_id, $division_id, $contest_id);
      $out['problem'] = DBManager::getContestDivisionProblem($problem_id, $division_id, $contest_id);
    }
    else {
      $out['success'] = false;
    }
  }
  
  public function initialize_judge($in, &$out) {
    global $g_curr_contest;
    if ($g_curr_contest) {
      $judge_id = DBManager::nextJudgeId();
      if ($judge_id) {
        $out = array_merge($out, $g_curr_contest);
        $out['judge_id'] = $judge_id;
        $out['metadata'] = json_decode($out['metadata'], true);
      }
      else {
        $out['success'] = false;
      }
    }
    else {
      $out['success'] = false;
    }
  }
  
  public function fetch_task($in, &$out) {
    global $g_curr_contest;
    $judge_id = $in['judge_id'];
    $contest_id = $in['contest_id'];
    if ($g_curr_contest) {
      if ($g_curr_contest['contest_id'] == $contest_id) {
        $res = DBManager::fetchTask($judge_id, $contest_id);
        if ($res) {
          $out = array_merge($out, $res);
          $out['division_metadata_hash'] = md5($out['division_metadata']);
          $out['problem_metadata_hash'] = md5($out['problem_metadata']);
          unset($out['division_metadata']);
          unset($out['problem_metadata']);
          $out['task_type'] = 'grade';
        }
        else {
          $out['task_type'] = 'poll';
        }
      }
      else {
        $out['task_type'] = 'reset';
      }
    }
    else {
      $out['task_type'] = 'halt';
    }
  }
  
  public function fetch_run($in, &$out) {
    global $g_curr_contest;
    $contest_id = $g_curr_contest['contest_id'];
    $team_username = $in['team_username'];
    $problem_alias = $in['problem_alias'];
    $res = DBManager::fetchRun($contest_id, $team_username, $problem_alias);
    if ($res) {
      $out = array_merge($out, $res);
      $out['contest_type'] = $g_curr_contest['contest_type'];
    }
    else {
      $out['success'] = false;
    }
  }
  
  public function fetch_task_metadata($in, &$out) {
    global $g_curr_contest;
    $problem_id = $in['problem_id'];
    $division_id = $in['division_id'];
    $contest_id = $in['contest_id'];
    $res = DBManager::fetchMetadata($problem_id, $division_id, $contest_id);
    $out['problem_metadata'] = $res['problem_metadata'];
    $out['division_metadata'] = $res['division_metadata'];
    $out['problem_metadata_hash'] = md5($res['problem_metadata']);
    $out['division_metadata_hash'] = md5($res['division_metadata']);
  }
  
  public function submit_judgment($in, &$out) {
    $judgment_id = $in['judgment_id'];
    $judge_id = $in['judge_id'];
    $correct = $in['correct'];
    $metadata = $in['metadata'];
    $out['success'] = (DBManager::updateJudgment($judgment_id, $judge_id, $correct, $metadata) == 1);
  }
  
  public function clear_judgment($in, &$out) {
    $judgment_id = $in['judgment_id'];
    $out['success'] = (DBManager::clearJudgment($judgment_id) == 1);
  }
  
  public function clear_judgments($in, &$out) {
    $problem_id = $in['problem_id'];
    $contest_id = $in['contest_id'];
    DBManager::clearJudgments($problem_id, $contest_id);
  }
    
  public function get_posts($in, &$out) {
    $contest_id = $in['contest_id'];
    $statuses = $in['statuses'];
    $out['posts'] = DBManager::getContestPosts($contest_id, $statuses);
  }
  
  public function read_post($in, &$out) {
    $post_id = $in['post_id'];
    $out['success'] = (DBManager::readPost($post_id) == 1);
  }
  
  public function reply_post($in, &$out) {
    $contest_id = $in['contest_id'];
    $team_id = $in['team_id'];
    $ref_id = $in['ref_id'];
    $message = $in['message'];
    DBManager::replyPost($contest_id, $team_id, $ref_id, $message);
    $out['success'] = (DBManager::readPost($ref_id) == 1);
  }
  
  public function broadcast_post($in, &$out) {
    $contest_id = $in['contest_id'];
    $message = $in['message'];
    $out['success'] = (DBManager::broadcastPost($contest_id, $message) == 1);
  }
  
  public function get_runs($in, &$out) {
    global $k_judgment_none;
    global $k_judgment_pending;
    $contest_id = $in['contest_id'];
    $count = $in['count'];
    $res = DBmanager::getRuns($contest_id, $count);
    if ($res) {
      $out['pending'] = array();
      $out['done'] = array();
      foreach ($res as $run) {
        $run['run_metadata'] = json_decode($run['run_metadata'], true);
        $run['judgment_metadata'] = json_decode($run['judgment_metadata'], true);
        if ($run['judgment'] == $k_judgment_none || $run['judgment'] == $k_judgment_pending) {
          array_push($out['pending'], $run);
        }
        else {
          array_push($out['done'], $run);
        }
      }
    }
    else {
      $out['success'] = false;
    }
  }
}
?>