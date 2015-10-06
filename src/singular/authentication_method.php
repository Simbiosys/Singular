<?php
  namespace Singular;

  abstract class AuthenticationMethod {
    public static function log_out() {
      session_unset();
    }

    public static function set_user($user) {
      $_SESSION["logged_user"] = $user;
    }

    public static function get_user() {
      return isset($_SESSION["logged_user"]) ? $_SESSION["logged_user"] : NULL;
    }

    public static function is_logged_in() {
      return isset($_SESSION["logged_user"]);
    }

    abstract public static function log_in($user, $password);

    abstract public static function get_log_in();
  }
