<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class JudgeConfigProblem {
  
  public function render() {
    global $k_months;
    global $g_curr_contest;
// BEGIN RENDER
?>
<html>
<head>
<title>Configure problem</title>
<link href="/css/main.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="/js/jquery.stringify.min.js"></script>
<script type="text/javascript">

(function ($) {  
  $(document).ready(function() {
    $.ajaxSetup({
      url: "handle.php",
      type: "post",
      jsonp: false,
      processData: false,
      dataType: "json"
    });
    
    var problemTypes = [];
    var divisionMap = {};
    var problemOrder = [];
    var problemDivisionMap = {};
    
    function makeProblemTypeDropdown(problemID, divisionID, contestID, data, key, refresh) {
      var select = $("<select>");
      $.each(problemTypes, function(index, problemType) {
        select.append($("<option>").val(problemType).text(problemType));
      });
      return select.val(data[key]).change(saveFn(problemID, divisionID, contestID, key, refresh));
    }
    function makeInput(problemID, divisionID, contestID, data, key, refresh) {
      return $("<input type='text'>").val(data[key]).keydown(function() {
        $(this).addClass('updating');
      }).change(saveFn(problemID, divisionID, contestID, key, refresh));
    }
    function saveFn(problemID, divisionID, contestID, key, refresh) {
      return function() {
        var thisElem = $(this);
        thisElem.addClass('updating');
        $.ajax({
          data: $.stringifyJSON({'action' : 'modify_problem', 'problem_id' : problemID, 'division_id' : divisionID, 'contest_id' : contestID, 'key' : key, 'value' : $(this).val()}),
          success: function(ret) {
            if (ret['success']) {
              if (refresh) {
                $("#contest_id").change();
              }
              else {
                thisElem.removeClass('updating').addClass('updated');
                setTimeout(function() { thisElem.removeClass('updated'); }, 500);
              }
            }
          }
        });
      };
    }
    function makeCheck(problemID, divisionID, contestID, checked) {
      return $("<input type='checkbox'>").attr("checked", checked).change(function() {
        if ($(this).is(":checked")) {
          $.ajax({
            data: $.stringifyJSON({'action' : 'enable_problem', 'problem_id' : problemID, 'division_id' : divisionID, 'contest_id' : contestID}),
            success: function(ret) {
              if (ret['success']) {
                var problem = ret['problem'];
                problem['valid'] = true;
                loadRow($("#problem_" + problemID + "_" + divisionID), problemID, divisionID, contestID, problem);
              }
            }
          });
        }
        else {
          $.ajax({
            data: $.stringifyJSON({'action' : 'disable_problem', 'problem_id' : problemID, 'division_id' : divisionID, 'contest_id' : contestID}),
            success: function(ret) {
              if (ret['success']) {
                var remove = true;
                $.each(divisionMap, function(otherDivisionID, otherDivisionName) {
                  if ($("#problem_" + problemID + "_" + otherDivisionID).children("td:nth-child(3)").children("input").is(":checked")) {
                    remove = false;
                  }
                });
                if (remove) {
                  
                  $.each(divisionMap, function(otherDivisionID, otherDivisionName) {
                    $("#problem_" + problemID + "_" + otherDivisionID).remove();
                  });
                }
                else {
                  var problem = {'valid' : false};
                  loadRow($("#problem_" + problemID + "_" + divisionID), problemID, divisionID, contestID, problem);
                }
              }
            }
          });
        }
      });
    }
    function loadRow(row, problemID, divisionID, contestID, problem) {
      row.children("td").slice(3).remove();
      if (problem['valid']) {
        row.append($("<td>").append(makeInput(problemID, divisionID, contestID, problem, 'alias', false)))
           .append($("<td>").append(makeInput(problemID, divisionID, contestID, problem, 'title', true)))
           .append($("<td>").append(makeProblemTypeDropdown(problemID, divisionID, contestID, problem, 'problem_type', true)))
           .append($("<td>").append(makeInput(problemID, divisionID, contestID, problem, 'url', false)))
           .append($("<td>").load("problemmetadata.php", {'data' : $.stringifyJSON({'problem_id' : problemID, 'division_id' : divisionID, 'contest_id' : contestID, 'problem_type' : problem['problem_type']})}));
      }
      else {
        row.append($("<td>").attr("colspan", 5).text("Not used"));
      }
    }
    
    function reorderProblem(problemID, up) {
      return function() {
        $.ajax({
          data: $.stringifyJSON({'action' : 'reorder_problem', 'problem_id' : problemID, 'up' : up}),
          success: function() {
            $("#contest_id").change();
          }
        });
      }
    }
    
    function appendRows(problemID) {
      var isFirst = true;
      var contestID = $("#contest_id").val();
      $.each(divisionMap, function(divisionID, divisionName) {
        var problem = problemDivisionMap[problemID][divisionID];
        var row = $("<tr>").attr("id", "problem_" + problemID + "_" + divisionID);
        var firstTD = $("<td>");
        if (isFirst) {
          firstTD.append(makeInput(problemID, divisionID, contestID, problem, 'order_seq', true));
<?php
    if ($g_curr_contest) {
?>
          if (contestID == <?= $g_curr_contest['contest_id'] ?>) {
            firstTD.append("<br>")
                   .append($("<button>").text("Regrade").click((function(pID, title, cID) {
                             return function() {
                               if (confirm("Are you sure you want to regrade all submissions for " + title + "?")) {
                                 $.ajax({
                                   data: $.stringifyJSON({'action' : 'clear_judgments', 'contest_id' : cID, 'problem_id' : pID}),
                                   success: function(ret) {
                                     if (ret['success']) {
                                       alert("Successfully cleared all judgments for " + title);
                                     }
                                     else {
                                       alert("Regrade request failed!");
                                     }
                                   }
                                 });
                               }
                             };
                           })(problemID, problem['title'], contestID)));
          }
<?php
    }
?>      }
        row.append(firstTD)
           .append($("<td>").text(divisionMap[divisionID]))
           .append($("<td>").append(makeCheck(problemID, divisionID, contestID, problem['valid'])));
        isFirst = false;
        loadRow(row, problemID, divisionID, $("#contest_id").val(), problem);
        $("#problems > tbody").append(row);
      });
    }
    
    $("#contest_id").change(function() {
      $("#division_id option:selected").removeAttr("selected");
      $("#problems > tbody").empty();
      var contestID = $("#contest_id").val();
      $("#new_problem_division_id").empty();
      $.ajax({
        data: $.stringifyJSON({'action' : 'get_contest_problems', 'contest_id' : contestID}),
        success: function(ret) {
          if (ret['success']) {
            $("#new_problem_division_id").empty();
            divisionMap = {};
            problemOrder = [];
            problemDivisionMap = {};
            problemTypes = ret['problem_types'];
            $.each(ret['divisions'], function(index, division) {
              divisionMap[division['division_id']] = division['division_name'];
              $("#new_problem_division_id").append($("<option>").val(division['division_id']).text(division['division_name']));
            });
            $.each(ret['problems'], function(index, problem) {
              var problemID = problem['problem_id'];
              if ($.inArray(problemID, problemOrder) < 0) {
                problemOrder.push(problemID);
              }
            });
            $.each(problemOrder, function(index, problemID) {
              problemDivisionMap[problemID] = {};
              $.each(divisionMap, function(divisionID, divisionName) {
                problemDivisionMap[problemID][divisionID] = {'valid' : false};
              });
            });
            $.each(ret['problems'], function(index, problem) {
              problemDivisionMap[problem['problem_id']][problem['division_id']] = problem;
              problemDivisionMap[problem['problem_id']][problem['division_id']]['valid'] = true;
            });
            $.each(problemOrder, function(index, problemID) {
              appendRows(problemID);
            });
          }
        }
      });
    });
<?php if ($g_curr_contest) { ?>
    $("#contest_id").val(<?= $g_curr_contest['contest_id'] ?>).change();
<?php } else { ?>
    $("#contest_id").val($("#contest_id option:enabled").val()).change();
<?php } ?>
    $("#new_problem").click(function() {
      $.ajax({
        data: $.stringifyJSON({'action' : 'add_problem', 'contest_id' : $("#contest_id").val(), 'division_id' : $("#new_problem_division_id").val()}),
        success: function(ret) {
          if (ret['success']) {
            var problem = ret['problem'];
            var problemID = problem['problem_id'];
            problemOrder.push(problemID);
            problemDivisionMap[problemID] = {};
            $.each(divisionMap, function(divisionID, divisionName) {
              problemDivisionMap[problemID][divisionID] = {'valid' : false};
            });
            problemDivisionMap[problemID][problem['division_id']] = problem;
            problemDivisionMap[problemID][problem['division_id']]['valid'] = true;
            appendRows(problemID);
          }
        }
      });
      
    });
    
  });
})(window.jQuery);
</script>
<style>
#contest_id { width: 200px; }
.updating { background-color: #fcc; }
.updated { background-color: #cfc; }
</style>
</head>
<body>
<div align="center">
<h1>Judge Problem Configuration</h1>
<?php
print judgeLinkPanel();
?>
<hr>
<table>
  <tr>
    <td>
      Contests (select one): <br />
      <select id="contest_id" size="20">
<?php
foreach (DBManager::getContestTypes() as $contest_type) {
  printf('<option disabled="disabled">[%s]</option>', $contest_type);
  foreach (DBManager::getContestsOfType($contest_type) as $contest) {
    printf('<option value="%d">%s</option>', $contest['contest_id'], $contest['contest_name']);
  }
}
?>
      </select>
    </td>
    <td>
      <table id="problems">
        <thead>
          <tr>
            <td>Order</td>
            <td>Division</td>
            <td>Used</td>
            <td>Alias</td>
            <td>Title</td>
            <td>Type</td>
            <td>URL</td>
            <td>Metadata</td>
          </tr>
        </thead>
        <tbody>
          <tr>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="8">
              <button id="new_problem">Create new problem in </button>
              <select id="new_problem_division_id">
                <option></option>
              </select>
            </td>
          </tr>
        </tfoot>
      </table>
    </td>
  </tr>
</table>
</div>
<?php footer(); ?>
</body>
</html>
<?php
// END RENDER
  }
}
?>