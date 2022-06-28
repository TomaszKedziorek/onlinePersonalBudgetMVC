<?php

namespace App\Controllers;

//Authenticated base contoller
abstract class Authenticated extends \Core\Controller
{
  // Before filter - called before an action method.
  protected function before()
  {
    $this->requireLogin();
  }
}
