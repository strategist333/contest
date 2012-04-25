<?php
require_once(__DIR__ . '/../../common.php');

class JudgeConfigProblem {
  
  public function render() {
    global $k_months;
// BEGIN RENDER
?>
<html>
<head>
<title>Configure problem</title>
<link href="/css/main.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript">

(function ($) {
  function makeInput(problemID, divisionID, contestID, data, key) {
    return $("<input type='text'>").val(data[key]).keydown(function() {
      $(this).addClass('updating');
    }).change(saveFn(problemID, divisionID, contestID, key));
  }
  function saveFn(problemID, divisionID, contestID, key) {
    return function() {
      var thisElem = $(this);
      thisElem.addClass('updating');
      $.ajax({
        data: JSON.stringify({'action' : 'modify_problem', 'problem_id' : problemID, 'division_id' : divisionID, 'contest_id' : contestID, 'key' : key, 'value' : $(this).val()}),
        success: function(ret) {
          if (ret['success']) {
            thisElem.removeClass('updating').addClass('updated');
            setTimeout(function() { thisElem.removeClass('updated'); }, 500);
          }
        }
      });
    };
  }
  $(document).ready(function() {
    $.ajaxSetup({
      url: "handle.php",
      type: "post",
      processData: false,
      dataType: "json"
    });    
    $("#contest_id").change(function() {
      $("#division_id option:selected").removeAttr("selected");
      $("#problems tbody").empty();
      var contestID = $("#contest_id").val();
      $.ajax({
        data: JSON.stringify({'action' : 'get_contest_problems', 'contest_id' : contestID}),
        success: function(ret) {
          if (ret['success']) {
            var divisionMap = {};
            $.each(ret['divisions'], function(index, division) {
              divisionMap[division['division_id']] = division['division_name'];
            });
            var problemOrder = [];
            var problemDivisionMap = {};
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
            $.each(problemDivisionMap, function(problemID, divisionProblem) {
              $.each(divisionProblem, function(divisionID, problem) {
                if (problem['valid']) {
                  $("#problems tbody").append($("<tr>").append($("<td>").text(problemID))
                                                       .append($("<td>").text(divisionMap[divisionID]))
                                                       .append($("<td>").append(makeInput(problemID, divisionID, contestID, problem, 'display_alias')))
                                                       .append($("<td>").append(makeInput(problemID, divisionID, contestID, problem, 'title')))
                                                       .append($("<td>").append(makeInput(problemID, divisionID, contestID, problem, 'problem_type')))
                                                       .append($("<td>").append(makeInput(problemID, divisionID, contestID, problem, 'url')))
                                                       .append($("<td>").append(makeInput(problemID, divisionID, contestID, problem, 'alias')))
                                                       .append($("<td>").load("problemmetadata.php", {'data' : JSON.stringify({'problem_id' : problemID, 'division_id' : divisionID, 'contest_id' : contestID, 'metadata' : problem['metadata'], 'problem_type' : problem['problem_type']})})));
                }
                else {
                  $("#problems tbody").append($("<tr>").append($("<td>").text(problemID))
                                                       .append($("<td>").text(divisionMap[divisionID]))
                                                       .append($("<td>").attr("colspan", 6).text("Not used")));
                }
              });
            });
          }
        }
      });
    });
    $("#contest_id").val($("#contest_id option:enabled").val()).change();
    
  });
})(window.jQuery);
</script>
<style>
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