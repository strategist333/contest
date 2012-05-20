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
    global $k_post_unread;
    global $k_post_read;
    global $k_post_reply;
    global $k_post_broadcast;
    $metadata = json_decode($g_curr_contest['metadata'], true);
    
// BEGIN RENDER SCRIPTS
?>

<script type="text/javascript" src="/js/jquery.ocupload.min.js"></script>
<script type="text/javascript" src="/js/jquery.stringify.min.js"></script>
<script type="text/javascript">
(function($) {
  var timerOffset = 0;
  var timerOffsetCount = 0;
  var contestStartTime = <?= $g_curr_contest['time_start'] ?>;
  var contestEndTime = <?= $g_curr_contest['time_start'] + $g_curr_contest['time_length'] ?>;
  var scoreboardFreezeTime = <?= $g_curr_contest['time_start'] + $metadata['time_freeze'] ?>;
  var submissionsIntervalID = 0;
  var scoreboardIntervalID = 0;
  var clarificationsIntervalID = 0;
  var timerIntervalID = 0;
  
  function formatTime(ts) {
    var h = Math.floor(ts / 3600);
    var m = Math.floor(ts % 3600 / 60);
    var s = ts % 60;
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
            var tr = $("<tr>").append($("<td>").text(formatTime(submission['time_submitted'] - contestStartTime)))
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
            loadScoreboard();
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
  
  function loadScoreboard() {
    $.ajax({
      data: $.stringifyJSON({'action' : 'load_scoreboard'}),
      success: function(ret) {
        if (ret['success']) {
          var scoreboard = ret['scoreboard'];
          var thr = $("<tr>").append($("<th>").text("Rank"))
                             .append($("<th>").text("Team"));
          $.each(scoreboard['problems'], function(index, problem) {
            thr.append($("<th>").text(problem['alias']));
          });
          thr.append($("<th>").text("Score"));
          var tbody = $("<tbody>");
          $.each(scoreboard['teams'], function(index, team) {
            var tr = $("<tr>").append($("<td>").text(index + 1))
                              .append($("<td>").text(team['alias']));
            $.each(team['judgments'], function(index, judgment) {
              var td = $("<td>").text(" ");
              if (judgment == <?= $k_judgment_correct ?>) {
                td.addClass('correct');
              }
              else if (judgment == <?= $k_judgment_incorrect ?>) {
                td.addClass('incorrect');
              }
              tr.append(td);
            });
            tr.append($("<td>").text(team['score']));
            tbody.append(tr);
          });
          $("#scoreboard_table > thead").replaceWith($("<thead>").append(thr));
          $("#scoreboard_table > tbody").replaceWith(tbody);
        }
      }
    });
  }
  
  function getTime() {
    var now = new Date();
    return now.getTime() / 1000;
  }
  
  function loadTime() {
    var start = getTime();
    $.get("time.php", function(time) {
      var recv = getTime();
      var expected = start + (recv - start) / 2;
      var actual = parseFloat(time);
      var offset = actual - expected;
      timerOffset = (timerOffset * timerOffsetCount + offset) / (timerOffsetCount + 1);
      timerOffsetCount++;
      if (timerOffsetCount < 5) {
        setTimeout(loadTime, 1000 + Math.floor(Math.random() * 10000));
      }
      if (timerIntervalID == 0) {
        timerIntervalID = setInterval(refreshTimer, 500);
      }
      refreshTimer();
    });
  }
  
  function refreshTimer() {
    var now = getTime();
    if (now < contestEndTime) {
      var timeLeft = 0;
      var event;
      if (now < contestStartTime) {
        timeLeft = Math.ceil(contestStartTime - now);
        event = "start";
      }
      else {
        timeLeft = Math.ceil(contestEndTime - now);
        event = "end";
        if (!$("#problems_listing").is(":visible")) {
          $("#problems_listing").load("problems.php").show();
        }
      }
      $("#timer").text(formatTime(timeLeft) + " until " + event);
    }
    else {
      $("#timer").text("Contest is over");
    }
    if (now >= scoreboardFreezeTime && scoreboardIntervalID != 0) {
      clearInterval(scoreboardIntervalID);
      scoreboardIntervalID = 0;
    }
  }
  
  function getCanonicalTime(timeString) {
    return timeString.substr(timeString.indexOf(':') - 2, 8);
  }
  
  function loadClarifications() {
    $.ajax({
      data: $.stringifyJSON({'action' : 'get_clarifications'}),
      success: function(ret) {
        if (ret['success']) {
          var container = $("<div>");
          $.each(ret['clarifications'], function(index, message) {
            var date = getCanonicalTime(new Date(message['time_posted'] * 1000).toTimeString());
            var messageType = "";
            switch (message['type']) {
              case <?= $k_post_broadcast; ?>:
                messageType = "Broadcast message";
                break;
              case <?= $k_post_reply; ?>:
                messageType = "Reply";
                break;
              default:
                messageType = "Asked";
                break;
            }
            container.append($("<div>").append($("<h5>").text(messageType + " at " + date))
                                       .append($("<span>").text(message['text'])));
          });
          $("#messages").empty().append(container);
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
    
    $("#problems_listing").hide();
    loadTime();    
    
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
    
    $("#ask_submit").click(function() {
      var text = $.trim($("#ask_message").val());
      if (text) {
        $.ajax({
          data: $.stringifyJSON({'action' : 'post_clarification', 'message' : text}),
          success: loadClarifications
        });
      }
      return false;
    });
    submissionsIntervalID = setInterval(loadSubmissions, 5000);
    loadSubmissions();
    scoreboardIntervalID = setInterval(loadScoreboard, 5000);
    loadScoreboard();
    clarificationsIntervalID = setInterval(loadClarifications, 5000);
    loadClarifications();
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
  <div id="timer"></div>
  <div id="logo_div"></div>
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
      <table id="scoreboard_table" class="results_table" style="margin: auto;" border="1" cellspacing="0">
        <thead>
        </thead>
        <tbody>
        </tbody>
      </table>
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
        <textarea id="ask_message" style="width: 90%; margin: auto;" rows="5"></textarea>
        <br />
        <button id="ask_submit">Submit Question</button>
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
    <div id="messages">
    </div>
  </div>  
</div>
<?php
// END RENDER CLARIFICATIONS VIEW
  }
}
?>
