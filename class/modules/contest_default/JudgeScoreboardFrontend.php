<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class JudgeScoreboardFrontend extends JudgeFrontend {
  
  protected function renderStyles() {
// BEGIN RENDER STYLES
?>
<style type="text/css">
#scoreboard_table_div {
  height: auto;
}

#header {
  position: static;
  text-align: center;
}

#timer {
  display: inline-block;
}

#division_div {
  position: absolute;
  top: 10px;
  right: 10px;
}
</style>
<?php
// END RENDER STYLES
  }
  
  protected function renderScripts() {
    parent::renderScripts();
    global $g_curr_contest;
    global $k_judgment_incorrect;
    global $k_judgment_correct;
    $metadata = json_decode($g_curr_contest['metadata'], true);
// BEGIN RENDER SCRIPTS
?>
<script type="text/javascript">
(function($) {  
  var contestStartTime = <?= $g_curr_contest['time_start'] ?>;
  var contestEndTime = <?= $g_curr_contest['time_start'] + $g_curr_contest['time_length'] ?>;
  var scoreboardFreezeTime = <?= $g_curr_contest['time_start'] + $metadata['time_freeze'] ?>;
  var judgmentCorrect = <?= $k_judgment_correct ?>;
  var judgmentIncorrect = <?= $k_judgment_incorrect ?>;
  var timerOffset = 0;
  var timerOffsetCount = 0;
  var scoreboardIntervalID = 0;
  var timerIntervalID = 0;
  var submitAllowed = false;
  
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
  
  function loadScoreboard() {
    $.ajax({
      data: $.stringifyJSON({'action' : 'load_scoreboard', 'division_id' : $("#division_id").val()}),
      success: function(ret) {
        if (ret['success']) {
          var scoreboard = ret['scoreboard'];
          var thr = $("<tr>").append($("<th>").text("Rank"))
                             .append($("<th>").text("Team"))
                             .append($("<th>").text("Username"));
          $.each(scoreboard['problems'], function(index, problem) {
            thr.append($("<th>").text(problem['alias']));
          });
          thr.append($("<th>").text("Time"))
             .append($("<th>").text("Score"));
          var tbody = $("<tbody>");
          $.each(scoreboard['teams'], function(index, team) {
            var tr = $("<tr>").append($("<td>").text(index + 1))
                              .append($("<td>").text(team['alias']))
                              .append($("<td>").text(team['username']));
            $.each(team['judgments'], function(index, judgment) {
              var td = $("<td>").text(" ");
              if (judgment == judgmentCorrect) {
                td.addClass('correct');
              }
              else if (judgment == judgmentIncorrect) {
                td.addClass('incorrect');
              }
              tr.append(td);
            });
            tr.append($("<td>").text(team['time'] ? formatTime(team['time'] - contestStartTime) : '---'));
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
    $.get("/time.php", function(time) {
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
        submitAllowed = false;
      }
      else {
        timeLeft = Math.ceil(contestEndTime - now);
        event = "end";
        if (!$("#problems_listing").is(":visible")) {
          $("#problems_listing").load("problems.php").show();
        }
        submitAllowed = true;
      }
      $("#timer").text(formatTime(timeLeft) + " until " + event);
    }
    else {
      submitAllowed = false;
      $("#timer").text("Contest is over").addClass("expired");
    }
    if (now >= scoreboardFreezeTime && scoreboardIntervalID != 0) {
      clearInterval(scoreboardIntervalID);
      scoreboardIntervalID = 0;
    }
  }
  
  function getCanonicalTime(timeString) {
    return timeString.substr(timeString.indexOf(':') - 2, 8);
  }
  
  $(document).ready(function() {
    $.ajaxSetup({
      url: "/restricted/handle.php",
      type: "post",
      jsonp: false,
      processData: false,
      dataType: "json"
    });
    
    loadTime();
    
    $("#division_id").change(function() {
      loadScoreboard();
    }).change();
    
    scoreboardIntervalID = setInterval(loadScoreboard, 15000);
  });
})(window.jQuery);
</script>
<?php
// END RENDER SCRIPTS
  }
  
  protected function renderHeader() {
    global $g_curr_contest;
// BEGIN RENDER HEADER
?>
<div id="header">
  <div id="logo_div"></div>
  <div id="timer"></div>
  <div id="division_div">
    Select a division:
    <select id="division_id">
<?php
    if ($g_curr_contest) {
      $divisions = DBManager::getContestDivisions($g_curr_contest['contest_id']);
      foreach ($divisions as $division) {
        print '<option value="' . $division['division_id'] . '">' . $division['division_name'] . '</option>';
      }
    }
?>
    </select>
  </div>
</div>
<?php
// END RENDER HEADER
  }
  
  protected function renderBody() {
    $this->renderScoreboard();
  }
  
  protected function renderFooter() {
// BEGIN RENDER FOOTER
// END RENDER FOOTER
  }
  
  
  protected function renderScoreboard() {
// BEGIN RENDER SCOREBOARD
?>
<div id="scoreboard_table_div">
  <div class="div_padding">
    <div class="div_title">Scoreboard</div>
    <table id="scoreboard_table" class="results_table" border="1" cellspacing="0">
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
}
?>
