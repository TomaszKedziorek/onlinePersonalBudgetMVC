<?php

namespace App\Controllers\Balance;

use \App\Controllers\Authenticated;
use \Core\View;
use \App\Models\Incomes;
use \App\Models\Expense;
use App\Models\Income;

class Balance extends Authenticated
{
  //Array with dates for given balance and selected period (radio buttons)
  protected $balancePeriod = [];
  //Array of incomes
  protected $incomes = [];
  //Array of expenses
  protected $expenses = [];
  //Array with sum  incomes, expenses and total
  protected $total = [];
  //Before any action determine period for balance
  protected function before()
  {
    parent::before();
    $this->determineDate();
    $this->getIncomes();
    $this->getExpenses();
    $this->calculateTotal();
  }
  //Show the balance page
  public function showAction()
  {
    View::renderTemplate('Balance/show.html', [
      'balancePeriod' => $this->balancePeriod,
      'incomes' => $this->incomes,
      'expenses' => $this->expenses,
      'total' => $this->total
    ]);
  }

  //
  public function determineDate()
  {
    $today = new \DateTime();
    $yyyy = $today->format('Y');
    $mm = $today->format('m');

    if (isset($_POST['dateCategory'])) {
      switch ($_POST['dateCategory']) {
        case 'currentMonth':
          $since = $today->format('Y-m') . '-01';
          $for = $today->format('Y-m-t');
          $checkedDate = 'currentMonth';
          break;
        case 'previousMonth':
          if ($mm == '01') {
            $since = $yyyy - 1 . '-12-01';
            $for = $yyyy - 1 . '-12-31';
          } else {
            $since = $yyyy . '-' . str_pad($mm - 1, 2, '0', STR_PAD_LEFT) . '-01';
            $for = $yyyy . '-' . str_pad($mm - 1, 2, '0', STR_PAD_LEFT) . '-' . cal_days_in_month(CAL_GREGORIAN, $mm - 1, $yyyy);
          }
          $checkedDate = 'previousMonth';
          break;
        case 'currentYear':
          $since = $yyyy . '-01-01';
          $for = $yyyy . '-12-31';
          $checkedDate = 'currentYear';
          break;
        default:
          $since = $today->format('Y-m') . '-01';
          $for = $today->format('Y-m-t');
          $checkedDate = 'currentMonth';
          break;
      }
    } elseif (isset($_POST['customDate']) && $_POST['customDate'] == 'customDate' && isset($_POST['datesince']) && isset($_POST['datefor'])) {
      if ($_POST['datesince'] < $_POST['datefor']) {
        $since = $_POST['datesince'];
        $for = $_POST['datefor'];
      } else {
        $since = $_POST['datefor'];
        $for = $_POST['datesince'];
      }
      $checkedDate = 'customDate';
    } else {
      $since = $today->format('Y-m') . '-01';
      $for = $today->format('Y-m-t');
      $checkedDate = 'currentMonth';
    }

    $this->balancePeriod = [
      'since' => date_format(date_create($since), 'd.m.Y'),
      'for'   =>  date_format(date_create($for), 'd.m.Y'),
      'checkedDate' => $checkedDate
    ];
  }

  //get incomes
  public function getIncomes()
  {
    $this->incomes = Income::getIncomesInPeriod($this->balancePeriod['since'], $this->balancePeriod['for']);
  }

  //get expenses
  public function getExpenses()
  {
    $this->expenses = Expense::getExpensesInPeriod($this->balancePeriod['since'], $this->balancePeriod['for']);
  }

  //Calculate sum of incomes, expenses and both
  private function calculateTotal()
  {
    $this->total['totalIncomes'] = array_sum(array_column($this->incomes, 'amount'));
    $this->total['totalExpenses'] = array_sum(array_column($this->expenses, 'amount'));
    $this->total['totalBalance'] = $this->total['totalIncomes'] - $this->total['totalExpenses'];
  }
}
