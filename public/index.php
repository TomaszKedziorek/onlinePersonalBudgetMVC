<?php

/**
 * Front controller
 *
 * PHP version 7.0
 */

//ini_set('session.cookie_lifetime', '864000'); //10 days in seconds

// //Composer
require dirname(__DIR__) . '/vendor/autoload.php';


//Error and Exception handling
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');

//Session start
//We need start stssion only here because everything is passing through his script
//but after error handler so if somethings go wrong we will get the error message
session_start();
// Routing
$router = new Core\Router();

// Add the routes
$router->add('', ['controller' => 'Home', 'action' => 'index']);
//to enable route: http://mvcphpframework.original/login
//not only http://mvcphpframework.original/login/new
$router->add('login', ['controller' => 'Login', 'action' => 'new']);
$router->add('logout', ['controller' => 'Login', 'action' => 'destroy']);
//add route with regex to enable only valid token (hex number) reset password
$router->add('password/reset/{token:[\da-f]+}', ['controller' => 'Password', 'action' => 'reset']);
//add route with regex to enable only valid token (hex number) activation od an account
$router->add('signup/activate/{token:[\da-f]+}', ['controller' => 'Signup', 'action' => 'activate']);
$router->add('add-income/{controller}/{action}', ['namespace' => 'AddIncome']);
$router->add('add-expense/{controller}/{action}', ['namespace' => 'AddExpense']);
$router->add('balance/{controller}/{action}', ['namespace' => 'Balance']);
$router->add('settings/{controller}/{action}', ['namespace' => 'Settings']);



$router->add('{controller}/{action}');

$router->dispatch($_SERVER['QUERY_STRING']);
