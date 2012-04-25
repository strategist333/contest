<?php
class X {
  protected $a;
  public function __construct($x) {
    $this->a = $x;
  }
}
class Y extends X {
  public function b() {
    print $this->a;
  }
}
$y = new Y(5);
$y->b();
?>