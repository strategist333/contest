<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class TeamFrontend {
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
<!--[if IE 7]>
<link rel="stylesheet" type="text/css" href="/css/ie7.css" />
<![endif]-->
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
?>
<div id="header">
  <div id="logo_div"></div>
  <div id="timer"></div>
</div>
<?php
// END RENDER HEADER
  }
  
  protected function renderFooter() {
// BEGIN RENDER FOOTER
// END RENDER FOOTER
  }
  
  protected function renderBody() {
    print '<div class="tablecell left">' . "\n";
    $this->renderLeft();
    print '</div>';
    print '<div class="tablecell middle">' . "\n";
    $this->renderMiddle();
    print '</div>';
    print '<div class="tablecell right">' . "\n";
    $this->renderRight();
    print '</div>';
  }
  
  protected function renderLeft() {
    $this->renderUpload();
    $this->renderProblems();
  }
  
  protected function renderMiddle() {
    $this->renderSubmissions();
    $this->renderScoreboard();
  }
  
  protected function renderRight() {
    $this->renderClarificationsPost();
    $this->renderClarificationsView();
  }  
  
  protected function renderUpload() {
// BEGIN RENDER UPLOAD
?>
<div id="upload">
  <div class="div_padding">
    <div class="div_title">Submit Solution</div>
    <div id="submissions_upload_status">Select a file</div>
    <div id="submissions_upload_button">
      <button id="submissions_upload">Upload</button>
    </div>
  </div>
</div>
<?php
// END RENDER UPLOAD
  }
  
  protected function renderProblems() {
// BEGIN RENDER PROBLEMS
?>
<div id="problems">
  <div class="div_padding">
    <div class="div_title">Problems</div>
    <div id="problems_listing" class="listmenu"></div>
  </div>
</div>
<?php
// END RENDER PROBLEMS
  }

  protected function renderSubmissions() {
// BEGIN RENDER SUBMISSIONS
?>
<div id="submissions_table_div">
  <div class="div_padding">
    <div class="div_title">Submission Results</div>
    <table id="submissions_table" class="results_table">
      <thead>
        <tr>
          <th>Time</th>
          <th>Problem</th>
          <th>Title</th>
          <th>Judgment</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>
</div>
<?php
// END RENDER SUBMISSIONS
  }
  
  protected function renderScoreboard() {
// BEGIN RENDER SCOREBOARD
?>
<div id="scoreboard_table_div">
  <div class="div_padding">
    <div class="div_title">Scoreboard</div>
    <table id="scoreboard_table" class="results_table">
      <thead>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>    
</div>
<?php
// END RENDER SCOREBOARD
  }
  protected function renderClarificationsPost() {
// BEGIN RENDER CLARIFICATIONS POST
?>
<div id="ask">   
  <div class="div_padding">
    <div class="div_title">Request Clarification</div>
    <textarea id="ask_message" rows="5"></textarea>
    <br />
    <button id="ask_submit">Submit Question</button>
  </div>
</div>
<?php
// END RENDER CLARIFICATIONS POST
  }
  
  protected function renderClarificationsView() {
// BEGIN RENDER CLARIFICATIONS VIEW
?>
<div id="answers">
  <div class="div_padding">  
    <div class="div_title">Clarification History</div>
    <div id="messages">
    </div>
  </div>  
</div>
<?php
// END RENDER CLARIFICATIONS VIEW
  }
}
?>
