<?php


namespace App\Models;

use PDO;
use \Core\View;


class Income extends \Core\Model
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
  public static function findUserIncomesCategories($user_id)
  {
    $sql = 'SELECT * FROM incomes_category_assigned_to_users WHERE user_id = :id';
    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  //Save new income in database
  public  function saveIncome()
  {
    if ($this->validateIncome()) {
      $sql = 'INSERT INTO incomes (id,user_id,income_category_assigned_to_user_id,amount,date_of_income,income_comment) 
              VALUES (:incomeID,:userID,:categoryID,:amount,:date,:comment)';

      $db = static::getDB();
      $stmt = $db->prepare($sql);
      $stmt->bindVAlue(':incomeID', NULL, PDO::PARAM_STR);
      $stmt->bindVAlue(':userID', $_SESSION['user_id'], PDO::PARAM_INT);
      $stmt->bindVAlue(':categoryID', $this->category, PDO::PARAM_INT);
      $stmt->bindVAlue(':amount', $this->amount, PDO::PARAM_STR);
      $stmt->bindVAlue(':date', $this->date, PDO::PARAM_STR);
      $stmt->bindVAlue(':comment', $this->comment, PDO::PARAM_STR);

      return $stmt->execute();
    }
    return false;
  }
  //Validate income data posted by user
  public function validateIncome()
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

    $dateOfIncome = filter_input(INPUT_POST, 'date');
    $dateArr  = explode('-', $dateOfIncome);
    if (strlen($dateOfIncome) != 10 || !checkdate($dateArr[1], $dateArr[2], $dateArr[0])) {
      $correctData = false;
      $this->e_date = 'The given date was incorrect!';
    }
    $this->date = $dateOfIncome;

    $category = filter_input(INPUT_POST, 'category', FILTER_VALIDATE_INT);
    if ($category == false) {
      $correctData = false;
      $this->e_category = 'This field is required!';
    } else {
      $this->checked = $category;
    }

    if (isset($_POST['comment']) && !empty($_POST['comment'])) {
      $this->comment = htmlentities($this->comment, ENT_QUOTES, "UTF-8");
      $this->commentToShow = $_POST['comment'];
    } else {
      $this->comment = '';
    }
    return $correctData;
  }

  // Get sum of incomes for every income category in given period
  public static function getIncomesInPeriod($since, $for)
  {
    //Change dates format to Y-m-d due to SQL
    $since = date_format(date_create($since), 'Y-m-d');
    $for = date_format(date_create($for), 'Y-m-d');

    $sql = 'SELECT incat.name AS name, SUM(incomes.amount) AS amount 
            FROM incomes_category_assigned_to_users AS incat, incomes 
            WHERE incomes.income_category_assigned_to_user_id = incat.id 
            AND incomes.user_id=:userID AND incat.user_id=:userID 
            AND incomes.date_of_income BETWEEN :since AND :for
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
