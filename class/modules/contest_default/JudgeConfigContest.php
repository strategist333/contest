<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class JudgeConfigContest {

  private $contest_type;
  private $contest_id;

  public function __construct($contest_type, $contest_id) {
    $this->contest_type = $contest_type;
    $this->contest_id = $contest_id;
  }
  
  public function renderMetadataLoadDefaultJS() { }
  public function renderMetadataLoadJS() { }
  public function renderMetadataSubmitJS() { }
  public function renderMetadataTR() {
  }
  
  public function render() {
    global $k_months;
    global $g_curr_contest;
// BEGIN RENDER
?>
<html>
<head>
<title>Configure contest</title>
<link href="/css/main.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="/js/jquery.stringify.min.js"></script>
<script type="text/javascript">
(function ($) {

  function reloadContest() {
    if ($("#contest_id").val() == 0) {
      $("#contest_name").val("");
      $("#start_year").val(2012);
      $("#start_month").val(0);
      $("#start_date").val(1);
      $("#start_hour").val(0);
      $("#start_minute").val(0);
      $("#length_hour").val(2);
      $("#length_minute").val(0);
      $("#contest_tag").val("default");
      $("#current").hide();
      $("#clone").attr("disabled", "disabled");
      $("#delete").attr("disabled", "disabled");
<?php $this->renderMetadataLoadDefaultJS(); ?>
    }
    else {
      $.ajax({
        data: $.stringifyJSON({'action' : 'load_contest', 'contest_id' : $("#contest_id").val()}),
        success: function(ret) {
          if (ret['success']) {
            $("#contest_name").val(ret['contest_name']);
            var startTime = new Date(ret['time_start'] * 1000);
            $("#start_year").val(startTime.getFullYear());
            $("#start_month").val(startTime.getMonth());
            $("#start_date").val(startTime.getDate());
            $("#start_hour").val(startTime.getHours());
            $("#start_minute").val(startTime.getMinutes());
            $("#length_hour").val(Math.floor(ret['time_length'] / 3600));
            $("#length_minute").val(Math.floor(ret['time_length'] % 3600 / 60));
            $("#contest_tag").val(ret['tag']);
<?php if ($g_curr_contest) { ?>            
            if (ret['contest_id'] == <?php print $g_curr_contest['contest_id']; ?>) {
              $("#current").text("Deactivate this contest");
            }
            else {
              $("#current").text("Switch active contest from <?php print $g_curr_contest['contest_name'] ?> to this one");
            }
<?php } else { ?>
            $("#current").text("Set active contest to this one");
<?php } ?>
            $("#current").show();
            $("#clone").removeAttr("disabled");
            $("#delete").removeAttr("disabled");
            var metadata = ret['metadata'];
<?php $this->renderMetadataLoadJS(); ?>
          }
        }
      });
    }
  }

  $(document).ready(function() {
    $.ajaxSetup({
      url: "handle.php",
      type: "post",
      jsonp: false,
      processData: false,
      dataType: "json"
    });
    reloadContest();
    $("#contest_id").change(reloadContest);
    $("#contest_type").change(function() {
      window.location = "contests.php?contest_type=" + $("#contest_type").val();
    });
    $("#current").click(function() {
      $.ajax({
        data: $.stringifyJSON({'action' : 'toggle_current_contest', 'contest_id' : $("#contest_id").val()}),
        success: function(ret) {
          if (ret['success']) {
            window.location = "contests.php?contest_type=" + $("#contest_type").val() + "&contest_id=" + $("#contest_id").val();
          }
        }
      });
    });
    $("#delete").click(function() {
      if (confirm("Are you sure you want to delete this contest?")) {
        $.ajax({
          data: $.stringifyJSON({'action' : 'delete_contest', 'contest_id' : $("#contest_id").val()}),
          success: function(ret) {
            if (ret['success']) {
              window.location = "contests.php?contest_type=" + $("#contest_type").val();
            }
          }
        });
      }
    });
    $("#clone").click(function() {
      var name = prompt("Give a name to the cloned contest:", $("#contest_name").val() + " copy");
      if (name) {
        $.ajax({
          data: $.stringifyJSON({'action' : 'clone_contest', 'contest_id' : $("#contest_id").val(), 'contest_name' : name}),
          success: function(ret) {
            if (ret['success']) {
              window.location = "contests.php?contest_type=" + $("#contest_type").val() + "&contest_id=" + ret['contest_id'];
            }
          }
        });
      }
    });
    $("#submit").click(function() {
      var metadata = {};
<?php $this->renderMetadataSubmitJS(); ?>
      var data = {'action' : 'save_contest',
                  'contest_id' : $("#contest_id").val(),
                  'contest_name' : $("#contest_name").val(),
                  'contest_type' : $("#contest_type").val(),
                  'time_start' : Math.floor(new Date(parseInt($("#start_year").val()), parseInt($("#start_month").val()), parseInt($("#start_date").val()), parseInt($("#start_hour").val()), parseInt($("#start_minute").val()), 0, 0).getTime() / 1000),
                  'time_length' : parseInt($("#length_hour").val()) * 3600 + parseInt($("#length_minute").val()) * 60,
                  'metadata' : $.stringifyJSON(metadata),
                  'tag' : $("#contest_tag").val()
                 };
      $.ajax({
        data: $.stringifyJSON(data),
        success: function(ret) {
          if (ret['success']) {
            window.location = "contests.php?contest_type=" + $("#contest_type").val() + "&contest_id=" + ret['contest_id'];
          }
        }
      });
    });
  });
})(window.jQuery);
</script>
</head>
<body>
<div align="center">
<h1>Judge Contest Configuration</h1>
<?php
print judgeLinkPanel();
?>
<hr>
<p><b><big>Modifying Contests of Type 
<select id="contest_type">
<?php
foreach (DBManager::getContestTypes() as $contest_type) {
  printf('<option value="%s"%s>%s</option>', $contest_type, ($contest_type == $this->contest_type ? ' selected="selected"' : ''), $contest_type);
}
?>
</select>
</big></b></p>
<select id="contest_id">
<?php
$contest_selected = false;
foreach (DBManager::getContestsOfType($this->contest_type) as $contest) {
  if ($contest['contest_id'] == $this->contest_id) {
    $contest_selected = true;
  }
  printf('<option value="%d"%s>%s</option>', $contest['contest_id'], ($contest['contest_id'] == $this->contest_id ? ' selected="selected"' : ''), htmlentities($contest['contest_name'] . ($g_curr_contest && $g_curr_contest['contest_id'] == $contest['contest_id'] ? ' (current)' : '')));
}
print '<option value="0"' . ($contest_selected ? '' : ' selected="selected"') . '>[New Contest]</option>';
?>
</select>

<p><b><big>Contest Base Info</big></b></p>
<table cellpadding="2">
  <tr>
    <td>Contest Name</td>
    <td><input type="text" id="contest_name" size="30" value="" /></td>
  </tr>
  <tr>
    <td>Contest Start</td>
    <td>
      <select id="start_month">
      <?php for ($i = 0; $i < 12; $i++) { printf('<option value=%d>%s</option>', $i, $k_months[$i]); } ?>
      </select>
      <select id="start_date">
      <?php for ($i = 1; $i <= 31; $i++) { printf('<option value="%d">%d</option>', $i, $i); } ?>
      </select>
      <input type="text" id="start_year" size="4" value="2012"/><br />
      <select id="start_hour">
      <?php for ($i = 0; $i <= 23; $i++) { printf('<option value="%d">%02d</option>', $i, $i); } ?>
      </select> :
      <select id="start_minute">
      <?php for ($i = 0; $i < 60; $i++) { printf('<option value="%d">%02d</option>', $i, $i); } ?>
      </select>
    </td>
  </tr>
  <tr>
    <td>Contest Length</td>
    <td>
      <input type="text" id="length_hour" size="2" value="2" /> hours&nbsp;
      <input type="text" id="length_minute" size="2" value="0" /> minutes
    </td>
  </tr>
<?php $this->renderMetadataTR(); ?>
  <tr>
    <td>Management</td>
    <td>
      <button id="current">Set active contest to this one</button><br />
      <button id="clone">Clone this contest</button><br />
      <button id="delete">Delete this contest (cannot be undone!)</button>
    </td>
  </tr>
  <tr>
    <td>Contest Tag</td>
    <td><input type="text" id="contest_tag" size="32" value="default" /> (Used as a namespace for team usernames)</td>
  </tr>
</table>
<button id="submit">Save</button>
<br />
<?php footer(); ?>
</body>
</html>
<?php
// END RENDER
  }
}
?>