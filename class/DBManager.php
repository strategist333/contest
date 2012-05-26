<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'DBConstants.php');

class DBManager {
  private static $singleton;

  private function __construct() {
  }
  
  public function __clone() {
    trigger_error('Clone is not allowed.', E_USER_ERROR);
  }
  
  public function __wakeup() {
    trigger_error('Deserialization is not allowed.', E_USER_ERROR);
  }
  
  private static function querySelectUnique($sql) {
    $dbh = self::singleton();
    $stmt = $dbh->prepare($sql);
    $stmt->execute(array_slice(func_get_args(), 1));
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    if (count($res) == 1) {
      return $res[0];
    }
    return false;
  }
  
  private static function querySelect($sql) {
    $dbh = self::singleton();
    $stmt = $dbh->prepare($sql);
    $stmt->execute(array_slice(func_get_args(), 1));
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    return $res;
  }
  
  private static function queryInsert($sql) {
    $dbh = self::singleton();
    $stmt = $dbh->prepare($sql);
    $stmt->execute(array_slice(func_get_args(), 1));
    $res = $dbh->lastInsertID();
    $stmt->closeCursor();
    return $res;
  }
  
  private static function queryUpdate($sql) {
    $dbh = self::singleton();
    $stmt = $dbh->prepare($sql);
    $stmt->execute(array_slice(func_get_args(), 1));
    $res = $stmt->rowCount();
    $stmt->closeCursor();
    return $res;
  }
  
  private static function singleton() {
    if (!isset(self::$singleton)) {
      try {
        self::$singleton = new PDO('mysql:host=localhost;dbname=contest', 'contest', 'proco', array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
        self::$singleton->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      }
      catch (PDOException $e) {
        die();
      }
    }
    return self::$singleton;
  }
  
  public static function begin() {
    $dbh = self::singleton();
    $dbh->beginTransaction();
  }
  
  public static function commit() {
    $dbh = self::singleton();
    $dbh->commit();
  }
  
  public static function rollback() {
    $dbh = self::singleton();
    $dbh->rollBack();
  }
  
  // Begin definition of procedures
  public static function getContestTypes() {
    $contest_types = array();
    foreach (glob(__DIR__ . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'contest_*') as $contest_full_type) {
      array_push($contest_types, substr($contest_full_type, strrpos($contest_full_type, 'contest_') + strlen('contest_')));
    }
    return $contest_types;
  }
  
  public static function getProblemTypes($curr_contest_type) {
    $contest_types = array($curr_contest_type);
    if ($curr_contest_type != 'default') {
      array_push($contest_types, 'default');
    }
    $problem_types = array();
    foreach ($contest_types as $contest_type) {
      foreach (glob(__DIR__ . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'contest_' . $contest_type . DIRECTORY_SEPARATOR . 'problem_*') as $problem_full_type) {
        array_push($problem_types, substr($problem_full_type, strrpos($problem_full_type, 'problem_') + strlen('problem_')));
      }
    }
    return $problem_types;
  }

  public static function getCurrentContest() {
    return self::querySelectUnique('select contest_id, contest_type, contest_name, time_start, time_length, metadata, status, unix_timestamp() as now from globals join contests on globals.curr_contest_id = contests.contest_id');
  }
  
  public static function login($username, $password) {
    return self::querySelectUnique('select team_id, alias, division_id, contest_id from globals join contests on globals.curr_contest_id = contests.contest_id join contests_divisions using (contest_id) join tags using (tag) join divisions using (division_id) join teams using (division_id, tag) where username = ? and password = ?', $username, $password);
  }
  
  public static function getContest($contest_id) {
    global $k_contest_active;
    return self::querySelectUnique('select contest_id, contest_type, contest_name, time_start, time_length, tag, metadata, status from contests where contest_id = ? and status = ?', $contest_id, $k_contest_active);
  }
  
  public static function getContestsOfType($contest_type) {
    global $k_contest_active;
    return self::querySelect('select contest_id, contest_name, tag from contests where contest_type = ? and status = ? order by contest_id desc', $contest_type, $k_contest_active);
  }
  
  public static function addContest($contest_type, $contest_name, $time_start, $time_length, $tag, $metadata) {
    global $k_contest_active;
    self::queryUpdate('insert into tags set tag = ? on duplicate key update tag=tag', $tag);
    return self::queryInsert('insert into contests set contest_type = ?, contest_name = ?, time_start = ?, time_length = ?, tag = ?, metadata = ?, status = ?', $contest_type, $contest_name, $time_start, $time_length, $tag, $metadata, $k_contest_active);
  }
  
  public static function modifyContest($contest_id, $contest_type, $contest_name, $time_start, $time_length, $tag, $metadata) {
    global $k_contest_active;
    self::queryUpdate('insert into tags set tag = ? on duplicate key update tag=tag', $tag);
    return self::queryUpdate('update contests set contest_type = ?, contest_name = ?, time_start = ?, time_length = ?, tag = ?, metadata = ? where contest_id = ? and status = ?', $contest_type, $contest_name, $time_start, $time_length, $tag, $metadata, $contest_id, $k_contest_active);
  }
  
  public static function cloneContest($contest_id, $contest_name) {
    $contest = self::getContest($contest_id);
    if ($contest) {
      return self::queryInsert('insert into contests set contest_type = ?, contest_name = ?, time_start = ?, time_length = ?, tag = ?, metadata = ?, status = ?', $contest['contest_type'], $contest_name, $contest['time_start'], $contest['time_length'], $contest['tag'], $contest['metadata'], $contest['status']);
    }
    return false;
  }  
  
  public static function deleteContest($contest_id) {
    global $k_contest_active;
    global $k_contest_inactive;
    self::queryUpdate('update globals set curr_contest_id = 0 where curr_contest_id = ?', $contest_id);
    return self::queryUpdate('update contests set status = ? where contest_id = ? and status = ?', $k_contest_inactive, $contest_id, $k_contest_active);
  }
  
  public static function setCurrentContest($contest_id) {
    return self::queryUpdate('update globals set curr_contest_id = ?', $contest_id);
  }
  
  public static function getDivisions() {
    return self::querySelect('select division_id, division_name from divisions order by division_name asc');
  }
  public static function addDivision($division_name) {
    return self::queryInsert('insert into divisions set division_name = ?', $division_name);
  }
  
  public static function linkContestsDivisions($contest_id, $division_ids) {
    try {
      self::begin();
      self::queryUpdate('delete from contests_divisions where contest_id = ?', $contest_id);
      $res = self::queryUpdate('insert into contests_divisions (contest_id, division_id, metadata) values ' . implode(',', array_map(function($division_id) use ($contest_id) { return '(' . $contest_id . ',' . $division_id . ',\'{}\')'; }, $division_ids)));
      self::commit();
    }
    catch (Exception $e) {
      self::rollback();
      $res = false;
    }
    return $res;
  }
    
  public static function getContestDivisions($contest_id) {
    return self::querySelect('select division_id, division_name from contests_divisions join divisions using (division_id) where contest_id = ? order by division_name asc', $contest_id);
  }
  
  public static function getContestDivisionMetadata($contest_id, $division_id) {
    $metadata = self::querySelectUnique('select metadata from contests_divisions where contest_id = ? and division_id = ?', $contest_id, $division_id);
    if ($metadata) {
      return $metadata['metadata'];
    }
    return false;
  }
  
  public static function modifyContestDivisionMetadata($contest_id, $division_id, $metadata) {
    return self::queryUpdate('update contests_divisions set metadata = ? where contest_id = ? and division_id = ?', $metadata, $contest_id, $division_id);
  }
  
  public static function modifyDivision($division_id, $division_name) {
    return self::queryUpdate('update divisions set division_name = ? where division_id = ?', $division_name, $division_id);
  }
  
  public static function getTeams($contest_id) {
    return self::querySelect('select team_id, username, password, alias, division_name from contests join contests_divisions using (contest_id) join divisions using (division_id) join teams using (tag, division_id) where contest_id = ?', $contest_id);
  }
  
  public static function setTeams($contest_id, $teams) {
    try {
      self::begin();
      $tags = self::querySelectUnique('select tag from contests where contest_id = ?', $contest_id);
      $tag = $tags['tag'];
      
      $divisions = self::getContestDivisions($contest_id);
      $division_map = array();
      foreach ($divisions as $division) {
        $division_map[$division['division_name']] = $division['division_id'];
      }
      
      $valid_team_ids = array();
      
      $dbh = self::singleton();
      $update_stmt = $dbh->prepare('update teams set username = ?, password = ?, alias = ?, tag = ?, division_id = ? where team_id = ?');
      $insert_stmt = $dbh->prepare('insert into teams set username = ?, password = ?, alias = ?, tag = ?, division_id = ?');
      
      $update_count = 0;
      $insert_count = 0;
      
      $success = true;
      foreach ($teams as $team) {
        $username = $team['username'];
        $password = $team['password'];
        $alias = $team['alias'];
        if (!isset($division_map[$team['division_name']])) {
          throw new Exception('Invalid division ' . $team['division_name']);
        }
        $division_id = $division_map[$team['division_name']];
        if (isset($team['team_id'])) {
          $team_id = $team['team_id'];
          $update_stmt->execute(array($username, $password, $alias, $tag, $division_id, $team_id));
          if ($update_stmt->rowCount() != 1) {
            throw new Exception('Failed to update ' . $team_id);
          }
          $update_count += $update_stmt->rowCount();
          array_push($valid_team_ids, $team_id);
        }
        else {
          $insert_stmt->execute(array($username, $password, $alias, $tag, $division_id));
          if ($insert_stmt->rowCount() != 1) {
            throw new Exception('Failed to insert ' . $team_id);
          }
          $insert_count += $insert_stmt->rowCount();
          $team_id = $dbh->lastInsertID();
          array_push($valid_team_ids, $team_id);
        }
      }
      $update_stmt->closeCursor();
      $insert_stmt->closeCursor();
      
      $delete_stmt = $dbh->prepare('delete from teams where division_id in (select division_id from contests_divisions where contest_id = ?) and tag = (select tag from contests where contest_id = ?)' . (count($valid_team_ids) > 0 ? (' and team_id not in (' . implode(',', $valid_team_ids) . ')') :  ''));
      $delete_stmt->execute(array($contest_id, $contest_id));
      $delete_count = $delete_stmt->rowCount();
      
      if ($delete_count != count($teams) - count($valid_team_ids)) {
        throw new Exception('Failed to delete');
      }
      
      self::commit();
      $res = array('success' => true, 'update' => $update_count, 'insert' => $insert_count, 'delete' => $delete_count);
    }
    catch (Exception $e) {
      self::rollback();
      $res = array('success' => false, 'error' => $e->getMessage());
    }
    return $res;
  }
  
  public static function getContestTeams($contest_id) {
    return self::querySelect('select team_id, tag, username, password, alias, division_id, division_name from teams join tags using (tag) join divisions using (division_id) join contests_divisions using (division_id) join contests using (tag, contest_id) where contest_id = ? order by division_name asc, username asc', $contest_id);
  }
  
  public static function getContestDivisionTeams($contest_id, $division_id) {
    return self::querySelect('select team_id, tag, username, password, alias from teams join tags using (tag) join divisions using (division_id) join contests_divisions using (division_id) join contests using (tag, contest_id) where contest_id = ? and division_id = ? order by username asc', $contest_id, $division_id);
  }
  
  public static function getContestProblems($contest_id) {
    return self::querySelect('select problem_id, problem_type, title, order_seq, status, division_id, url, alias from problems join contests_divisions_problems using (problem_id) where contest_id = ? order by order_seq asc, problem_id asc, division_id asc', $contest_id);
  }
  
  public static function getContestDivisionProblems($contest_id, $division_id) {
    return self::querySelect('select problem_id, problem_type, title, order_seq, status, url, alias, metadata, division_metadata from problems join contests_divisions_problems using (problem_id) where contest_id = ? and division_id = ? order by order_seq asc, problem_id asc', $contest_id, $division_id);
  }
  
  public static function getContestProblem($contest_id, $problem_id) {
    return self::querySelect('select problem_id, problem_type, title, order_seq, status, division_id, url, alias, metadata, division_metadata from problems join contests_divisions_problems using (problem_id) where contest_id = ? and problem_id = ? order by order_seq asc, problem_id asc, division_id asc', $contest_id, $problem_id);
  }
  
  public static function modifyProblem($problem_id, $key, $value) {
    $success = false;
    try {
      self::begin();
      if (self::queryUpdate('update problems set ' . $key . ' = ? where problem_id = ?', $value, $problem_id) != 1) {
        throw new Exception('Did not update problem.');
      }
      if ($key == 'problem_type') {
        $division_metadata = '{}';
        self::queryUpdate('update contests_divisions_problems set division_metadata = ? where problem_id = ?', $division_metadata, $problem_id);
      }
      self::commit();
      $success = true;
    }
    catch (Exception $e) {
      self::rollback();
    }
    return $success;
  }
  
  public static function modifyContestDivisionProblem($problem_id, $division_id, $contest_id, $key, $value) {
    return self::queryUpdate('update contests_divisions_problems set ' . $key . ' = ? where problem_id = ? and division_id = ? and contest_id = ?', $value, $problem_id, $division_id, $contest_id);
  }
  
  public static function getContestDivisionProblem($problem_id, $division_id, $contest_id) {
    return self::querySelectUnique('select problem_id, problem_type, title, order_seq, status, division_id, url, alias, metadata, division_metadata from problems join contests_divisions_problems using (problem_id) where contest_id = ? and division_id = ? and problem_id = ?', $contest_id, $division_id, $problem_id);
  }
  
  public static function addContestDivisionProblem($problem_id, $division_id, $contest_id) {
    $division_metadata = '{}';
    return self::queryUpdate('insert ignore into contests_divisions_problems set problem_id = ?, division_id = ?, contest_id = ?, division_metadata = ?', $problem_id, $division_id, $contest_id, $division_metadata);
  }
  
  public static function removeContestDivisionProblem($problem_id, $division_id, $contest_id) {
    return self::queryUpdate('delete from contests_divisions_problems where problem_id = ? and division_id = ? and contest_id = ?', $problem_id, $division_id, $contest_id);
  }
  
  public static function addProblem() {
    global $k_problem_active;
    try {
      self::begin();
      $metadata = '{}';
      $res = self::queryInsert('insert into problems set status = ?, metadata = ?, order_seq = (select next_order_seq from globals)', $k_problem_active, $metadata);
      self::queryUpdate('update globals set next_order_seq = next_order_seq + 1');
      self::commit();
    }
    catch (Exception $e) {
      self::rollback();
      $res = false;
    }
    return $res;
  }
  
  public static function addRun($team_id, $division_id, $contest_id, $filebase, $payload, $metadata) {
    global $k_run_active;
    global $k_judge_none;
    global $k_judgment_none;
    $res = array('success' => false);
    try {
      self::begin();
      $problem = self::querySelectUnique('select problem_id from problems join contests_divisions_problems using (problem_id) where contest_id = ? and division_id = ? and alias = ?', $contest_id, $division_id, $filebase);
      if (!$problem) {
        self::rollback();
        $res['error'] = 'Invalid filename';
      }
      else {
        $problem_id = $problem['problem_id'];
        $run_id = self::queryInsert('insert into runs set problem_id = ?, team_id = ?, payload = ?, time_submitted = unix_timestamp(), metadata = ?, status = ?', $problem_id, $team_id, $payload, $metadata, $k_run_active);
        $default_metadata = '{}';
        $update_count = self::queryUpdate('insert into judgments set judge_id = ?, run_id = ?, time_updated = unix_timestamp(), metadata = ?, status = ?', $k_judge_none, $run_id, $default_metadata, $k_judgment_none);
        if ($run_id == 0 || $update_count != 1) {
          throw new Exception('Run and judgment not inserted');
        }
        self::commit();
        $res['success'] = true;
        $res['run_id'] = $run_id;
      }
    }
    catch (Exception $e) {
      self::rollback();
      $res['error'] = 'Internal error';
    }
    return $res;
  }
  
  public static function nextJudgeID() {
    try {
      self::begin();
      $info = self::querySelectUnique('select next_judge_id from globals');
      $judge_id = $info['next_judge_id'];
      $update_count = self::queryUpdate('update globals set next_judge_id = next_judge_id + 1');
      if ($update_count != 1) {
        throw new Exception('Judge ID not allocated');
      }
      $res = $judge_id;
      self::commit();
    }
    catch (Exception $e) {
      self::rollback();
      $res = false;
    }
    return $res;
  }
  
  public static function fetchTask($judge_id, $contest_id) {
    global $k_judgment_pending;
    global $k_judgment_none;
    global $k_judgment_maxdelay;
    $print_error = true;
    try {
      self::begin();
      $existing_runs = self::querySelect('select run_id, judgment_id, problem_id, team_id, payload, time_submitted, runs.metadata as run_metadata from runs join judgments using (run_id) where judgments.judge_id = ? and judgments.status = ? limit 1', $judge_id, $k_judgment_pending);
      if (count($existing_runs) == 0) {
        $update_count = self::queryUpdate('update judgments set judge_id = ?, time_updated = unix_timestamp(), status = ? where status = ? or (status = ? and (unix_timestamp() - time_updated > ?)) order by run_id asc limit 1', $judge_id, $k_judgment_pending, $k_judgment_none, $k_judgment_pending, $k_judgment_maxdelay);
        if ($update_count != 1) {
          $print_error = false;
          throw new Exception('Queue empty.');
        }
        $run_info = self::querySelectUnique('select run_id, judgment_id, problem_id, team_id, payload, time_submitted, runs.metadata as run_metadata from runs join judgments using (run_id) where judgments.judge_id = ? and judgments.status = ?', $judge_id, $k_judgment_pending);
      }
      else {
        $run_info = $existing_runs[0];
      }
      
      $problem_id = $run_info['problem_id'];
      $team_id = $run_info['team_id'];
      $problem_info = self::querySelectUnique('select problem_id, problem_type, contests_divisions_problems.alias as alias, problems.metadata as problem_metadata, division_id, division_metadata, team_id, username as team_username from teams join divisions using (division_id) join contests_divisions_problems using (division_id) join problems using (problem_id) where team_id = ? and contest_id = ? and problem_id = ?', $team_id, $contest_id, $problem_id);
      if (!$problem_info) {
        throw new Exception('Problem not fetched. ' . $team_id . ' ' . $contest_id . ' ' . $problem_id);
      }
      $res = array_merge($run_info, $problem_info);
      self::commit();
    }
    catch (Exception $e) {
      if ($print_error) {
        print $e->getMessage();
      }
      self::rollback();
      $res = false;
    }
    return $res;
  }
  
  public static function fetchRun($contest_id, $team_username, $problem_alias) {
    return self::querySelectUnique('select run_id, problem_id, team_id, payload, time_submitted, runs.metadata as run_metadata, problem_type, contests_divisions_problems.alias as alias, problems.metadata as problem_metadata, division_id, division_metadata, username as team_username from runs join teams using (team_id) join problems using (problem_id) join contests_divisions_problems using (problem_id, division_id) where contest_id = ? and username = ? and contests_divisions_problems.alias = ? order by time_submitted desc limit 1', $contest_id, $team_username, $problem_alias);
  }
  
  public static function fetchMetadata($problem_id, $division_id, $contest_id) {
    return self::querySelectUnique('select problems.metadata as problem_metadata, division_metadata from problems join contests_divisions_problems using (problem_id) where problem_id = ? and division_id = ? and contest_id = ?', $problem_id, $division_id, $contest_id);
  }
  
  public static function updateJudgment($judgment_id, $judge_id, $correct, $metadata) {
    global $k_judgment_correct;
    global $k_judgment_incorrect;
    return self::queryUpdate('update judgments set time_updated = unix_timestamp(), metadata = ?, status = ?, judge_id = ? where judgment_id = ?', $metadata, ($correct ? $k_judgment_correct : $k_judgment_incorrect), $judge_id, $judgment_id);
  }
  
  public static function clearJudgment($judgment_id) {
    global $k_judgment_none;
    $metadata = '{}';
    return self::queryUpdate('update judgments set time_updated = unix_timestamp(), judge_id = 0, metadata = ?, status = ? where judgment_id = ?', $metadata, $k_judgment_none, $judgment_id);
  }
  
  public static function clearJudgments($problem_id, $contest_id) {
    global $k_judgment_none;
    $metadata = '{}';
    return self::queryUpdate('update judgments set time_updated = unix_timestamp(), judge_id = 0, metadata = ?, status = ? where run_id in (select run_id from runs join teams using (team_id) join divisions using (division_id) join contests_divisions_problems using(problem_id, division_id) where problem_id = ? and contest_id = ?)', $metadata, $k_judgment_none, $problem_id, $contest_id);
  }
  
  public static function getContestDivisionJudgments($contest_id, $division_id) {
    global $k_judgment_correct;
    global $k_judgment_incorrect;
    // No transaction, to prevent blocking
    $teams = self::getContestDivisionTeams($contest_id, $division_id);
    $team_ids = array();
    foreach ($teams as $team) {
      array_push($team_ids, $team['team_id']);
    }
    $problems = self::getContestDivisionProblems($contest_id, $division_id);
    $problem_ids = array();
    foreach ($problems as $problem) {
      array_push($problem_ids, $problem['problem_id']);
    }
    $judgments = array();
    if (count($team_ids) > 0 && count($problem_ids) > 0) {
      $judgments = self::querySelect('select team_id, problem_id, time_submitted, judgments.status as judgment from runs join judgments using (run_id) where (judgments.status = ? or judgments.status = ?) and team_id in (' . implode(',', $team_ids) . ') and problem_id in (' . implode(',', $problem_ids) . ') order by time_submitted asc', $k_judgment_incorrect, $k_judgment_correct);
    }
    
    return array('teams' => $teams, 'problems' => $problems, 'judgments' => $judgments);
  }
  
  public static function getTeamSubmissions($contest_id, $team_id, $division_id) {
    return self::querySelect('select time_submitted, alias, title, judgments.status as judgment, judgments.metadata as judgment_metadata from runs join judgments using (run_id) join problems using (problem_id) join contests_divisions_problems using (problem_id) where contest_id = ? and team_id = ? and division_id = ? order by time_submitted desc', $contest_id, $team_id, $division_id);
  }
  
  public static function addPost($contest_id, $team_id, $message) {
    global $k_post_unread;
    return self::queryInsert('insert into posts set contest_id = ?, team_id = ?, text = ?, time_posted = unix_timestamp(), status = ?', $contest_id, $team_id, $message, $k_post_unread);
  }
  
  public static function readPost($post_id) {
    global $k_post_read;
    return self::queryUpdate('update posts set status = ? where post_id = ?', $k_post_read, $post_id);
  }
  
  public static function replyPost($contest_id, $team_id, $ref_id, $message) {
    global $k_post_reply;
    return self::queryInsert('insert into posts set contest_id = ?, team_id = ?, ref_id = ?, text = ?, time_posted = unix_timestamp(), status = ?', $contest_id, $team_id, $ref_id, $message, $k_post_reply);
  }
  
  public static function broadcastPost($contest_id, $message) {
    global $k_post_broadcast;
    return self::queryInsert('insert into posts set contest_id = ?, text = ?, time_posted = unix_timestamp(), status = ?', $contest_id, $message, $k_post_broadcast);
  }
  
  public static function getTeamPosts($contest_id, $team_id) {
    global $k_post_broadcast;
    return self::querySelect('select post_id, ref_id, text, time_posted, status from posts where contest_id = ? and (team_id = ? or status = ?) order by time_posted desc, post_id desc', $contest_id, $team_id, $k_post_broadcast);
  }
  
  public static function getContestPosts($contest_id, $statuses) {
    $posts = array();
    if (count($statuses) > 0) {
      $posts = self::querySelect('select post_id, team_id, username, ref_id, text, time_posted, posts.status as status from posts left join teams using (team_id) where contest_id = ? and posts.status in (' . implode(',', $statuses) . ') order by time_posted desc, post_id desc', $contest_id);
    }
    return $posts;
  }
  
  public static function getRuns($contest_id, $count) {
    return self::querySelect('select run_id, time_submitted, username, division_id, division_name, contests_divisions_problems.alias as problem_alias, judgments.status as judgment, runs.metadata as run_metadata, time_updated, judgments.metadata as judgment_metadata, judgment_id, teams.alias as team_alias from runs join judgments using (run_id) join teams using (team_id) join divisions using (division_id) join problems using (problem_id) join contests_divisions_problems using (problem_id, division_id) where contest_id = ? order by time_submitted desc limit ' . $count, $contest_id);
  }
  
  public static function getRun($contest_id, $run_id) {
    return self::querySelectUnique('select run_id, problem_id, team_id, payload, time_submitted, runs.metadata as run_metadata, problem_type, contests_divisions_problems.alias as problem_alias, problems.metadata as problem_metadata, division_id, division_metadata, username as team_username from runs join problems using (problem_id) join teams using (team_id) join contests_divisions_problems using (problem_id, division_id) where run_id = ? and contest_id = ?', $run_id, $contest_id);
  }
  
}
?>