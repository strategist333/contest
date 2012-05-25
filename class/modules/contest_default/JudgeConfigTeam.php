<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class JudgeConfigTeam {
  
  public function render() {
    global $k_months;
    global $g_curr_contest;
// BEGIN RENDER
?>
<html>
<head>
<title>Configure teams</title>
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
    $("#contest_id").change(function() {
      $("#upload_contest_id").val($("#contest_id").val());
      $("#teams tbody").empty();
      $("#divisions input:checked").removeAttr("checked");
      $.ajax({
        data: $.stringifyJSON({'action' : 'get_contest_divisions', 'contest_id' : $("#contest_id").val()}),
        success: function(ret) {
          if (ret['success']) {
            $("#divisions input:checked").removeAttr("checked");
            $.each(ret['division_ids'], function(index, val) {
              $("#division_" + val).attr("checked", "checked");
            });
          }
        }
      });
      $.ajax({
        data: $.stringifyJSON({'action' : 'get_contest_teams', 'contest_id' : $("#contest_id").val()}),
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
<?php if ($g_curr_contest) { ?>
    $("#contest_id").val(<?= $g_curr_contest['contest_id'] ?>).change();
<?php } else { ?>
    $("#contest_id").val($("#contest_id option:enabled").val()).change();
<?php } ?>
    $("#download").click(function() {
      window.location.href = "handlefile.php?action=download_teams&contest_id=" + $("#contest_id").val();
    });
  });
})(window.jQuery);
</script>
<style>
select { width: 200px; }
iframe { border: 0px; width: 400px; height: 100px; }
</style>
</head>
<body>
<div align="center">
<h1>Judge Team Configuration</h1>
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
    printf('<option value="%d">%s [tag: %s]</option>', $contest['contest_id'], $contest['contest_name'], $contest['tag']);
  }
}
?>
      </select>
    </td>
    <td>
      Linked divisions: <br />
      <div id="divisions">
<?php
foreach (DBManager::getDivisions() as $division) {
  printf('<input id="division_%d" type="checkbox" disabled="disabled">%s<br />', $division['division_id'], $division['division_name']);
}
?>
      </div>
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
    <td colspan="3" align="center">
      Actions: <br />
      <button id="download">Download teams csv</button><br />
      <form action="handlefile.php" method="post" enctype="multipart/form-data" target="upload_frame">
        Select a teams csv file <input type="file" name="upload_file"></input> to <input type="submit" value="upload"></input>
        <input id="upload_contest_id" type="hidden" name="contest_id" value="0"></input>
        <input type="hidden" name="action" value="upload_teams"></input>
      </form>
      <iframe name="upload_frame"></iframe>
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