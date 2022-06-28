<?php

namespace App;

/**
 * Application configuration
 *
 * PHP version 7.0
 */
class Config
{
  /**
   * Database host
   * @var string
   */
  const DB_HOST = 'localhost';

  /**
   * Database name
   * @var string
   */
  const DB_NAME = 'budgetMVC';

  /**
   * Database user
   * @var string
   */
  const DB_USER = 'root';

  /**
   * Database password
   * @var string
   */
  const DB_PASSWORD = '';

  /**
   * Show or hide error messages on screen
   * @var boolean
   */
  const SHOW_ERRORS = true;

  //Secret key for hashing np ze strony https://randomkeygen.com/
  const SECRET_KEY = 'J8e22o1sR1FTD7QExPJjTEDJI56kCaiw';
					  
  //Secret key for hashing np ze strony https://randomkeygen.com/
  const EMAIL_ADDRESS = 'someemail@gmail.com';
}
