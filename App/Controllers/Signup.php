<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;

/**
 * Signup controller
 *
 * PHP version 7.0
 */
class Signup extends \Core\Controller
{

  //Show the signup page

  public function newAction()
  {
    View::renderTemplate('Signup/new.html');
  }


  public function createAction()
  {
    //We are aending data from form Signup by creating new object
    //User
    $user = new User($_POST);
    if ($user->save()) {
      //Send activation email
      $user->sendActivationEmail();
      //Load default categories for new user
      $user->loadCategories();
      //Call redirect method of /Core/Controller
      $this->redirect('/signup/success');
    } else {
      View::renderTemplate('Signup/new.html', ['user' => $user]);
    }
  }

  //Show signup success page
  public function successAction()
  {
    View::renderTemplate('Signup/success.html');
  }

  //Activate a new account
  public function activateAction()
  {
    User::activate($this->route_params['token']);
    $this->redirect('/signup/activated');
  }

  //Show the activate success page
  public function activatedAction()
  {
    View::renderTemplate('Signup/activated.html');
  }
}
