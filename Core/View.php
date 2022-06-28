<?php

namespace Core;

use App\Auth;

/**
 * View
 *
 * PHP version 7.0
 */
class View
{

  /**
   * Render a view file
   *
   * @param string $view  The view file
   * @param array $args  Associative array of data to display in the view (optional)
   *
   * @return void
   */
  public static function render($view, $args = [])
  {
    //Return sets of separate variables wiht name as in array
    extract($args, EXTR_SKIP);

    $file = dirname(__DIR__) . "/App/Views/$view";  // relative to Core directory

    if (is_readable($file)) {
      require $file;
    } else {
      throw new \Exception("$file not found");
    }
  }

  /**
   * Render a view template using Twig
   *
   * @param string $template  The template file
   * @param array $args  Associative array of data to display in the view (optional)
   *
   * @return void
   */
  public static function renderTemplate($template, $args = [])
  {
    echo static::getTemplate($template, $args);
  }

  /*
  * Get the content of the view template using Twig
  * @param string $template  The template file
  * @param array $args  Associative array of data to display in the view (optional)
  *
  */
  //Dodaliśmy tą funkcję aby można było traktować zawartość tych szablonów jako zmienne z tekstem
  // możemy więc je wysyłać mailem, choć są to np duże pliki html
  public static function getTemplate($template, $args = [])
  {
    static $twig = null;

    if ($twig === null) {
      $loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/App/Views');
      $twig = new \Twig\Environment($loader);
      // //Add session variables as global variables
      // //This allows us to use session variables in twig template
      // $twig->addGlobal('session', $_SESSION);

      // //Add isLoggedIn() from App\Auth class  as global variables
      // //This allows us to use this function in twig template
      // $twig->addGlobal('is_logged_in', Auth::isLoggedIn());
      //because we can use current_user to check if someone is logged in
      //global variable is_logged_in is redundant
      $twig->addGlobal('current_user', Auth::getUser());
      $twig->addGlobal('flash_messages', \App\Flash::getMessages());

      //Show or hide Home button: if at Home page
      if (empty($_SERVER['QUERY_STRING'])) {
        $twig->addGlobal('dontShowHome', true);
      }
    }

    return $twig->render($template, $args);
  }
}
