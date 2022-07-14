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
      'paymentMethods' => Expense::findUserPaymentMethods($_SESSION['user_id']),
      'userLimits' => Expense::getAllUserLimits()
    ]);
  }

  //SHOW IF FAILURE
  public function showIfFailure($settingsObject, $twigSettingsObjectName)
  {
    View::renderTemplate('Settings/settings.html', [
      'incomeCategories' => Income::findUserIncomesCategories($_SESSION['user_id']),
      'expenseCategories' => Expense::findUserExpensesCategories($_SESSION['user_id']),
      'paymentMethods' => Expense::findUserPaymentMethods($_SESSION['user_id']),
      'userLimits' => Expense::getAllUserLimits(),
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
        Expense::deleteLimitsForCategory($settingsObject->processedCategoryID);
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

  //EDIT LIMIT
  public function editLimit()
  {
    $editedLimit = new Expense($_POST);
    //DELETE
    if (isset($editedLimit->limitDelete)) {
      if ($editedLimit->deleteLimit()) {
        \App\Flash::addMessage("Limit deleted.", \App\Flash::SUCCESS);
        $this->redirect('/settings/settings/show');
      } else {
        \App\Flash::addMessage("Limit has not been deleted.", \App\Flash::WARNING);
        $this->redirect('/settings/settings/show');
      }
    } else { //EDIT
      //If ther is already limit set with month you want to edit
      if ($this->checkIfNotEdit($editedLimit)) {
        \App\Flash::addMessage("Limit in given month is already set.", \App\Flash::WARNING);
        $this->redirect('/settings/settings/show');
      } elseif ($editedLimit->editLimit()) {
        \App\Flash::addMessage("Limit edited successfully.", \App\Flash::SUCCESS);
        $this->redirect('/settings/settings/show');
      } else {
        \App\Flash::addMessage("Limit edited unsuccessfully.", \App\Flash::WARNING);
        $this->redirect('/settings/settings/show');
      }
    }
  }
  //ADD NEW LIMIT
  public function addNewLimitAction()
  {
    $newLimit = new Expense($_POST);
    if ($this->updateLimitIfIsSet($newLimit)) {
      \App\Flash::addMessage("New limit has replaced the existing one on the given date.", \App\Flash::SUCCESS);
      $this->redirect('/settings/settings/show');
    } elseif ($newLimit->addNewLimit()) {
      \App\Flash::addMessage("Limit has been added.", \App\Flash::SUCCESS);
      $this->redirect('/settings/settings/show');
    } else {
      \App\Flash::addMessage("Limit has not been added.", \App\Flash::WARNING);
      $this->redirect('/settings/settings/show');
    }
  }

  //If limit in given date already exist update it with new data
  public function updateLimitIfIsSet($newLimit)
  {
    $oldLimit = $newLimit->checkLimitInGivenDate();
    if ($oldLimit) {
      $newLimit->limit_id = $oldLimit['id'];
      return $newLimit->editLimit();
    } else {
      return false;
    }
  }

  //check if can edit existin limit with no date collisions
  public function checkIfNotEdit($editedLimit)
  {
    $limit = $editedLimit;
    $existingLimits = $editedLimit->checkLimitInGivenDate("edit");
    if (count($existingLimits) == 0) {
      return false;
    } elseif (count($existingLimits) == 1 && $existingLimits[0]['id'] == $limit->limit_id) {
      return false;
    } else {
      return true;
    }
  }
}
