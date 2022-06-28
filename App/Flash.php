<?php

namespace App;

//Flash notification messages for one time display 
//using the session for storage between requests
class Flash
{
  //Success message type
  const SUCCESS = 'success';
  //Information message type
  const INFO = 'info';
  //Warning message type
  const WARNING = 'warning';
  //Add a message with type
  public static function addMessage($message, $type = 'success')
  {
    //Create array in the session if doesnt exist
    if (!isset($_SESSION['flash_notifications'])) {
      $_SESSION['flash_notifications'] = [];
    }
    //Append message to array
    $_SESSION['flash_notifications'][] = ['body' => $message, 'type' => $type];
  }

  //Get all the messages 
  public static function getMessages()
  {
    if (isset($_SESSION['flash_notifications'])) {
      //To show it only once return normal variable not session
      $messages = $_SESSION['flash_notifications'];
      unset($_SESSION['flash_notifications']);
      return $messages;
    }
  }
}
