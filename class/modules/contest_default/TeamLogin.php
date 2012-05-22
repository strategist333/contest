<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

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
<!doctype html>
<html>
<head>
<title>Log In</title>
<link href="/css/reset.css" rel="stylesheet" type="text/css">
<link href="/css/login.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="/js/jquery-1.7.2.min.js"></script>

<?php
  if (!isset($_SESSION['login'])) {
?>
<script type="text/javascript">
(function ($) {
  $(document).ready( function() {
    $("#username_input").focus();
  });
})(window.jQuery);
</script>
<?php
  }
?>

</head>

<body>

<div id="logo_div">
  <img src="images/proco_vector.png" alt="ProCo">
</div>

<div align="center">
<h1>Team Log In</h1>
</div>

<div align="center">
<?php
  print implode("\n", $bodyHTML);
  if (!isset($_SESSION['login'])) {
?>
<span class="div_title">Warning: Team name is <b>case-sensitive</b>!!!</span>
<br/>
<br/>
<form name="login" method="post" action="login.php">
<div id="input_div"><input id="username_input" type="text" name="username" /></div>
<div id="input_div"><input type="password" name="password" /></div>
<div id="input_div"><button>Log in!</button></div>
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