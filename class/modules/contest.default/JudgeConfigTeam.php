<?php
require_once(__DIR__ . '/../../common.php');

class JudgeConfigTeam {
  
  public function render() {
    global $k_months;
// BEGIN RENDER
?>
<html>
<head>
<title>Configure teams</title>
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
    $("#contest_id").change(function() {
      $("#upload_form").attr('action', 'uploadteams.php?contest_id=' + $("#contest_id").val());
      $("#teams tbody").empty();
      $("#division_id option:selected").removeAttr("selected");
      $.ajax({
        data: JSON.stringify({'action' : 'get_contest_divisions', 'contest_id' : $("#contest_id").val()}),
        success: function(ret) {
          if (ret['success']) {
            $("#division_id option:selected").removeAttr("selected");
            $.each(ret['division_ids'], function(index, val) {
              $("#division_id option[value=" + val + "]").attr("selected", "selected");
            });
          }
        }
      });
      $.ajax({
        data: JSON.stringify({'action' : 'get_contest_teams', 'contest_id' : $("#contest_id").val()}),
        success: function(ret) {
          if (ret['success']) {
            $("#teams tbody").empty();
            $.each(ret['teams'], function(index, team) {
              $("#teams tbody").append($("<tr>").append($("<td>").text(team['team_id']))
                                                .append($("<td>").text(team['tag']))
                                                .append($("<td>").text(team['username']))
                                                .append($("<td>").text(team['password']))
                                                .append($("<td>").text(team['alias']))
                                                .append($("<td>").text(team['division_name'])));
            });
          }
        }
      });
    });
    $("#contest_id").val($("#contest_id option:enabled").val()).change();
    $("#download").click(function() {
      window.location.assign("downloadteams.php?contest_id=" + $("#contest_id").val());
    });
    $("#upload").click(function() {
      setTimeout(function () { window.location = "teams.php"; }, 2000);
      return true;
    });
  });
})(window.jQuery);
</script>
<style>
select { width: 200px; }
</style>
</head>
<body>
<div align="center">
  <h1>Judge Team Configuration</h1>
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
    printf('<option value="%d">%s [tag: %s]</option>', $contest['contest_id'], $contest['contest_name'], $contest['tag']);
  }
}
?>
      </select>
    </td>
    <td>
      Linked divisions: <br />
      <select id="division_id" size="20" multiple="multiple" disabled="disabled">
<?php
foreach (DBManager::getDivisions() as $division) {
  printf('<option value="%d">%s</option>', $division['division_id'], $division['name']);
}
?>
      </select>
    </td>
    <td>
      Teams: <br />
      <table id="teams">
        <thead>
          <tr>
            <td>Team ID</td>
            <td>Tag</td>
            <td>Username</td>
            <td>Password</td>
            <td>Alias</td>
            <td>Division</td>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center">
      Actions: <br />
      <button id="download">Download teams csv</button><br />
      <form id="upload_form" action="uploadteams.php" method="post" enctype="multipart/form-data" target="_blank">
        Select a teams csv file <input type="file" name="upload_teams_file"></input> to <input id="upload" type="submit" value="upload"></input>
      </form>
    </td>
  </tr>
    </td>
  </tr>
</table>
</div>
<br />
<?php footer(); ?>
</body>
</html>
<?php
// END RENDER
  }
}
?>