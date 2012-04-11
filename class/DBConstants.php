<?php
  $k_post_unread = 0;
  $k_post_read = 1;
  $k_post_reply = 2;
  $k_post_broadcast = 3;
  
  $k_judge_none = 0;
  
  $k_run_active = 1;
  
  $k_judgment_invalid = 99;
  $k_judgment_none = 0;
  $k_judgment_pending = 1;
  $k_judgment_correct = 2;
  $k_judgment_maxdelay = 15;
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
  
  $k_problem_active = 1;
  $k_problem_inactive = 0;
  $k_problem_deleted = 2;
  
  $k_months = array(
    "January", "February", "March",
    "April", "May", "June",
    "July", "August", "September",
    "October", "November", "December"
  );
  
?>
