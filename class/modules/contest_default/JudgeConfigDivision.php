<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class JudgeConfigDivision {
  
  public function render() {
    global $k_months;
    global $g_curr_contest;
// BEGIN RENDER
?>
<html>
<head>
<title>Configure division</title>
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
      $("#division_id option:selected").removeAttr("selected");
      $.ajax({
        data: $.stringifyJSON({'action' : 'get_contest_divisions', 'contest_id' : $("#contest_id").val()}),
        success: function(ret) {
          if (ret['success']) {
            $.each(ret['division_ids'], function(index, val) {
              $("#division_id option[value=" + val + "]").attr("selected", "selected");
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
    $("#new_division").click(function() {
      var name = prompt("New division name:");
      if (name) {
        $.ajax({
          data: $.stringifyJSON({'action' : 'add_division', 'division_name' : name}),
          success: function(ret) {
            if (ret['success']) {
              $("#division_id").append($("<option>").val(ret['division_id']).text(name));
            }
          }
        });
      }
    });
    $("#rename_division").click(function() {
      var options = $("#division_id option:selected");
      if (options.length == 1) {
        var divisionID = options.eq(0).val();
        var name = prompt("New division name:");
        if (name) {
          $.ajax({
            data: $.stringifyJSON({'action' : 'rename_division', 'division_id' : divisionID, 'division_name' : name}),
            success: function(ret) {
              if (ret['success']) {
                options.eq(0).text(name);
              }
            }
          });
        }
      }
      else {
        alert("Select exactly one division to rename!");
      }
    });
    $("#link_divisions").click(function() {
      var divisionIDs = [];
      $("#division_id option:selected").each(function() {
        divisionIDs.push(parseInt($(this).val()));
      });
        
      $.ajax({
        data: $.stringifyJSON({'action' : 'link_divisions', 'contest_id' : $("#contest_id").val(), 'division_ids' : divisionIDs}),
        success: function(ret) {
          if (!ret['success']) {
            alert("Divisions not linked");
          }
        },
        error: function() {
          alert("Divisions not linked");
        }
      });
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
<h1>Judge Division Configuration</h1>
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
      Divisions (select many): <br />
      <select id="division_id" size="20" multiple="multiple">
<?php
foreach (DBManager::getDivisions() as $division) {
  printf('<option value="%d">%s</option>', $division['division_id'], $division['division_name']);
}
?>
      </select>
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center">
      Actions: <br />
      <button id="new_division">Add new division</button><br />
      <button id="rename_division">Rename division</button><br />
      <button id="link_divisions">Set contest &lt;-&gt; division linkages</button>
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