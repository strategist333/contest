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
    global $g_curr_contest;
    global $k_judgment_none;
    global $k_judgment_pending;
    global $k_judgment_incorrect;
    global $k_judgment_correct;
    
// BEGIN RENDER SCRIPTS
?>

<script type="text/javascript" src="/js/jquery.ocupload.min.js"></script>
<script type="text/javascript" src="/js/jquery.stringify.min.js"></script>
<script type="text/javascript">
(function($) {
  var contestStartTime = <?= $g_curr_contest['time_start'] ?>;
  var submissionsIntervalID = 0;
  
  function formatTime(ts) {
    var ds = ts - contestStartTime;
    var h = Math.floor(ds / 3600);
    var m = Math.floor(ds % 3600 / 60);
    var s = ds % 60;
    var ar = $.map([h, m, s], function(elem) {
      if (elem < 10) { return "0" + elem; }
      return "" + elem;
    })    
    return ar.join(":");
  }

  function loadSubmissions() {
    $.ajax({
      data: $.stringifyJSON({'action' : 'load_submissions'}),
      success: function(ret) {
        if (ret['success']) {
          var tbody = $("<tbody>");
          var outstanding = 0;
          $.each(ret['submissions'], function(index, submission) {
            var tr = $("<tr>").append($("<td>").text(formatTime(submission['time_submitted'])))
                              .append($("<td>").text(submission['alias']))
                              .append($("<td>").text(submission['title']))
                              .append($("<td>").text(submission['message']));
            if (submission['judgment'] == <?= $k_judgment_correct ?>) {
              tr.addClass('correct');
            }
            else if (submission['judgment'] == <?= $k_judgment_incorrect ?>) {
              tr.addClass('incorrect');
            }
            else {
              tr.addClass('pending');
              outstanding++;
            }
            tbody.append(tr);
          });
          if (outstanding == 0 && ret['submissions'].length != 0) {
            clearInterval(submissionsIntervalID);
            submissionsIntervalID = setInterval(loadSubmissions, 180000);
          }
          else if (outstanding != 0) {
            clearInterval(submissionsIntervalID);
            submissionsIntervalID = setInterval(loadSubmissions, 5000);
          }
          $("#submissions_table > tbody").replaceWith(tbody);
        }
      }
    });
  }
  $(document).ready(function() {
    $.ajaxSetup({
      url: "handle.php",
      type: "post",
      processData: false,
      dataType: "json"
    });
    
    $("#submissions_upload").upload({
      name: 'team_file',
      action: 'handlefile.php',
      autoSubmit: false,
      params: {'action' : 'upload_submission', 'MAX_FILE_SIZE' : 1000000},
      onSelect: function() {
        var filename = this.filename().replace(/.*(\/|\\)(.*)/g, "$2");
        if (filename.match(/\.(c|java|cc|cpp|py)$/)) {
          this.submit();
        }
        else {
          $("#submissions_upload_status_ajax").text("Invalid extension");
        }
      },
      onComplete: function(response) {
        ret = $.parseJSON(response);
        if (ret['success']) {
          $("#submissions_upload_status_ajax").text("Select a file");
          loadSubmissions();
        }
        else {
          $("#submissions_upload_status_ajax").text(ret['error']);
        }
      }
    });
    submissionsIntervalID = setInterval(loadSubmissions, 1800000);
    loadSubmissions();
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
  <div id="timer_div">
    Contest is over
  </div>
  <div id="logo_div">
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
  <div id="quote_marquee">
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
<div id="upload">
  <div class="div_padding">
    <div class="div_title">Submit a<br />Solution File:</div>
    <button id="submissions_upload" style="height: 30px; ">Upload</button>
    <div id="submissions_upload_status">Select a file</div>
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
    <div id="problems_listing" class="listmenu">
    </div>
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
    <div id="submissions_table_ajax">
      <table id="submissions_table" class="results_table" style="margin: auto;" border="1" cellspacing="0">
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
    <div id="scoreboard_table_ajax">
      <table id="scoreboard_table" class="results_table" style="margin: auto;" border="1" cellspacing="0"></table>
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
  <div class="div_padding">
    <div class="div_title">Clarification Requests</div>
    <form>
      <center>
        <textarea style="width: 90%; margin: auto;" rows="5" id="ask_area"></textarea>
        <br />
        <input type="button" id="ask_submit" value="Submit Question"></input>
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
  <div class="div_padding">  
    <div class="div_title">Clarification History</div>
    <div id="clarification_ajax">
    </div>
  </div>  
</div>
<?php
// END RENDER CLARIFICATIONS VIEW
  }
}
?>