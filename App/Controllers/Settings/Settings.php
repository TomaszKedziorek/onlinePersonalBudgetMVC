<?php

namespace App\Controllers\Settings;

use \App\Controllers\Authenticated;
use \Core\View;
use \App\Models\Income;
use \App\Models\Expense;
use \App\Models\GeneralSettings;

class Settings extends Authenticated
{
  public $whichIsActive = [];
  //Display settings
  public function showAction()
  {
    View::renderTemplate('Settings/settings.html', [
      'incomeCategories' => Income::findUserIncomesCategories($_SESSION['user_id']),
      'expenseCategories' => Expense::findUserExpensesCategories($_SESSION['user_id']),
      'paymentMethods' => Expense::findUserPaymentMethods($_SESSION['user_id'])
    ]);
  }

  //SHOW IF FAILURE
  public function showIfFailure($settingsObject, $twigSettingsObjectName)
  {
    View::renderTemplate('Settings/settings.html', [
      'incomeCategories' => Income::findUserIncomesCategories($_SESSION['user_id']),
      'expenseCategories' => Expense::findUserExpensesCategories($_SESSION['user_id']),
      'paymentMethods' => Expense::findUserPaymentMethods($_SESSION['user_id']),
      'isActive' => $this->whichIsActive,
      $twigSettingsObjectName => $settingsObject
    ]);
  }
  //ADD CATEGORY
  public function addCategory($settingsObject, $twigSettingsObjectName, $startOfMesage)
  {
    if ($settingsObject->saveNewCategory($_SESSION['user_id'])) {
      \App\Flash::addMessage("$startOfMesage added successfully", \App\Flash::SUCCESS);
      $this->redirect('/settings/settings/show');
    } else {
      \App\Flash::addMessage("$startOfMesage has not been added.", \App\Flash::WARNING);
      $this->showIfFailure($settingsObject, $twigSettingsObjectName);
    }
  }

  //EDIT CATEGORY
  public function editCategory($settingsObject, $twigSettingsObjectName, $startOfMesage)
  {
    //DELETE
    if (isset($settingsObject->processedCategoryDelete)) {
      if ($settingsObject->deleteCategory($settingsObject->categoryTransfer, $_SESSION['user_id'])) {
        \App\Flash::addMessage("$startOfMesage deleted.", \App\Flash::SUCCESS);
        $this->redirect('/settings/settings/show');
      } else {
        \App\Flash::addMessage("$startOfMesage has not been deleted.", \App\Flash::WARNING);
        $this->showIfFailure($settingsObject, $twigSettingsObjectName);
      }
    } else { //EDIT
      if ($settingsObject->editCategoryName($_SESSION['user_id'])) {
        \App\Flash::addMessage("$startOfMesage edited successfully.", \App\Flash::SUCCESS);
        $this->redirect('/settings/settings/show');
      } else {
        $this->showIfFailure($settingsObject, $twigSettingsObjectName);
      }
    }
  }

  //Add new income category
  public function addIncomeCategoryAction()
  {
    $this->whichIsActive = "incomeCategory";
    $newIncomeCategory = new GeneralSettings($_POST, $this->whichIsActive);
    $this->addCategory($newIncomeCategory, 'newIncomeCategory', 'Income category');
  }

  //Eddit income category
  public function editIncomeCategoryAction()
  {
    $this->whichIsActive = "incomeCategory";
    $editedIncomeCategory = new GeneralSettings($_POST, $this->whichIsActive);
    $this->editCategory($editedIncomeCategory, 'editedIncomeCategory', 'Income category');
  }

  //Add new expense category
  public function addExpenseCategoryAction()
  {
    $this->whichIsActive = "expenseCategory";
    $newExpenseCategory = new GeneralSettings($_POST, $this->whichIsActive);
    $this->addCategory($newExpenseCategory, 'newExpenseCategory', 'Expense category');
  }

  //Eddit expense category
  public function editExpenseCategoryAction()
  {
    $this->whichIsActive = "expenseCategory";
    $editedExpenseCategory = new GeneralSettings($_POST, $this->whichIsActive);
    $this->editCategory($editedExpenseCategory, 'editedExpenseCategory', 'Expense category');
  }

  //Add new payment method --> category
  public function addPaymentCategoryAction()
  {
    $this->whichIsActive = "paymentMethod";
    $newPaymentCategory = new GeneralSettings($_POST, $this->whichIsActive);
    $this->addCategory($newPaymentCategory, 'newPaymentCategory', 'Payment method');
  }

  //Eddit payment method --> category
  public function editPaymentCategoryAction()
  {
    $this->whichIsActive = "paymentMethod";
    $editedPaymentCategory = new GeneralSettings($_POST, $this->whichIsActive);
    $this->editCategory($editedPaymentCategory, 'editedPaymentCategory', 'Payment method');
  }
}
