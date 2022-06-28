<?php

namespace App\Models;

use \App\Token;
use PDO;
use \App\Models\User;
//Remembered login model
class RememberedLogin extends \Core\Model
{
  //Find a remembered login by the token
  public static function findByToken($token)
  {
    //Create new object of Token class to use getHash method 
    //in database token is hashed
    $token = new Token($token);
    $token_hash = $token->getHash();

    $sql = 'SELECT * FROM remembered_logins WHERE token_hash = :token_hash';
    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':token_hash', $token_hash, PDO::PARAM_STR);

    //By default PDO::fetch() return array. We can change this before exequting 
    //to return object so we can use methods of this object later
    //Change constructor of User parameter to optional
    $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

    $stmt->execute();
    //PDO fetch returns false if there is no results
    //WE are returning true if ther is an email or false otherwise
    return $stmt->fetch();
  }

  //Get the user model associated with this remembered login
  //we need User object to call login() methot in Auth class
  public function getUser()
  {
    return User::findByID($this->user_id);
  }

  //See if the remembered token has expired or not, based on the current system time
  public function hasExpired()
  {
    //expired of token in DB is string in 'Y-m-d H:i-s' format
    //convert this to system time by strtotime() function
    //return true if the token has expired, false otherwise
    return strtotime($this->expires_at) < time();
  }

  //Delete this model (remembered login)
  public function delete()
  {
    $sql = 'DELETE FROM remembered_logins WHERE token_hash = :token_hash';
    $db = static::getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':token_hash', $this->token_hash, PDO::PARAM_STR);
    $stmt->execute();
  }
}
