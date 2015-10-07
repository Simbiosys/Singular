<?php
  namespace Singular;

  abstract class AuthenticationMethod {
    public static function log_out() {
      session_unset();
    }

    public static function set_user($user, $data = NULL) {
      $_SESSION["logged_user"] = $user;
      $_SESSION["logged_user_data"] = $data;
    }

    public static function get_user() {
      return isset($_SESSION["logged_user"]) ? $_SESSION["logged_user"] : NULL;
    }
    
    public static function get_user_data() {
      return isset($_SESSION["logged_user_data"]) ? $_SESSION["logged_user_data"] : NULL;
    }

    public static function is_logged_in() {
      return isset($_SESSION["logged_user"]);
    }

    abstract public static function log_in($user, $password);

    abstract public static function get_log_in();
  }
