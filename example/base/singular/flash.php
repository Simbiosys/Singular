<?php
  namespace Singular;

  class Flash {
    public static function get_message() {
      $message = isset($_SESSION["flash_message"]) ? $_SESSION["flash_message"] : NULL;
      $_SESSION["flash_message"] = NULL;

      return $message;
    }

    public static function set_message($message) {
      $_SESSION["flash_message"] = $message;
    }
  }
