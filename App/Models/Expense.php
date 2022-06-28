<?php


namespace App\Models;

use PDO;

class Expense extends \Core\Model
{
  //Class constructor
  public function __construct($data = [])
  {
    //Change associative array form $_POST to variables
    //with names as They keys
    foreach ($data as $key => $value) {
      $this->$key = $value;
    }
  }
  //Find a user categories by ID
  public static function findUserExpensesCategories($user_id)
  {
    $sql = 'SELECT id, name FROM expenses_category_assigned_to_users WHERE user_id = :id';
    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
  }
  //Find a user categories by ID
  public static function findUserPaymentMethods($user_id)
  {
    $sql = 'SELECT * FROM payment_methods_assigned_to_users WHERE user_id = :id';
    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  //Save new expense in database
  public  function saveExpense()
  {
    if ($this->validateExpense()) {
      $sql = 'INSERT INTO expenses (id,user_id,expense_category_assigned_to_user_id,payment_method_assigned_to_user_id,amount,date_of_expense,expense_comment) 
              VALUES (:expenseID,:userID,:categoryID,:paymentMethodID,:amount,:date,:comment)';

      $db = static::getDB();
      $stmt = $db->prepare($sql);
      $stmt->bindVAlue(':expenseID', NULL, PDO::PARAM_STR);
      $stmt->bindVAlue(':userID', $_SESSION['user_id'], PDO::PARAM_INT);
      $stmt->bindVAlue(':categoryID', $this->category, PDO::PARAM_INT);
      $stmt->bindVAlue(':paymentMethodID', $this->paymentMethod, PDO::PARAM_INT);
      $stmt->bindVAlue(':amount', $this->amount, PDO::PARAM_STR);
      $stmt->bindVAlue(':date', $this->date, PDO::PARAM_STR);
      $stmt->bindVAlue(':comment', $this->comment, PDO::PARAM_STR);

      return $stmt->execute();
    }
    return false;
  }

  //Validate income data posted by user
  public function validateExpense()
  {
    $correctData = true;

    $this->amount = filter_var($this->amount, FILTER_VALIDATE_FLOAT);
    if ($this->amount == false) {
      $correctData = false;
      $this->e_amount = 'This field can only contain numbers!';
    }
    if (!isset($this->amount) || empty($this->amount)) {
      $correctData = false;
      $this->e_amount = 'This field is required!';
    }

    $dateOfExpense = filter_input(INPUT_POST, 'date');
    $dateArr  = explode('-', $dateOfExpense);
    if (strlen($dateOfExpense) != 10 || !checkdate($dateArr[1], $dateArr[2], $dateArr[0])) {
      $correctData = false;
      $this->e_date = 'The given date was incorrect!';
    }
    $this->date = $dateOfExpense;

    $paymentMethod = filter_input(INPUT_POST, 'paymentMethod', FILTER_VALIDATE_INT);
    if ($paymentMethod == false) {
      $correctData = false;
      $this->e_paymentMethod = 'This field is required!';
    } else {
      $this->checked = $paymentMethod;
    }

    $category = filter_input(INPUT_POST, 'category', FILTER_VALIDATE_INT);
    if ($category == false) {
      $correctData = false;
      $this->e_category = 'This field is required!';
    } else {
      $this->selected = $category;
    }

    if (isset($_POST['comment']) && !empty($_POST['comment'])) {
      $this->comment = htmlentities($this->comment, ENT_QUOTES, "UTF-8");
      $this->commentToShow = $_POST['comment'];
    } else {
      $this->comment = '';
    }
    return $correctData;
  }

  // Get sum of expenses for every expens category in given period
  public static function getExpensesInPeriod($since, $for)
  {
    //Change dates format to Y-m-d due to SQL
    $since = date_format(date_create($since), 'Y-m-d');
    $for = date_format(date_create($for), 'Y-m-d');

    $sql = 'SELECT excat.name AS name, SUM(expenses.amount) AS amount 
            FROM expenses_category_assigned_to_users AS excat, expenses 
            WHERE expenses.expense_category_assigned_to_user_id = excat.id 
            AND expenses.user_id=:userID AND excat.user_id=:userID 
            AND expenses.date_of_expense BETWEEN :since AND :for
            GROUP BY name ORDER BY name';
    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':userID', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindValue(':since', $since, PDO::PARAM_STR);
    $stmt->bindValue(':for', $for, PDO::PARAM_STR);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
