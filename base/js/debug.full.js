(function($) {
  var timerOffset = 0;
  var timerOffsetCount = 0;
  var submissionsIntervalID = 0;
  var scoreboardIntervalID = 0;
  var clarificationsIntervalID = 0;
  var submissionStatusTimeoutID = 0;
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
            if (submission['judgment'] == judgmentCorrect) {
              tr.addClass('correct');
            }
            else if (submission['judgment'] == judgmentIncorrect) {
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
            if (team.hasOwnProperty('me')) {
              tr.addClass('me');
            }
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
      $("#scoreboard_table_div .div_title").text("Scoreboard (frozen)");
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
            var messageClass = "";
            switch (message['type']) {
              case postBroadcast:
                messageType = "Broadcast message";
                messageClass = "broadcast";
                break;
              case postReply:
                messageType = "Reply";
                messageClass = "reply";
                break;
              default:
                messageType = "Asked";
                messageClass = "asked";
                break;
            }
            container.append($("<div>").addClass(messageClass)
                                       .append($("<h5>").text(messageType + " at " + date))
                                       .append($("<span>").text(message['text'])));
          });
          $("#messages").empty().append(container);
        }
      }
    });
  }
  
  function showStatus(text) {
    if (submissionStatusTimeoutID != 0) {
      clearTimeout(submissionStatusTimeoutID);
      submissionStatusTimeoutID = 0;
    }
    $("#submissions_status").text(text).show();
    submissionStatusTimeoutID = setTimeout(function() {
      $("#submissions_status").text("").hide();
      submissionStatusTimeoutID = 0;
    }, 3000);
  }
  
  $(document).ready(function() {
    $.ajaxSetup({
      url: "handle.php",
      type: "post",
      jsonp: false,
      processData: false,
      dataType: "json"
    });
    
    $("#problems_listing").hide();
    loadTime();    
    
    $("#submissions_status").hide();
    
    $("#debug_submit").click(function() {
      if (!submitAllowed) {
        showStatus("Contest closed");
        return;
      }
    
      var type = $('#soln_type').val();
      var good = $.trim($('#soln_good').val());
      var bad = $.trim($('#soln_bad').val());
      if ((type == "correct" || type == "sometimes") && !good) {
        showStatus("Must give correct input");
        return;
      }
      if ((type == "wrong" || type == "sometimes") && !bad) {
        showStatus("Must give wrong input");
        return;
      }
      $("#submissions_status").text("Submitting solution...");
      $("#soln_good").val("");
      $("#soln_bad").val("");
      $("#debug_submit").attr("disabled", "disabled");
      $.ajax({
        data: $.stringifyJSON({
         'action': 'submit_debug_solution',
         'problem_id': $('#problem_id').val(),
         'type': type,
         'good': good,
         'bad': bad,
        }),
        success: function(ret) {
          if (ret['success']) {
            showStatus("Solution submitted");
            loadSubmissions();
          } else {
            showStatus("Solution submission failed. Please contact contest staff.");
          }
          $("#debug_submit").removeAttr("disabled");
        }
      });
    });
    
    $("#soln_type").change(function() {
      var type = $(this).val();
      if (type == "correct") {
        $("#soln_good_div").show();
        $("#soln_bad_div").hide();
        $("#soln_bad").val("");
      }
      else if (type == "wrong") {
        $("#soln_good_div").hide();
        $("#soln_good").val("");
        $("#soln_bad_div").show();
      }
      else {
        $("#soln_good_div").show();
        $("#soln_bad_div").show();
      }
    }).change();
    
    $("#ask_submit").click(function() {
      var text = $.trim($("#ask_message").val());
      if (text) {
        $.ajax({
          data: $.stringifyJSON({'action' : 'post_clarification', 'message' : text}),
          success: loadClarifications
        });
        $("#ask_message").val("");
      }
      return false;
    });
    
    submissionsIntervalID = setInterval(loadSubmissions, 300000);
    loadSubmissions();
    scoreboardIntervalID = setInterval(loadScoreboard, 30000);
    loadScoreboard();
    clarificationsIntervalID = setInterval(loadClarifications, 30000);
    loadClarifications();
  });
})(window.jQuery);