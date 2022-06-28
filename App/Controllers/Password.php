<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;
//Password controller
class Password extends \Core\Controller
{
  //Show the forgotten password page
  public function forgotAction()
  {
    View::renderTemplate('Password/forgot.html');
  }

  //Send the password reset link to the supplied email
  public function requestResetAction()
  {
    //We must to find user with such email so do this in user model
    User::sendPasswordReset($_POST['email']);
    static::redirect('/Password/checkEmail');
    //View::renderTemplate('Password/reset_requested.html');
  }

  //Show reset password CHECK EMAIL page
  public function checkEmailAction()
  {
    View::renderTemplate('Password/reset_requested.html');
  }
  //Show the reset password form
  public function resetAction()
  {
    $token = $this->route_params['token'];
    $user = $this->getUserOrExit($token);
    //We must pass the token in as vaiable to reset view 
    //becausee later to reset the passwrd we must find the user
    //here we found the user but resetPasswordAction is triggered by submision
    //so we put the token in hidden input
    View::renderTemplate('Password/reset.html', ['token' => $token]);
  }

  //Reset the password
  public function resetPasswordAction()
  {
    //we have this token thanks to passed it through arguments 
    //in renderTemplate here, and hidden input in reset.html   
    $token = $_POST['token'];
    $user = $this->getUserOrExit($token);
    if ($user->resetPassword($_POST['password'])) {
      View::renderTemplate('Password/reset_success.html');
    } else {
      View::renderTemplate('Password/reset.html', [
        'token' => $token,
        'user' => $user
      ]);
    }
  }

  //Find the user model associated with the password reset token,
  // or end the request with a message
  protected function getUserOrExit($token)
  {
    $user = User::findByPasswordReset($token);
    if ($user) {
      return $user;
    } else {
      View::renderTemplate('Password/token_expired.html');
      exit;
    }
  }
}
