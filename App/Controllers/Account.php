<?php

namespace App\Controllers;

use \App\Models\User;
//Account controller to verify email address in JavaScript with Ajax
class Account extends \Core\Controller
{
  //Valiadte if email is available (AJAXS) for a new sign up
  public function validateEmailAction()
  {
    //If email is not taken emailExist return false so is_valid = true
    //emailExists is a static method to allow us call it here without creating a new object of User class
    //ignore email validation of logged user with ID = ignore_id  during editing data
    //or null if this variable is not set
    $is_valid = !User::emailExists($_GET['email'], $_GET['ignore_id'] ?? null);
    header('Content-Type: application/json');
    echo json_encode($is_valid);
  }
}
