<?php
require_once(__DIR__ . '/../../common.php');

class JudgeConfigContest {

  private $contest_type;
  private $contest_id;

  public function __construct($contest_type, $contest_id) {
    $this->contest_type = $contest_type;
    $this->contest_id = $contest_id;
  }
  
  public function renderMetadataLoadJS() { }
  public function renderMetadataSubmitJS() { }
  public function renderMetadataTR() {
  }
  
  public function render() {
    global $k_months;
// BEGIN RENDER
?>
<html>
<head>
<title>Configure contest</title>
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
    if ($("#contest_id").val() == 0) {
      $("#delete").attr("disabled", "disabled");
    }
    else {
      $.ajax({
        data: JSON.stringify({'action' : 'load_contest', 'contest_id' : $("#contest_id").val()}),
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
            var metadata = ret['metadata'];
<?php $this->renderMetadataLoadJS(); ?>
          }
        }
      });
    }
    $("#contest_id").change(function() {
      window.location = "contests.php?contest_type=" + $("#contest_type").val() + "&contest_id=" + $("#contest_id").val();
    });
    $("#contest_type").change(function() {
      window.location = "contests.php?contest_type=" + $("#contest_type").val();
    });
    $("#delete").click(function() {
      if (confirm("Are you sure you want to delete this contest?")) {
        $.ajax({
          data: JSON.stringify({'action' : 'delete_contest', 'contest_id' : $("#contest_id").val()}),
          success: function(ret) {
            if (ret['success']) {
              window.location = "contests.php?contest_type=" + $("#contest_type").val();
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
                  'metadata' : metadata,
                  'tag' : $("#contest_tag").val()
                 };
      $.ajax({
        data: JSON.stringify(data),
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
</div>
<hr>
<div align="center">
<p><b><big>Modifying Contests of Type 
<select id="contest_type">
<?php
foreach (glob(__DIR__ . '/../contest.*') as $contest_full_type) {
  $contest_type = substr($contest_full_type, strrpos($contest_full_type, 'contest.') + strlen('contest.'));
  print '<option value="' . $contest_type . '"' . ($contest_type == $this->contest_type ? ' selected="selected"' : '') . '>' . $contest_type . '</option>';
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
  print '<option value="' . $contest['contest_id'] . '"' . ($contest['contest_id'] == $this->contest_id ? ' selected="selected"' : '') . '>' . htmlentities($contest['contest_name']) . '</option>';
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

<?php
      print "      <select id=\"start_month\">\n";
      for ($i = 0; $i < 12; $i++) {
        print "        <option value=\"$i\">$k_months[$i]</option>\n";
      }
      print "      </select>\n";
      print "      <select id=\"start_date\">\n";
      for ($i = 1; $i <= 31; $i++) {
        printf("        <option value=\"%d\">%d</option>\n", $i, $i);
      }
      print "      </select>,\n";
      print "      <input type=\"text\" id=\"start_year\" size=\"4\" value=\"2012\"/><br />\n";
      print "      <select id=\"start_hour\">\n";
      for ($i = 0; $i <= 23; $i++) {    
        printf("        <option value=\"%d\">%02d</option>\n", $i, $i);
      }
      print "      </select> :\n";
      print "      <select id=\"start_minute\">\n";
      for ($i = 0; $i < 60; $i++) {
        printf("        <option value=\"%d\">%02d</option>\n", $i, $i);
      }
      print "      </select>\n";
?>
    </td>
  </tr>
  <tr>
    <td>Contest Length</td>
    <td>
      <input type="text" id="length_hour" size="2" value="2" /> Hours&nbsp;
      <input type="text" id="length_minute" size="2" value="0" /> Minutes
    </td>
  </tr>
<?php $this->renderMetadataTR(); ?>
  <tr>
    <td>Activity</td>
    <td>
      <button id="delete">Delete this contest (cannot be undone!)</button>
    </td>
  </tr>
  <tr>
    <td>Contest Tag</td>
    <td><input type="text" id="contest_tag" size="32" value="default" /><br />Used as a namespace for team usernames</td>
  </tr>
  <tr>
    <td>Teams Management</td>
    <td>
      <input type="button" id="download_teams" value="Download csv"></input>
      <input type="button" id="upload_teams" value="Upload csv"></input>
      <div id="upload_teams_div">
        <form action="uploadteams.php" method="post" enctype="multipart/form-data" target="_blank">
        Select a csv file <input type="file" name="upload_teams_file"></input> to <input type="submit" value="upload"></input>
        </form>
      </div>
    </td>
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