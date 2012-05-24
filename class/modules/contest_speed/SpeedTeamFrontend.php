<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'common.php');

class SpeedTeamFrontend extends TeamFrontend {
  
  protected function renderScripts() {
    parent::renderScripts();
    global $g_curr_contest;
    global $k_judgment_incorrect;
    global $k_judgment_correct;
    global $k_post_reply;
    global $k_post_broadcast;
    $metadata = json_decode($g_curr_contest['metadata'], true);
    
// BEGIN RENDER SCRIPTS
?>
<script type="text/javascript" src="/js/jquery.ocupload.min.js"></script>
<script type="text/javascript">
var contestStartTime = <?= $g_curr_contest['time_start'] ?>;
var contestEndTime = <?= $g_curr_contest['time_start'] + $g_curr_contest['time_length'] ?>;
var scoreboardFreezeTime = <?= $g_curr_contest['time_start'] + $metadata['time_freeze'] ?>;
var judgmentCorrect = <?= $k_judgment_correct ?>;
var judgmentIncorrect = <?= $k_judgment_incorrect ?>;
var postBroadcast = <?= $k_post_broadcast ?>;
var postReply = <?= $k_post_reply ?>;
</script>
<script type="text/javascript" src="/js/speed.full.js"></script>
<?php
// END RENDER SCRIPTS
  }
  
}
?>