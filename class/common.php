<?php
require_once('DBManager.php');

// Get current contest
$g_curr_contest = DBManager::getCurrentContest();

function currentContestType() {
  global $g_curr_contest;
  return $g_curr_contest ? $g_curr_contest['contest_type'] : 'default';
}

function loadClassInfo($curr_contest_type, $class_names, $params) {

  $folders = array('contest_' . $curr_contest_type);
  if ($curr_contest_type != 'default') {
    array_push($folders, 'contest_default');
  }
  foreach ($folders as $folder) {
    $it = new RecursiveDirectoryIterator(__DIR__ . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $folder);
    foreach (new RecursiveIteratorIterator($it) as $file) {
      if (in_array(basename($file, '.php'), $class_names)) {
        include_once($file);
        if (is_array($params)) {
          $reflection_obj = new ReflectionClass(basename($file, '.php'));
          return $reflection_obj->newInstanceArgs($params); 
        }
        else {
          break;
        }
      }
    }
  }
  return false;
}

function __autoload($base_class_name) {
  loadClassInfo(currentContestType(), array($base_class_name), false);
}

function load($curr_contest_type, $base_class_name) {
  return call_user_func_array('loadWithPrefix', array_merge(array($curr_contest_type, $curr_contest_type, $base_class_name), array_slice(func_get_args(), 2)));
}

function loadWithPrefix($curr_contest_type, $prefix, $base_class_name) {
  $class_names = array($base_class_name);
  if ($curr_contest_type != 'default') {
    array_push($class_names, ucfirst($prefix) . $base_class_name);
  }
  return loadClassInfo($curr_contest_type, $class_names, array_slice(func_get_args(), 3));
}

function judgeLinkPanel() {
?>
<div>
Configure: 
<a href="contests.php">Contests</a>
<a href="divisions.php">Divisions</a>
<a href="teams.php">Teams</a>
<a href="problems.php">Problems</a>
|
Monitor:
<a href="index.php">Scoreboard</a>
<a href="clars.php">Clarifications</a>
<a href="judgments.php">Judgments</a>
</div>
<?php
}

function footer() {
?>
<div id="footer">
<small>
Copyright (c) 2010-12 Frank Li, Frank Chen, Wendy Mu
</small>
</div>
<?php
}
?>