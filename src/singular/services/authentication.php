<?php
  /**
  * Singular's Authentication
  */
  namespace Singular;

  /**
  * Singular's Authentication Class
  */
  class Authentication {
    /** @var Object|null $authentication_method Authentication method instance. */
    protected static $authentication_method = NULL;

    /**
      * Returns True if the user has logged in.
      *
      * @return boolean
      */
    public static function is_logged_in() {
      $authentication_method = self::load_authentication_method();
      return empty($authentication_method) ? TRUE : $authentication_method->is_logged_in();
    }

    /**
      * Returns the user from session.
      *
      * @return Object|null
      */
    public static function get_user() {
      $authentication_method = self::load_authentication_method();
      return empty($authentication_method) ? NULL : $authentication_method->get_user();
    }

    /**
      * Returns the user information from session.
      *
      * @return Object|null
      */
    public static function get_user_data() {
      $authentication_method = self::load_authentication_method();
      return empty($authentication_method) ? NULL : $authentication_method->get_user_data();
    }

    /**
      * Logs out.
      *
      * @return void
      */
    public static function log_out() {
      $authentication_method = self::load_authentication_method();

      if ($authentication_method) {
        $authentication_method->log_out();
      }
    }

    /**
      * Logs in.
      *
      * @param string $user User's account.
      * @param string $password User's password.
      *
      * @return void
      */
    public function log_in($user, $password) {
      $authentication_method = self::load_authentication_method();

      if ($authentication_method) {
        $authentication_method->log_in($user, $password);
      }
    }

    /**
      * Returns the authentication method 'log in' path.
      *
      * @return string
      */
    public function get_log_in() {
      $authentication_method = self::load_authentication_method();
      return empty($authentication_method) ? NULL : $authentication_method->get_log_in();
    }

    /**
      * Returns the authentication method language.
      *
      * @return string
      */
    public function get_language() {
      $authentication_method = self::load_authentication_method();
      return empty($authentication_method) ? NULL : $authentication_method->get_language();
    }

    /**
      * Loads the authentication method from configuration.
      *
      * @return Object
      */
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

    /**
      * Returns user id
      *
      * @return string
      */
    public static function get_user_id() {
      $authentication_method = self::load_authentication_method();
      return empty($authentication_method) ? NULL : $authentication_method->get_user_id();
    }

    /**
      * Returns user name
      *
      * @return string
      */
    public static function get_user_name() {
      $authentication_method = self::load_authentication_method();
      return empty($authentication_method) ? NULL : $authentication_method->get_user_name();
    }
  }
