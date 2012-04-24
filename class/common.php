<?php
require_once('DBManager.php');

// Get current contest
$g_curr_contest = DBManager::getCurrentContest();

function loadClassInfo($class_infos, $params) {
  foreach ($class_infos as $class_info) {
    $filepath = __DIR__ . '/modules/contest.' . $class_info['contest_type'] . '/' . $class_info['class_name']. '.php';
    if (file_exists($filepath)) {
      include_once($filepath);
      if (is_array($params)) {
        $reflection_obj = new ReflectionClass($class_info['class_name']);
        return $reflection_obj->newInstanceArgs($params); 
      }
      else {
        break;
      }
    }
  }
  return false;
}

function __autoload($base_class_name) {
  global $g_curr_contest;
  $class_infos = array();
  if ($g_curr_contest) {
    array_push($class_infos, array('class_name' => $base_class_name, 'contest_type' => $g_curr_contest['contest_type']));
  }
  array_push($class_infos, array('class_name' => $base_class_name, 'contest_type' => 'default'));
  loadClassInfo($class_infos, false);
}

function load($curr_contest, $base_class_name) {
  $class_infos = array();
  if ($curr_contest) {
    array_push($class_infos, array('class_name' => ucfirst($curr_contest['contest_type']) . $base_class_name, 'contest_type' => $curr_contest['contest_type']));
    array_push($class_infos, array('class_name' => $base_class_name, 'contest_type' => $curr_contest['contest_type']));
  }
  array_push($class_infos, array('class_name' => $base_class_name, 'contest_type' => 'default'));
  return loadClassInfo($class_infos, array_slice(func_get_args(), 2));
}

function footer() {
  print <<<HEREDOC
<hr><p><small>
Copyright (c) 2010-12 Frank Li<br>
</small></p>
HEREDOC;
}
?>