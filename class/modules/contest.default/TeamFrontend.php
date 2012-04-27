<?php
require_once(__DIR__ . '/../../common.php');

class TeamFrontend {
  public function render() {
    global $g_curr_contest;
// BEGIN RENDER
?>

<!-- IE quirks rendering mode trigger -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<title><?= ($g_curr_contest ? ($g_curr_contest['contest_name'] . ' ') : '')?>Contest Portal</title>
<link rel="shortcut icon" href="favicon.ico" />
<link href="/css/main.css" rel="stylesheet" type="text/css">
<!--[if IE]>
<link href="/css/ie.css" rel="stylesheet" type="text/css">
<![endif]--> 

<link href="/css/main.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="/js/si.files.js"></script>
<?php
    $this->renderScripts();
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
</body>
</html>

<?php
// END RENDER
  }
  
  protected function renderScripts() {
// BEGN RENDER SCRIPTS
?>
<script type="text/javascript">
(function($) {
  $(document).ready(function() {
    $("#submissions-upload-submit").click(function() {
      // Check for preliminary valid file name
      var filename = $("#submissions-upload-file").val().replace(/.*(\/|\\)(.*)/g, "$2");
      if(filename.match(/\.(c|java|cc|cpp|py)$/)) {
        $("#submissions-upload-form").submit();
        $("#submissions-upload-status-ajax").text("Uploading..."); 
        $("#submissions-upload-submit").attr("disabled","disabled");
        uploadTimerId = setTimeout(abortUpload, 30000);
      }
      else {
        $("#submissions-upload-status-ajax").text("Invalid filename");
      }
    });
  });
})(window.jQuery);
</script>
<?php
// END RENDER SCRIPTS
  }
  
  protected function renderHeader() {
// BEGIN RENDER HEADER
?>
<div id="header">
  <div id="timer-div">
    Contest is over
  </div>
  <div id="logo-div">
  </div>
  <div id="title">
    <h1>Team Control Panel</h1>
  </div>
</div>
<?php
// END RENDER HEADER
  }
  
  protected function renderFooter() {
// BEGIN RENDER FOOTER
?>
<div id="footer">
<span>
  <div id="quote-marquee">
    &#8220;Beware of bugs in the above code; I have only proved it correct, not tried it.&#8221; &mdash; Donald Knuth
  </div>
</span>
</div>
<?php
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
<div id="submissions-upload">
  <div class="div-padding">
    <div class="div-title">Submit a<br />Solution File:</div>
    <div class="form">
      <form id="submissions-upload-form" action="upload.php" method="post" enctype="multipart/form-data" target="submissions-upload-target"> 
        <div class="cabinet"> 
          <input type="hidden" name="MAX_FILE_SIZE" value="1000000">
          <input type="file" class="file" id="submissions-upload-file" name="team_file" ></input>
          <div class="image">
          
          </div>
          <div class="filename" id="submissions-upload-filename">
          </div>
        </div>
        <input type="button" id="submissions-upload-submit" value="Submit"></input><br />
        <iframe id="submissions-upload-target" name="submissions-upload-target" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe> 
      </form>
    </div>
    <div id="submissions-upload-status">
      <div id="submissions-upload-status-ajax">
        No file uploaded
      </div>
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
  <div class="div-padding">
    <div class="div-title">Problems</div>
    <div id="problems-listing" class="listmenu">
    </div>
  </div>
</div>
<?php
// END RENDER PROBLEMS
  }

  protected function renderSubmissions() {
// BEGIN RENDER SUBMISSIONS
?>
<div id="submissions-table-div">
  <div class="div-padding">
    <div class="div-title">Submission Results</div>
    <div id="submissions-table-ajax">
      <table id="submissions-table" class="results-table" style="margin: auto;" border="1" cellspacing="0"></table>
    </div>
  </div>
</div>
<?php
// END RENDER SUBMISSIONS
  }
  
  protected function renderScoreboard() {
// BEGIN RENDER SCOREBOARD
?>
<div id="scoreboard-table-div">
  <div class="div-padding">
    <div class="div-title">Scoreboard</div>
    <div id="scoreboard-table-ajax">
      <table id="scoreboard-table" class="results-table" style="margin: auto;" border="1" cellspacing="0"></table>
    </div>
  </div>    
</div>
<?php
// END RENDER SCOREBOARD
  }
  
  protected function renderClarificationsPost() {
// BEGIN RENDER CLARIFICATIONS POST
?>
<div id="ask">   
  <div class="div-padding">
    <div class="div-title">Clarification Requests</div>
    <form>
      <center>
        <textarea style="width: 90%; margin: auto;" rows="5" id="ask-area"></textarea>
        <br />
        <input type="button" id="ask-submit" value="Submit Question"></input>
      </center>
    </form>

  </div>
</div>
<?php
// END RENDER CLARIFICATIONS POST
  }
  
  protected function renderClarificationsView() {
// BEGIN RENDER CLARIFICATIONS VIEW
?>
<div id="answers">
  <div class="div-padding">  
    <div class="div-title">Clarification History</div>
    <div id="clarification-ajax">
    </div>
  </div>  
</div>
<?php
// END RENDER CLARIFICATIONS VIEW
  }
}
?>