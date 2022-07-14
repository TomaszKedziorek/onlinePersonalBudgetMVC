<?php


namespace App\Models;

use PDO;
use Twig\Node\CheckSecurityCallNode;

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

  //set determine start and end of given date
  public static function monthPeriod($inputDate)
  {
    $inputDate = \DateTime::createFromFormat('Y-m-d', $inputDate);
    $since = $inputDate->format('Y-m') . '-01';
    $for = $inputDate->format('Y-m-t');
    $period = ['since' => $since, 'for' => $for];
    return $period;
  }

  function getLimitAndExpenses()
  {
    $period = static::monthPeriod($this->inputDate);

    $sql = "SELECT 
            exp_limit.id AS limit_id, exp_limit.limit_amount AS `limit`, 
            exp_limit.date_of_limit,
            exp_limit.expense_category_assigned_to_user_id AS limit_category_id,
            exp_limit.user_id AS user_who_set_limit_id, exp_cat_user.name,
            exp_cat_user.id AS category_id, exp_cat_user.user_id AS user_category_id,
            exp_cat_user.id, exp.expense_category_assigned_to_user_id AS expense_category_id,
            exp.user_id AS expense_user_id, IFNULL(SUM(exp.amount),0) AS total_expenses
            FROM expense_limit exp_limit 
            INNER JOIN expenses_category_assigned_to_users AS exp_cat_user 
            ON exp_limit.user_id=exp_cat_user.user_id 
            AND exp_limit.expense_category_assigned_to_user_id=exp_cat_user.id
            AND exp_limit.user_id = :loggedID 
            AND exp_limit.expense_category_assigned_to_user_id=:expenseID
            AND exp_limit.date_of_limit BETWEEN :since AND :for
            LEFT JOIN expenses exp
            ON exp_limit.user_id = exp.user_id 
            AND exp_limit.expense_category_assigned_to_user_id=exp.expense_category_assigned_to_user_id
            AND exp.date_of_expense BETWEEN :since AND :for";

    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':loggedID', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindValue(':expenseID', $this->expenseCategoryID, PDO::PARAM_INT);
    $stmt->bindValue(':since', $period['since'], PDO::PARAM_STR);
    $stmt->bindValue(':for', $period['for'], PDO::PARAM_STR);

    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($data);
  }

  //get limits for category
  public function getLimitsForCategory()
  {
    $sql = "SELECT exp_limit.id AS limit_id, 
            exp_limit.limit_amount AS `limit`, 
            exp_limit.date_of_limit,
            exp_limit.expense_category_assigned_to_user_id AS category_id,
            exp_limit.user_id AS user_id, exp_cat_user.name
            FROM expense_limit exp_limit 
            INNER JOIN expenses_category_assigned_to_users AS exp_cat_user 
            ON exp_limit.user_id=exp_cat_user.user_id 
            AND exp_limit.expense_category_assigned_to_user_id=exp_cat_user.id
            AND exp_limit.user_id = :loggedID 
            AND exp_limit.expense_category_assigned_to_user_id =:categoryID";

    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':loggedID', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindValue(':categoryID', $this->expenseCategoryID, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);
  }

  //get all user limits for all categories
  public static function getAllUserLimits()
  {
    $sql = "SELECT exp_limit.id AS limit_id, 
              exp_limit.limit_amount AS `limit`, 
              exp_limit.date_of_limit,
              exp_limit.expense_category_assigned_to_user_id AS category_id,
              exp_limit.user_id AS user_id, exp_cat_user.name
              FROM expense_limit exp_limit 
              INNER JOIN expenses_category_assigned_to_users AS exp_cat_user 
              ON exp_limit.user_id=exp_cat_user.user_id 
              AND exp_limit.expense_category_assigned_to_user_id=exp_cat_user.id
              AND exp_limit.user_id = :loggedID";

    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':loggedID', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $data;
  }

  //validate limit
  public function validateLimit()
  {
    $correctData = true;
    $this->limit_amount = filter_var($this->limit_amount, FILTER_VALIDATE_FLOAT);
    if ($this->limit_amount == false) {
      $correctData = false;
    }
    if (!isset($this->limit_amount) || empty($this->limit_amount)) {
      $correctData = false;
    }

    $dateOfLimit = $this->date_of_limit;
    $dateArr  = explode('-', $dateOfLimit);
    if (strlen($dateOfLimit) != 10 || !checkdate($dateArr[1], $dateArr[2], $dateArr[0])) {
      $correctData = false;
    }
    return $correctData;
  }
  //EDIT LIMIT
  public function editLimit()
  {
    if ($this->validateLimit()) {
      $sql = "UPDATE expense_limit set limit_amount = :limit_amount, date_of_limit=:date_of_limit WHERE user_id =:userID AND id = :limit_id";

      $db = static::getDB();
      $stmt = $db->prepare($sql);
      $stmt->bindValue(':userID', $_SESSION['user_id'], PDO::PARAM_INT);
      $stmt->bindValue(':limit_id', $this->limit_id, PDO::PARAM_INT);
      $stmt->bindValue(':limit_amount', $this->limit_amount, PDO::PARAM_STR);
      $stmt->bindValue(':date_of_limit', $this->date_of_limit, PDO::PARAM_STR);

      return $stmt->execute();
    }
    return false;
  }
  //DELETE LIMIT
  public function deleteLimit()
  {
    $sql = "DELETE FROM expense_limit WHERE user_id =:userID AND id = :limit_id";

    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':userID', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindValue(':limit_id', $this->limit_id, PDO::PARAM_INT);
    return $stmt->execute();
  }
  //DELETE LIMIT for category id (after deleteng some category)
  public static function deleteLimitsForCategory($category_id)
  {
    $sql = "DELETE FROM expense_limit WHERE user_id =:userID AND expense_category_assigned_to_user_id = :category_id";

    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':userID', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
    return $stmt->execute();
  }

  //EDIT LIMIT
  public function addNewLimit()
  {
    if ($this->validateLimit()) {
      $sql = "INSERT INTO expense_limit(id, user_id, expense_category_assigned_to_user_id, limit_amount, date_of_limit) VALUES (NULL,:userID,:category_id,:limit_amount,:date_of_limit)";

      $db = static::getDB();
      $stmt = $db->prepare($sql);
      $stmt->bindValue(':userID', $_SESSION['user_id'], PDO::PARAM_INT);
      $stmt->bindValue(':category_id', $this->category_id, PDO::PARAM_INT);
      $stmt->bindValue(':limit_amount', $this->limit_amount, PDO::PARAM_STR);
      $stmt->bindValue(':date_of_limit', $this->date_of_limit, PDO::PARAM_STR);

      return $stmt->execute();
    }
    return false;
  }

  //Check if ther are limits in given date
  public function checkLimitInGivenDate($addOrEdit = "add")
  {
    $period = static::monthPeriod($this->date_of_limit);

    $sql = "SELECT * FROM expense_limit WHERE user_id = :loggedID 
            AND expense_category_assigned_to_user_id = :category_id
            AND date_of_limit BETWEEN :since AND :for";

    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':loggedID', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindValue(':category_id', $this->category_id, PDO::PARAM_INT);
    $stmt->bindValue(':since', $period['since'], PDO::PARAM_STR);
    $stmt->bindValue(':for', $period['for'], PDO::PARAM_STR);

    $stmt->execute();
    if ($addOrEdit == "add")
      $data = $stmt->fetch(PDO::FETCH_ASSOC);
    elseif ($addOrEdit == "edit")
      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $data;
  }
}
