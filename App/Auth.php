<?php

namespace App;

use \App\Models\User;
use App\Models\RememberedLogin;

//Authentication
class Auth
{
  //Login the user
  public static function login($user, $remember_me)
  {
    //to prevent session fixation attack 
    session_regenerate_id(true);

    $_SESSION['user_id'] = $user->id;

    //if ther is $remember_me variable call function on user object
    if ($remember_me) {
      //rememberLogin() return true if unique token has been put to database successful
      //so we can set the cookie
      if ($user->rememberLogin()) {
        //name: remember_me, valuse: hashedMHAC token,expiry: now + 30days, Path: whole doamin
        setcookie('remember_me', $user->remember_token, $user->expiry_timestamp, '/');
      }
    }
  }

  //logout the user
  public static function logout()
  {
    // Unset all of the session variables.
    $_SESSION = array();
    // If it's desired to kill the session, also delete the session cookie.
    // Note: This will destroy the session, and not just the session data!
    if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
      );
    }
    // Finally, destroy the session.
    session_destroy();
    //Delete hashed token in database and unset cookie
    static::forgetLogin();
  }
  //Check if user is logged
  //because we can use getUser() to check if someone is logged in
  //method isLoggedIn() is redundant
  //public static function isLoggedIn(){return isset($_SESSION['user_id']);}

  //Remember the requested  page
  public static function rememberRequestedPage()
  {
    $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
  }
  //Get the originally  requested  page to return after login or default to home page
  public static function getReturnToPage()
  {
    //return url saved in Session or if not exist return url to home page
    return $_SESSION['return_to'] ?? '/';
  }

  //Get the current user from the session or the remember me cookie
  public static function getUser()
  {
    if (isset($_SESSION['user_id'])) {
      return User::findByID($_SESSION['user_id']);
    }
    //if there is no session started with user we can try login using cookie    
    else {
      return static::loginFromRememberCookie();
    }
  }

  //Login the user from a login cookie
  public static function loginFromRememberCookie()
  {
    //if in an associative array cookie with key 'remember_me" will return this
    //else return false 
    $cookie = $_COOKIE['remember_me'] ?? false;
    if ($cookie) {
      //if ther is such token this will return associative array
      $remembered_login = RememberedLogin::findByToken($cookie);
      //if ther is some record in database wiht such token and token hasn't expired
      //then log in user
      if ($remembered_login && !$remembered_login->hasExpired()) {
        //we need User object to call login() methot in Auth class
        $user = $remembered_login->getUser();

        static::login($user, false);

        return $user;
      }
    }
  }

  //Forget the remembered login if present
  protected static function forgetLogin()
  {
    //if in an associative array cookie with key 'remember_me" will return this
    //else return false 
    $cookie = $_COOKIE['remember_me'] ?? false;
    if ($cookie) {
      //if ther is such token this will return associative array otherwise fals
      $remembered_login = RememberedLogin::findByToken($cookie);
      //if true delete
      if ($remembered_login) {
        $remembered_login->delete();
      }
      //set to expire cookie in the past
      setcookie('remember_me', '', time() - 3600);
    }
  }
}
