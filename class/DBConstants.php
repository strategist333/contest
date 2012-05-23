<?php
  $k_post_unread = 0;
  $k_post_read = 1;
  $k_post_reply = 2;
  $k_post_broadcast = 3;
  
  $k_judge_none = 0;
  
  $k_run_active = 1;
  
  $k_judgment_none = 0;
  $k_judgment_pending = 1;
  $k_judgment_incorrect = 2;
  $k_judgment_correct = 3;
  
  $k_judgment_maxdelay = 180;
  
  $k_judgments = array(
    "No judgment",
    "Judgment pending",
    "Correct",
    "Compile error",
    "Time limit exceeded",
    "Runtime error",
    "Presentation error",
    "Wrong answer",
    "Internal error"
  );
  
  $k_contest_inactive = 0;
  $k_contest_active = 1;
  
  $k_team_inactive = 0;
  $k_team_active = 1;
  
  $k_problem_inactive = 0;
  $k_problem_active = 1;
  
  $k_months = array(
    "January", "February", "March",
    "April", "May", "June",
    "July", "August", "September",
    "October", "November", "December"
  );
  
  $k_teams_fields = array('team_id', 'username', 'password', 'alias', 'division_name');
  $k_problems_fields = array('problem_type', 'title', 'status', 'metadata', 'order_seq');
  $k_contest_divisions_problems_fields = array('url', 'alias', 'point_value', 'division_metadata');
  
  $k_problems_grace_period = 5;
?>
