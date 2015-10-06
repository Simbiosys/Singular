<?php
  namespace Singular;

  class Debug {
    public static function get_message() {
      $message = isset($_SESSION["debug_message"]) ? $_SESSION["debug_message"] : NULL;
      $_SESSION["debug_message"] = NULL;

      return print_r($message, TRUE);
    }

    public static function set_message($message) {
      $_SESSION["debug_message"] = $message;
    }
  }
