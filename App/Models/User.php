<?php

namespace App\Models;

use PDO;
use \App\Token;
use \Core\View;
use \App\Mail;


/**
 * Example user model
 *
 * PHP version 7.0
 */
class User extends \Core\Model
{
  //Error massages
  public $errors = [];
  //Class constructor
  //optional parameters $data due to Login findByEmail() return object not array
  public function __construct($data = [])
  {
    //Change associative array form $_POST to variables
    //with names as They keys
    foreach ($data as $key => $value) {
      $this->$key = $value;
    }
  }
  //Save the user with current property values
  public function save()
  {
    $this->validate();

    if (empty($this->errors)) {
      $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

      //To validation of an account
      $token = new Token();
      $hashed_token = $token->getHash();
      $this->activation_token = $token->getValue();

      $sql = 'INSERT INTO users (id,name,email,password_hash,activation_hash) 
              VALUES (:id,:name,:email,:password_hash,:activation_hash)';
      $db = static::getDB();
      $stmt = $db->prepare($sql);
      $stmt->bindValue(':id', NULL, PDO::PARAM_STR);
      $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
      $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
      $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
      $stmt->bindValue(':activation_hash', $hashed_token, PDO::PARAM_STR);
      //column is_active in DB is by default fals
      return $stmt->execute();
    }

    return false;
  }
  //Validate current valuses, adding validation error massage an arayy poperty
  public function validate()
  {
    //Name
    if ($this->name == '') {
      $this->errors[] = 'Name is required';
    }
    //Email
    if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
      $this->errors[] = 'Invalid email.';
    }
    //Additional argument  is for reseting the password 
    if (static::emailExists($this->email, $this->id ?? null)) {
      $this->errors[] = 'Email already taken.';
    }
    // Password
    if (isset($this->password)) {
      if (strlen($this->password) < 6) {
        $this->errors[] = 'Password needs at least 6 chaacters.';
      }
      if (preg_match('/.*[a-z]+.*/i', $this->password) == 0) {
        $this->errors[] = 'Password needs at least one letter.';
      }
      if (preg_match('/.*\d+.*/i', $this->password) == 0) {
        $this->errors[] = 'Password needs at least one number.';
      }
    }
  }
  //See if an email address already exists
  //We will use this function to validate email n JavaScript
  //That is why is static
  //to call static method use static:: operator isnide this class
  //or className:: otherwhise
  //Additional argument ignore_id is for reseting the password 
  //to ignor this fild  during validation
  public static function emailExists($email, $ignore_id = null)
  {
    //previously
    // return static::findByEmail($email) !== false;
    //Due to validation during password reset
    $user = static::findByEmail($email);
    if ($user) {
      if ($user->id != $ignore_id) {
        return true;
      }
    }
    return false;
  }

  //Find a user by email adress
  public static function findByEmail($email)
  {
    $sql = 'SELECT * FROM users WHERE email = :email';
    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);

    //By default PDO::fetch() return array. We can change this before exequting 
    //to return object so we can use methods of this object later
    //Change constructor of User parameter to optional
    $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

    $stmt->execute();
    //PDO fetch returns false if there is no results
    //WE are returning true if ther is an email or false otherwise
    return $stmt->fetch();
  }

  //Authenticate a user by email and password
  public static function authentication($email, $password)
  {
    $user = static::findByEmail($email);
    //chceck if ther is such user and its accound is active
    if ($user && $user->is_active) {
      if (password_verify($password, $user->password_hash)) {
        return $user;
      }
      return false;
    }
  }

  //Find a user by ID
  public static function findByID($id)
  {
    $sql = 'SELECT * FROM users WHERE id = :id';
    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    //By default PDO::fetch() return array. We can change this before exequting 
    //to return object so we can use methods of this object later
    //Change constructor of User parameter to optional
    $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

    $stmt->execute();
    //PDO fetch returns false if there is no results
    //WE are returning true if ther is an email or false otherwise
    return $stmt->fetch();
  }

  //Remember the login by inserting aunique token into the remembered-logins
  //table for user record
  public function rememberLogin()
  {
    $token = new Token();
    $hashed_token = $token->getHash();
    //$this enable us to call those variables in App\Auth.php
    $this->remember_token = $token->getValue();

    //expiry date
    $this->expiry_timestamp = time() + 60 * 60 * 24 * 30; //30 days from now
    $sql = 'INSERT INTO remembered_logins (token_hash,user_id,expires_at) 
            VALUES (:token_hash,:user_id,:expires_at)';

    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
    $stmt->bindValue(':user_id', $this->id, PDO::PARAM_INT);
    //Date in SQL must has such format
    $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $this->expiry_timestamp), PDO::PARAM_STR);

    return $stmt->execute();
  }

  //Send password reset instructions to the user specified
  public static function sendPasswordReset($email)
  {
    $user = static::findByEmail($email);
    if ($user) {
      if ($user->startPasswordReset()) {
        $user->sendPasswordResetEmail();
      }
    }
  }

  //Start the password generation process by generating a newtoken and expiry
  public function startPasswordReset()
  {
    $token = new Token();
    $hashed_token = $token->getHash();
    //$this enable us to call those variables in App\Auth.php
    $this->password_reset_token = $token->getValue();

    //expiry date
    $expiry_timestamp = time() + 60 * 60 * 2; //2 hours from now
    $sql = 'UPDATE users 
            SET password_reset_hash = :token_hash,
            password_reset_expires_at = :expires_at
            WHERE id = :id';

    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
    $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
    //Date in SQL must has such format
    $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $expiry_timestamp), PDO::PARAM_STR);

    return $stmt->execute();
  }

  //Send password reset instructions in an email to the user
  protected function sendPasswordResetEmail()
  {
    $url = 'http://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this->password_reset_token;
    $text = View::getTemplate('Password/reset_email.txt', ['url' => $url]);
    $html = View::getTemplate('Password/reset_email.html', ['url' => $url]);
    Mail::send($this->email, 'Password reset', $html, $text);
  }

  //Find a user model by password reset token and expiry
  public static function findByPasswordReset($token)
  {
    $token = new Token($token);
    $hashed_token = $token->getHash();

    $sql = 'SELECT * FROM users 
            WHERE password_reset_hash = :token_hash';

    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
    //we want to return object
    $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
    $stmt->execute();

    //returns false if ther is no such token hash
    $user =  $stmt->fetch();

    if ($user) {
      //check if the Password reset token has expired or not, based on the current system time
      if (strtotime($user->password_reset_expires_at) > time())
        //return NULL if expired  
        return $user;
    }
  }

  //Reset the password
  public function resetPassword($password)
  {
    $this->password = $password;
    $this->validate();
    if (empty($this->errors)) {
      $passwrd_hash = password_hash($this->password, PASSWORD_DEFAULT);
      $sql = 'UPDATE users 
              SET password_hash = :password_hash ,
                  password_reset_hash = NULL,
                  password_reset_expires_at = NULL
                  WHERE id=:id';

      $db = static::getDB();
      $stmt = $db->prepare($sql);
      $stmt->bindValue(':password_hash', $passwrd_hash, PDO::PARAM_STR);
      $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
      return $stmt->execute();
    }
    return false;
  }

  //Send activation email to the user
  public function sendActivationEmail()
  {
    $url = 'http://' . $_SERVER['HTTP_HOST'] . '/signup/activate/' . $this->activation_token;
    $text = View::getTemplate('Signup/activation_email.txt', ['url' => $url]);
    $html = View::getTemplate('Signup/activation_email.html', ['url' => $url]);
    Mail::send($this->email, 'Account activation', $html, $text);
  }

  //Activate the user account with the specific activation token
  //$value activation token from URL
  public static function activate($value)
  {
    $token = new Token($value);
    $hashed_token = $token->getHash();
    $sql = 'UPDATE users 
              SET is_active = 1 ,
                  activation_hash = NULL
                  WHERE activation_hash=:hashed_token';

    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':hashed_token', $hashed_token, PDO::PARAM_STR);
    $stmt->execute();
  }

  //Update the user's profile
  public function updateProfile($data)
  {
    $this->name = $data['name'];
    $this->email = $data['email'];
    // Only validate and update the password if a value provided
    if ($data['password'] != '') {
      $this->password = $data['password'];
    }

    $this->validate();

    if (empty($this->errors)) {
      $sql = 'UPDATE users SET name = :name, email = :email';
      // Add password if it's set
      if (isset($this->password)) {
        $sql .= ', password_hash = :password_hash';
      }
      $sql .= "\nWHERE id = :id";

      $db = static::getDB();
      $stmt = $db->prepare($sql);

      $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
      $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
      $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

      // Add password if it's set
      if (isset($this->password)) {
        $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
        $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
      }
      return $stmt->execute();
    }
    return false;
  }


  //Load default categories of incomes, expenses and payment methods for every new user
  public function loadCategories()
  {
    $user = static::findByEmail($this->email);
    $db = static::getDB();
    //Load default INCOME categories
    $sql = 'INSERT INTO incomes_category_assigned_to_users (id,user_id,name) SELECT :id,:user_id,name FROM incomes_category_default';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':id', NULL, PDO::PARAM_STR);
    $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
    $stmt->execute();
    //Load default EXPENSES categories
    $sql = 'INSERT INTO expenses_category_assigned_to_users (id,user_id,name) SELECT :id,:user_id,name FROM expenses_category_default';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':id', NULL, PDO::PARAM_STR);
    $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
    $stmt->execute();
    //Load default payment methods
    $sql = 'INSERT INTO payment_methods_assigned_to_users (id,user_id,name) SELECT :id,:user_id,name FROM payment_methods_default';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':id', NULL, PDO::PARAM_STR);
    $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
    $stmt->execute();
  }
}
