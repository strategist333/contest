<?php
require_once(__DIR__ . '/../../common.php');

class TeamLogin {
  public function render() {
// BEGIN RENDER
?>

<?php
  $username = isset($_POST['username']) ? html_entity_decode($_POST['username']) : false;
  $password = isset($_POST['password']) ? html_entity_decode($_POST['password']) : false;
  
  $bodyHTML = array('');
  
  // Authenticate the team
  if ($username && $password) {
    if (isset($_SESSION['login'])) {
      unset($_SESSION['login']);
    }
    $session_info = DBManager::login($username, $password);
    if ($session_info) {
      $_SESSION['login'] = $session_info;
      header('Location: index.php');
    }
    else {
      array_push($bodyHTML, '<font color="red"><p>Invalid username or password!</p></font>');
    }
  }
  // Otherwise log the team out
  else if (isset($_SESSION['login']))
  {
    unset($_SESSION['login']);
    array_push($bodyHTML, '<p><b><big>Now logged out.</big></b></p>');
  }
?>

<html>
<head>
<title>Log In</title>
<link href="main.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="/js/jquery-1.7.2.min.js"></script>

<?php
  if (!isset($_SESSION['login'])) {
?>
<script type="text/javascript">
  <!--
  $(document).ready( function() {
    $("#username-input").focus();
  });
  //-->
</script>
<?php
  }
?>

</head>

<body>

<div id="logo-div">
  <img src="images/proco_logo64.png">
</div>

<div align="center">
<h1>Team Log In</h1>
</div>

<hr>
<div align="center">

<?php
  print implode("\n", $bodyHTML);
  if (!isset($_SESSION['login'])) {
?>
<span class="div-title">Warning: Team name is <b>case-sensitive</b>!!!</span>
<br/>
<br/>
<form name="login" method="post" action="login.php">
<table border="0" width="400">
<tr><td style="text-align: right;">Team:</td><td><input id="username-input" type="text" name="username" /></td></tr>
<tr><td style="text-align: right;">Password:</td><td><input type="password" name="password" /></td></tr>
<tr align="center"><td colspan="2"><input type="submit" value="Log Me In!" /></td></tr>
</table>
</form>
<?php
  }
?>

</div>
<?php footer(); ?>
</body>
</html>

<?php
// END RENDER
  }
}
?>