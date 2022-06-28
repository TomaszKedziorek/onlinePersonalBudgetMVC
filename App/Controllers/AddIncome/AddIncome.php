<?php

namespace App\Controllers\AddIncome;

use \App\Controllers\Authenticated;
use \App\Auth;
use \Core\View;
use \App\Models\Income;

class AddIncome extends Authenticated
{
  //Show add new Income
  public function newAction()
  {
    View::renderTemplate('Income/new.html', ['incomesCategories' => $this->getUserIncomeCategories()]);
  }
  //Get current user income categories
  private function getUserIncomeCategories()
  {
    return Income::findUserIncomesCategories($_SESSION['user_id']);
  }

  //Add income
  public function addNewIncomeAction()
  {
    //We are adding data from form add incom by creating new object
    $newIncome = new Income($_POST);
    if ($newIncome->saveIncome()) {
      \App\Flash::addMessage('Income added successfully.', \App\Flash::SUCCESS);
      $this->redirect('/');
    } else {
      View::renderTemplate('Income/new.html', [
        'incomesCategories' => $this->getUserIncomeCategories(),
        'newIncome' => $newIncome
      ]);
    }
  }
}
