<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class JudgeViewJudgments {
  
  public function render() {
    global $g_curr_contest;
    global $k_judgment_correct;
    global $k_judgment_incorrect;
// BEGIN RENDER
?>
<html>
<head>
<title>Judgments</title>
<link href="/css/main.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="/js/jquery.stringify.min.js"></script>
<script type="text/javascript">
(function ($) {
  var judgmentCorrect = <?= $k_judgment_correct ?>;
  var judgmentIncorrect = <?= $k_judgment_incorrect ?>;
  var contestStartTime = <?= $g_curr_contest['time_start'] ?>;
  
  function renderPayloadTD(run, td) {
<?php
  $this->renderPayloadTDBody();
?>
  }
  
  function renderUpdateTD(run, td) {
    
  }

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
  
  function loadRuns() {
    $.ajax({
      data: $.stringifyJSON({'action' : 'get_runs', 'contest_id' : <?= $g_curr_contest['contest_id'] ?>, 'count' : parseInt($("#num_runs").val())}),
      success: function(ret) {
        if (ret['success']) {
          var tbody = $("<tbody>");
          $.each(ret['pending'], function(index, run) {
            var tr = $("<tr>")
              .append($("<td>").text(formatTime(run['time_submitted'] - contestStartTime)))
              .append($("<td>").text(run['team_alias']))
              .append($("<td>").text(run['username']))
              .append($("<td>").text(run['division_name']))
              .append($("<td>").text(run['problem_alias']))
            var payloadTD = $("<td>");
            renderPayloadTD(run, payloadTD);
            tr.append(payloadTD);
            tr.addClass("pending").append($("<td>").text("Pending"))
              .append($("<td>").text(formatTime(run['time_updated'] - contestStartTime)))
              .append($("<td>").text(formatTime(run['time_updated'] - run['time_submitted'])));
            tr.append($("<td>").append($("<button>").text("Regrade").click((function(username, problem, judgmentID, divisionID) {
                        return function() {
                          if (confirm("Update (" + username + ", " + problem + ") to pending?")) {
                            $.ajax({
                              data: $.stringifyJSON({'action' : 'clear_judgment', 'judgment_id' : judgmentID, 'contest_id' : <?= $g_curr_contest['contest_id'] ?>, 'division_id' : divisionID}),
                              success: function(ret) {
                                if (ret['success']) {
                                  loadRuns();
                                }
                              }
                            });
                          }
                        }
                      })(run['username'], run['problem_alias'], run['judgment_id'], run['division_id']))));
            tbody.append(tr);
          });
          $.each(ret['done'], function(index, run) {
            var tr = $("<tr>")
              .append($("<td>").text(formatTime(run['time_submitted'] - contestStartTime)))
              .append($("<td>").text(run['team_alias']))
              .append($("<td>").text(run['username']))
              .append($("<td>").text(run['division_name']))
              .append($("<td>").text(run['problem_alias']));
            var payloadTD = $("<td>");
            renderPayloadTD(run, payloadTD);
            tr.append(payloadTD);
            var updateTD = $("<td>");
            if (run['judgment'] == judgmentCorrect) {
              tr.addClass("correct").append($("<td>").text("Correct"));
              updateTD.append($("<button>").text("Incorrect").click((function(username, problem, judgmentID, divisionID) {
                return function() {
                  var errorMessage = prompt("Update (" + username + ", " + problem + ") to incorrect with message:");
                  if (errorMessage) {
                    $.ajax({
                      data: $.stringifyJSON({'action' : 'submit_judgment', 'judgment_id' : judgmentID, 'judge_id' : 0, 'correct' : false, 'metadata' : $.stringifyJSON({'error' : errorMessage}), 'contest_id' : <?= $g_curr_contest['contest_id'] ?>, 'division_id' : divisionID}),
                      success: function(ret) {
                        if (ret['success']) {
                          loadRuns();
                        }
                      }
                    });
                  }
                }
              })(run['username'], run['problem_alias'], run['judgment_id'], run['division_id'])));
            }
            else {
              tr.addClass("incorrect").append($("<td>").text(run['judgment_metadata']['error'] ? run['judgment_metadata']['error'] : "Incorrect"));
              
              updateTD.append($("<button>").text("Correct").click((function(username, problem, judgmentID, divisionID) {
                return function() {
                  if (confirm("Update (" + username + ", " + problem + ") to correct?")) {
                    $.ajax({
                      data: $.stringifyJSON({'action' : 'submit_judgment', 'judgment_id' : judgmentID, 'judge_id' : 0, 'correct' : true, 'metadata' : '{}', 'contest_id' : <?= $g_curr_contest['contest_id'] ?>, 'division_id' : divisionID}),
                      success: function(ret) {
                        if (ret['success']) {
                          loadRuns();
                        }
                      }
                    });
                  }
                }
              })(run['username'], run['problem_alias'], run['judgment_id'], run['division_id'])));
            }
            tr.append($("<td>").text(formatTime(run['time_updated'] - contestStartTime)))
              .append($("<td>").text(formatTime(run['time_updated'] - run['time_submitted'])));
            updateTD.append($("<button>").text("Regrade").click((function(username, problem, judgmentID, divisionID) {
              return function() {
                if (confirm("Update (" + username + ", " + problem + ") to pending?")) {
                  $.ajax({
                    data: $.stringifyJSON({'action' : 'clear_judgment', 'judgment_id' : judgmentID, 'contest_id' : <?= $g_curr_contest['contest_id'] ?>, 'division_id' : divisionID}),
                    success: function(ret) {
                      if (ret['success']) {
                        loadRuns();
                      }
                    }
                  });
                }
              }
            })(run['username'], run['problem_alias'], run['judgment_id'], run['division_id'])));
            tr.append(updateTD);
            tbody.append(tr);
          });
          $("#runs > tbody").replaceWith(tbody);
          var now = new Date();
          $("#last_update").text(now.toString());
        }
      }
    });
  }

  $(document).ready(function() {
    $.ajaxSetup({
      url: "handle.php",
      type: "post",
      jsonp: false,
      processData: false,
      dataType: "json"
    });
    
    loadRuns();
    setInterval(loadRuns, 15000);
    $("#refresh").click(loadRuns);
  });
})(window.jQuery);
</script>
<style type="text/css">
#runs {
  width: 90%;
}
#runs td {
  text-align: center;
}
#num_runs {
  width: 30px;
}
</style>
</head>
<body>
<div align="center">
<h1>Hand Down Judgments!</h1>
<?php
print judgeLinkPanel();
?>
<hr>
<p>Last updated at:<br>
<i><div id="last_update"></div></i></p>
<p>
Display <input id="num_runs" type="text" value="50" /> runs
<button id="refresh">Refresh</button>
</p>
<table id="runs" border="1">
  <thead>
    <tr>
      <th>Time</th>
      <th>Team Alias</th>
      <th>Username</th>
      <th>Division</th>
      <th>Problem Alias</th>
      <th>Payload</th>
      <th>Judgment</th>
      <th>Updated</th>
      <th>Elapsed</th>
      <th>Update</th>
    </tr>
  </thead>
  <tbody>
  </tbody>
</table>
</div>
<?php footer(); ?>
</body>
</html>
<?php
// END RENDER
  }
  
  protected function renderPayloadTDBody() {
?>
    td.text("Payload");
<?php
  }
}
?>