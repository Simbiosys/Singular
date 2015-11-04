<?php
  /**
  * Debug messages
  */
  namespace Singular;

  /**
  * Singular's Debug Messages
  */
  class Debug {
    /**
      * Returns the flash message stored in session.
      *
      * @return Object
      */
    public static function get_message() {
      $message = isset($_SESSION["debug_message"]) ? $_SESSION["debug_message"] : NULL;
      $_SESSION["debug_message"] = NULL;

      return print_r($message, TRUE);
    }

    /**
      * Stores in session the message to show.
      *
      * @param Object $message Flash message to show.
      *
      * @return void
      */
    public static function set_message($message) {
      $_SESSION["debug_message"] = $message;
    }
  }
