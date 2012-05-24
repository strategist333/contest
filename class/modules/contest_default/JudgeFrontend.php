<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class JudgeFrontend {
  public function render() {
    global $g_curr_contest;
// BEGIN RENDER
?>

<!doctype html>
<html>
<head>
<title><?= ($g_curr_contest ? ($g_curr_contest['contest_name'] . ' ') : '')?>Contest Portal</title>
<link rel="shortcut icon" href="favicon.ico" />
<link rel="stylesheet" type="text/css" href="/css/reset.css" />
<link rel="stylesheet" type="text/css" href="/css/main.css" />
<?php
    $this->renderStyles();
?>
</head>
<body>
<div id="container">

<?php
    $this->renderHeader();
    $this->renderBody();
    $this->renderFooter();
?>
</div>
<?php
    $this->renderScripts();
?>
</body>
</html>

<?php
// END RENDER
  }
  
  protected function renderStyles() {
// BEGIN RENDER STYLES
// END RENDER STYLES
  }
  
  protected function renderScripts() {
// BEGIN RENDER SCRIPTS
?>
<script type="text/javascript" src="/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="/js/jquery.stringify.min.js"></script>
<?php
// END RENDER SCRIPTS
  }
  
  protected function renderHeader() {
// BEGIN RENDER HEADER
// END RENDER HEADER
  }
  
  protected function renderBody() {
// BEGIN RENDER BODY
// END RENDER BODY
  }
  
  protected function renderFooter() {
// BEGIN RENDER FOOTER
// END RENDER FOOTER
  }
  
}
?>
