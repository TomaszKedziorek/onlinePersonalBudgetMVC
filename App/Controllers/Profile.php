<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;
//Profile controller
//By extending Authnticated makes that the user must be logged before
//showing this page 
class Profile extends Authenticated
{
  //Before filter - called before each action method
  //we must call here in each action method Auth::getUser();
  protected function before()
  {
    parent::before();
    $this->user = Auth::getUser();
  }

  //Show the profile
  public function showAction()
  {
    //pass user data to view template
    View::renderTemplate('Profile/show.html', [
      'user' => $this->user
    ]);
  }

  //Show the form for editing the profile
  public function editAction()
  {
    View::renderTemplate('Profile/edit.html', [
      'user' => $this->user
    ]);
  }

  //Update the profile
  public function updateAction()
  {
    if ($this->user->updateProfile($_POST)) {
      Flash::addMessage('Changes saved');
      $this->redirect('/profile/show');
    } else {
      View::renderTemplate('Profile/edit.html', [
        'user' => $this->user
      ]);
    }
  }
}
