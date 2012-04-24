<?php
require_once(__DIR__ . '/../../common.php');

class JudgeConfigTeam {

  private $contest_type;
  private $contest_id;

  public function __construct($contest_type, $contest_id) {
    $this->contest_type = $contest_type;
    $this->contest_id = $contest_id;
  }
  
  public function render() {
    global $k_months;
// BEGIN RENDER
?>
<html>
<head>
<title>Configure division</title>
<link href="/css/main.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript">
(function ($) {
  $(document).ready(function() {
    
  });
})(window.jQuery);
</script>
</head>
<body>
<div align="center">
  <h1>Judge Team Configuration</h1>
</div>
<hr>
<br />
<?php footer(); ?>
</body>
</html>
<?php
// END RENDER
  }
}
?>