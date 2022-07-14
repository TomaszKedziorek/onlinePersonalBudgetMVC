<?php

namespace App\Models;

use PDO;

class GeneralSettings extends \Core\Model
{
  //Class constructor
  public function __construct($data = [], $categoryOfCategory = [])
  {
    //Change associative array form $_POST to variables
    //with names as They keys
    foreach ($data as $key => $value) {
      $this->$key = $value;
    }

    //Select table set to use in functions on the basis of
    //passed categoryOfCategory
    if ($categoryOfCategory == 'incomeCategory') {
      $this->categoryAssignedToUsers = 'incomes_category_assigned_to_users';
      $this->InExUsersTable  = 'incomes';
      $this->asignedToUserCategoryID = 'income_category_assigned_to_user_id';
      //
    } elseif ($categoryOfCategory == 'expenseCategory') {
      $this->categoryAssignedToUsers = 'expenses_category_assigned_to_users';
      $this->InExUsersTable  = 'expenses';
      $this->asignedToUserCategoryID = 'expense_category_assigned_to_user_id';
      //
    } elseif ($categoryOfCategory == 'paymentMethod') {
      $this->categoryAssignedToUsers = 'payment_methods_assigned_to_users';
      $this->InExUsersTable = 'expenses';
      $this->asignedToUserCategoryID = 'payment_method_assigned_to_user_id';
    }
  }
  //Validate new category
  public function vlidateCategoryName($user_id)
  {
    $correctData = true;
    $this->processedCategoryName = trim($this->processedCategoryName);
    if (!preg_match('/^[a-z0-9 ]+$/i', $this->processedCategoryName)) {
      $this->e_category = "It can contain only letters and numbers.";
      $correctData = false;
    }
    if (empty($this->processedCategoryName) || ctype_space($this->processedCategoryName)) {
      $this->e_category = "It must contain at least one letter.";
      $correctData = false;
    }
    if (strlen($this->processedCategoryName) > 20) {
      $this->e_category = "The name can be at least 20 characters long.";
      $correctData = false;
    }
    $foundCategory = $this->findCategoryByName($this->processedCategoryName, $user_id);
    if ($foundCategory) {
      $this->e_category = "Such category already exist.";
      $correctData = false;
    }
    return $correctData;
  }

  //Find category by name
  public function findCategoryByName($processedCategoryName, $user_id)
  {
    $tableName = $this->categoryAssignedToUsers;
    $sql = "SELECT * FROM $tableName WHERE name = :name AND user_id = :userID";
    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':name', $processedCategoryName, PDO::PARAM_STR);
    $stmt->bindValue(':userID', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  //Save new category in database
  public function saveNewCategory($user_id)
  {
    if ($this->vlidateCategoryName($user_id)) {
      $tableName = $this->categoryAssignedToUsers;
      $sql = "INSERT INTO $tableName (id,user_id,name) VALUES (:id,:user_id,:name)";
      $db = static::getDB();
      $stmt = $db->prepare($sql);
      $stmt->bindValue(':id', NULL, PDO::PARAM_STR);
      $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
      $stmt->bindValue(':name', $this->processedCategoryName, PDO::PARAM_STR);
      return $stmt->execute();
    }
    return false;
  }

  //Eddit category
  public function editCategoryName($user_id)
  {
    if ($this->vlidateCategoryName($user_id)) {
      $tableName = $this->categoryAssignedToUsers;
      $sql = "UPDATE $tableName set name = :newName WHERE user_id =  :user_id AND id = :id";
      $db = static::getDB();
      $stmt = $db->prepare($sql);
      $stmt->bindValue(':id', $this->processedCategoryID, PDO::PARAM_INT);
      $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
      $stmt->bindValue(':newName',  $this->processedCategoryName, PDO::PARAM_STR);
      return $stmt->execute();
    }
    return false;
  }

  //Delete category and tranfer its  to Another
  //by change id
  public function deleteCategory($categoryTransfer, $user_id)
  {
    $tableName = $this->categoryAssignedToUsers;
    if ($this->changeIDBeforeDeletingCategory($categoryTransfer, $user_id)) {
      $sql = "DELETE FROM $tableName WHERE user_id = :user_id AND id = :id";
      $db = static::getDB();
      $stmt = $db->prepare($sql);
      $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
      $stmt->bindValue(':id', $this->processedCategoryID, PDO::PARAM_INT);
      return $stmt->execute();
    }
    return false;
  }

  //Change incomes id to undeletable  category "Another"
  public function changeIDBeforeDeletingCategory($categoryTransfer, $user_id)
  {
    $undeletableCategory = $this->findCategoryByName($categoryTransfer, $user_id);
    $tableName = $this->InExUsersTable;
    $columnName = $this->asignedToUserCategoryID;
    $sql = "UPDATE $tableName SET $columnName = :newID WHERE user_id =  :user_id AND $columnName = :oldID";
    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':oldID', $this->processedCategoryID, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':newID', $undeletableCategory["id"], PDO::PARAM_INT);
    return $stmt->execute();
  }
}
