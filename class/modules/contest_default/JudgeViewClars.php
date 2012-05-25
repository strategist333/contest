<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class JudgeViewClars {
  
  public function render() {
    global $g_curr_contest;
    global $k_post_unread;
    global $k_post_read;
    global $k_post_reply;
    global $k_post_broadcast;
// BEGIN RENDER
?>
<html>
<head>
<title>Clarifications</title>
<link href="/css/main.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="/js/jquery.stringify.min.js"></script>
<script type="text/javascript">
(function ($) {

  function loadClars() {
    var statuses;
    if ($("input:radio[name=mode]:checked").val() == 0) {
      statuses = [<?= $k_post_unread ?>];
    }
    else {
      statuses = [<?= $k_post_unread ?>, <?= $k_post_read ?>, <?= $k_post_reply ?>, <?= $k_post_broadcast ?>];
    }
    $.ajax({
      data: $.stringifyJSON({'action' : 'get_posts', 'contest_id' : <?= $g_curr_contest['contest_id'] ?>, 'statuses' : statuses}),
      success: function(ret) {
        if (ret['success']) {
          var tbody = $("<tbody>");
          $.each(ret['posts'], function(index, post) {
            var tr = $("<tr>").attr('id', 'post_' + post['post_id']);
            if (post['status'] == <?= $k_post_unread ?>) {
              tr.css('background-color', '#fcc');
            }
            else if (post['status'] == <?= $k_post_broadcast ?>) {
              tr.css('background-color', '#edf3fe');
            }
            else if (post['status'] == <?= $k_post_reply ?>) {
              tr.css('background-color', '#ccc');
            }
            else if (post['status'] == <?= $k_post_read ?>) {
              tr.css('background-color', '#cfc');
            }
            tr.append($("<td>").append($("<a href=''>").text(post['post_id'])))
              .append($("<td>").text(new Date(post['time_posted'] * 1000).toTimeString().replace(/GMT.*/,'')));
            if (post['team_id'] && post['username']) {
              tr.append($("<td>").text(post['team_id']))
                .append($("<td>").text(post['username']));
            }
            else {
              tr.append($("<td>")).append($("<td>"));
            }
            tr.append($("<td>").text(post['text']));
            tbody.append(tr);
          });
          $("#clars > tbody").replaceWith(tbody);
          
          $("#clars a").click(function() {
            $("#reply_id").val($(this).text());
            $("#response").val("");
            $("#response").focus();
            return false;
          });
          
          var now = new Date();
          $("#last_update").text(now.toString());
        }
      }
    });
  }

  function saveReply(replyID, text) {
    var params = {};
    
    if (text.length == 0) {
      if (replyID == 0) {
        return;
      }
      params['action'] = 'read_post';
      params['post_id'] = replyID;
    }
    else {
      params['contest_id'] = <?= $g_curr_contest['contest_id'] ?>;
      params['message'] = text;
      if (replyID == 0) {
        params['action'] = 'broadcast_post';
      }
      else {
        params['action'] = 'reply_post';
        params['ref_id'] = replyID;
        params['team_id'] = $("#post_" + replyID).children("td").eq(2).text();
      }
    }
    $.ajax({
      data: $.stringifyJSON(params),
      success: function() {
        $("#reply_id").val('');
        $("#response").val('');
        loadClars();
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
    
    loadClars();
    setInterval(loadClars, 10000);
    $("input[name=mode]").click(function() {
      loadClars();
    });
    $("#btn_post").click(function() {
      saveReply($("#reply_id").val(), $.trim($("#response").val()));
    });
  });
})(window.jQuery);
</script>
</head>
<body>
<div align="center">
<h1>Judge Clarifications</h1>
<?php
print judgeLinkPanel();
?>
<hr>
Reply to post ID (0 for broadcast): <br />
<input type="text" id="reply_id" style="width: 100px;"></input> <br />
Message (blank to simply mark as replied): <br />
<textarea id="response" style="width: 90%; margin: auto;" rows="5"></textarea>
<br />
<input type="button" id="btn_post" value="Post message" /><br />

<p>Last updated at:<br>
<i><div id="last_update"></div></i></p>

<input type="radio" name="mode" value="0" checked>Unreplied only</input>
<input type="radio" name="mode" value="1">All messages</input>

<table id="clars" border="1" width="1000" cellspacing="0">
<thead>
  <tr bgcolor="#eee">
    <th>Post ID</th>
    <th>Time</th>
    <th>Team ID</th>
    <th>Username</th>
    <th width="500">Post</th>
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
}
?>