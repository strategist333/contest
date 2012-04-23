<?php
require_once(__DIR__ . '/../../common.php');

class SpeedTeamFrontend extends TeamFrontend {
  public function render() {
    parent::render();
    echo <<<HEREDOC
Speed/SpeedTeamFrontend.
HEREDOC;
  }
}
?>