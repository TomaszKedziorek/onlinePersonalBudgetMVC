<?php

namespace App;

//Unique random tokens
class Token
{
  //The token value
  protected $token;
  //Class constructor 
  //Create a new random token
  //optional argument token_value to get hash of existing token
  public function __construct($token_value = null)
  {
    //if we pass some value in costructor it will use this value to hash
    if ($token_value) {
      $this->token = $token_value;
    } else {
      $this->token = bin2hex(random_bytes(16)); //16bytes=128bits=32 chars
    }
  }
  //Get the token value
  public function getValue()
  {
    return $this->token;
  }
  //Get the hashed token
  public function getHash()
  {
    return hash_hmac('sha256', $this->token, \App\Config::SECRET_KEY); //sha256 ->64chars
  }
}
