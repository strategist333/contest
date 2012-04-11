<?php
require_once ('DBManager.php');

// Enable session variables and set up database
session_start();

// Get current contest
$g_current_contest_id = DBManager::exec('getCurrentContestID');

function footer()
{
  print <<<HEREDOC
<hr><p><small>
Copyright (c) 2010-12 Frank Li<br>
</small></p>
HEREDOC;
}
?>