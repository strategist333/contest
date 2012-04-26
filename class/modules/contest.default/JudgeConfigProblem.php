<?php
require_once(__DIR__ . '/../../common.php');

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
<script type="text/javascript">

(function ($) {  
  $(document).ready(function() {
    $.ajaxSetup({
      url: "handle.php",
      type: "post",
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
          data: JSON.stringify({'action' : 'modify_problem', 'problem_id' : problemID, 'division_id' : divisionID, 'contest_id' : contestID, 'key' : key, 'value' : $(this).val()}),
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
            data: JSON.stringify({'action' : 'enable_problem', 'problem_id' : problemID, 'division_id' : divisionID, 'contest_id' : contestID}),
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
            data: JSON.stringify({'action' : 'disable_problem', 'problem_id' : problemID, 'division_id' : divisionID, 'contest_id' : contestID}),
            success: function(ret) {
              if (ret['success']) {
                var refresh = true;
                $.each(divisionMap, function(otherDivisionID, otherDivisionName) {
                  if ($("#problem_" + problemID + "_" + otherDivisionID).children("td:nth-child(3)").children("input").is(":checked")) {
                    refresh = false;
                  }
                });
                if (refresh) {
                  $("#contest_id").change();
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
           .append($("<td>").append(makeInput(problemID, divisionID, contestID, problem, 'display_alias', false)))
           .append($("<td>").load("problemmetadata.php", {'data' : JSON.stringify({'problem_id' : problemID, 'division_id' : divisionID, 'contest_id' : contestID, 'metadata' : problem['metadata'], 'division_metadata' : problem['division_metadata'], 'problem_type' : problem['problem_type']})}));
      }
      else {
        row.append($("<td>").attr("colspan", 6).text("Not used"));
      }
    }
    function appendRows(problemID) {
      $.each(divisionMap, function(divisionID, divisionName) {
        var problem = problemDivisionMap[problemID][divisionID];
        var row = $("<tr>").attr("id", "problem_" + problemID + "_" + divisionID);
        row.append($("<td>").text(problemID))
           .append($("<td>").text(divisionMap[divisionID]))
           .append($("<td>").append(makeCheck(problemID, divisionID, $("#contest_id").val(), problem['valid'])));
        loadRow(row, problemID, divisionID, $("#contest_id").val(), problem);
        $("#problems tbody").append(row);
      });
    }
    
    $("#contest_id").change(function() {
      $("#division_id option:selected").removeAttr("selected");
      $("#problems tbody").empty();
      var contestID = $("#contest_id").val();
      $("#new_problem_division_id").empty();
      $.ajax({
        data: JSON.stringify({'action' : 'get_contest_problems', 'contest_id' : contestID}),
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
        data: JSON.stringify({'action' : 'add_problem', 'contest_id' : $("#contest_id").val(), 'division_id' : $("#new_problem_division_id").val()}),
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
</div>
<hr>
<div align="center">
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
            <td>Problem ID</td>
            <td>Division</td>
            <td>Used</td>
            <td>Filename</td>
            <td>Title</td>
            <td>Type</td>
            <td>URL</td>
            <td>Alias</td>
            <td>Metadata</td>
          </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="8">
              <button id="new_problem">Create new problem in </button>
              <select id="new_problem_division_id">
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