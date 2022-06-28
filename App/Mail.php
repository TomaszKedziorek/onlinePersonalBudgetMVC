<?php

namespace App;

//Mail
class Mail
{
  /**
   * Send a message
   * @param string $to Recipient
   * @param string $subject Subject
   * @param string $text Text-only content of the message
   * @param string $html HTML content of message
   **/
  //$to: do kogo, $subject:temat, $text: treść wiadomości
  //$html - treść wiadomości w html.
  // wysyłamy to samo w 2 formatach bo niektóre maile nie wspeierają html, oraz unikamy spamu
  public static function send($to, $subject, $text, $html)
  {
    $headers[] = 'From: ' . Config::EMAIL_ADDRESS;
    $headers[] = 'Content-type: text/html; charset=iso-8859-1';
    $success = mail($to, $subject, $text, implode("\r\n", $headers));

    return $success;
  }
}
