<?php
  namespace Singular;

  class Authentication {
    protected static $authentication_method = NULL;

    public static function is_logged_in() {
      $authentication_method = self::load_authentication_method();
      return empty($authentication_method) ? TRUE : $authentication_method->is_logged_in();
    }

    public static function get_user() {
      $authentication_method = self::load_authentication_method();
      return empty($authentication_method) ? NULL : $authentication_method->get_user();
    }

    public static function get_user_data() {
      $authentication_method = self::load_authentication_method();
      return empty($authentication_method) ? NULL : $authentication_method->get_user_data();
    }

    public static function log_out() {
      $authentication_method = self::load_authentication_method();

      if ($authentication_method) {
        $authentication_method->log_out();
      }
    }

    public function log_in($user, $password) {
      $authentication_method = self::load_authentication_method();

      if ($authentication_method) {
        $authentication_method->log_in($user, $password);
      }
    }

    public function get_log_in() {
      $authentication_method = self::load_authentication_method();
      return empty($authentication_method) ? NULL : $authentication_method->get_log_in();
    }

    private static function load_authentication_method() {
      if (empty(static::$authentication_method)) {
        $authentication_method = Configuration::get_authentication();

        if ($authentication_method) {
          $r = new \ReflectionClass($authentication_method);
          static::$authentication_method = $r->newInstanceArgs(array());
        }
      }

      return static::$authentication_method;
    }
  }
