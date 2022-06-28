<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;
use \App\Auth;
use \App\Flash;
use \App\Token;
use PDO;
//Login controller
class Login extends \Core\Controller
{
  //Show the logn page
  public function newAction()
  {
    View::renderTemplate('Login/new.html');
  }
  // Log in a user
  public function createAction()
  {
    //if not found return false, else return object of User class
    $user = User::authentication($_POST['email'], $_POST['password']);

    $remember_me = isset($_POST['remember_me']);

    if ($user) {

      Auth::login($user, $remember_me);
      //Remember the login could be here but to keep all the authentication code 
      //together and to keep code cleaner we ut this in Auth class

      Flash::addMessage('Login successful.');
      //Call redirect method of /Core/Controller
      $this->redirect(Auth::getReturnToPage());
    } else {
      Flash::addMessage('Login unsuccessful, please try again.', Flash::WARNING);
      //additional parameter to show email in input
      View::renderTemplate('Login/new.html', [
        'email' => $_POST['email'],
        'remember_me' => $remember_me
      ]);
    }
  }
  //Log out a user
  public function destroyAction()
  {
    Auth::logout();
    $this->redirect('/login/show-logout-message');
  }
  /* 
  Show a "logged out" flash message and redirect to the homepage. Necessary to use the flash messages
  as they use the session and at the end of the logout method (destroyAction) the session is destroyed
  so a new action needs to be called in order to use the session.
  */
  public function showLogoutMessageAction()
  {
    Flash::addMessage('Logout successful.');
    $this->redirect('/');
  }
}
