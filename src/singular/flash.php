<?php
  /**
  * Flash messages
  */
  namespace Singular;

  /**
  * Singular's Flash Messages
  */
  class Flash {
    /**
      * Returns the flash message stored in session.
      *
      * @return Object
      */
    public static function get_message() {
      $message = isset($_SESSION["flash_message"]) ? $_SESSION["flash_message"] : NULL;
      $_SESSION["flash_message"] = NULL;

      return $message;
    }

    /**
      * Stores in session the message to show.
      *
      * @param Object $message Flash message to show.
      *
      * @return void
      */
    public static function set_message($message) {
      $_SESSION["flash_message"] = $message;
    }
  }
