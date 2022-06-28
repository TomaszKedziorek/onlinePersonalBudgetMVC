<?php

namespace App\Controllers\AddExpense;

use \App\Controllers\Authenticated;
use \App\Auth;
use \Core\View;
use \App\Models\Expense;

class AddExpense extends Authenticated
{
  //Show add new Expense
  public function newAction()
  {
    View::renderTemplate('Expense/new.html', [
      'expenseCategories' => $this->getUserExpenseCategories(),
      'paymentMethods' => $this->getUserPaymentMethods()
    ]);
  }
  //Get current user income categories
  private function getUserExpenseCategories()
  {
    return Expense::findUserExpensesCategories($_SESSION['user_id']);
  }
  //Get current user payment methods
  private function getUserPaymentMethods()
  {
    return Expense::findUserPaymentMethods($_SESSION['user_id']);
  }

  //Add new Expense to database
  public function addNewExpenseAction()
  {
    //We are adding data from form add incom by creating new object
    $newExpense = new Expense($_POST);
    if ($newExpense->saveExpense()) {
      \App\Flash::addMessage('Expense added successfully.', \App\Flash::SUCCESS);
      $this->redirect('/');
    } else {
      View::renderTemplate('Expense/new.html', [
        'expenseCategories' => $this->getUserExpenseCategories(),
        'paymentMethods' => $this->getUserPaymentMethods(),
        'newExpense' => $newExpense
      ]);
    }
  }
}
