<?php
  /**
  * Singular's Abstract Authentication Method
  */
  namespace Singular;

  /**
  * Singular's Abstract Authentication Method Class
  */
  abstract class AuthenticationMethod {
    /** @var string|null $identifier Application's identifier, prevents several applications in the same server share the session. */
    protected static $identifier = NULL;

    /**
      * Logs out.
      *
      * @return void
      */
    public static function log_out() {
      session_unset();
    }

    /**
      * Stores user's information in session.
      *
      * @param string $user User's account.
      * @param Object|null $data User's data to store.
      *
      * @return void
      */
    public static function set_user($user, $data = NULL) {
      $identifier = self::get_identifier();
      $_SESSION[$identifier . "_logged_user"] = $user;
      $_SESSION[$identifier . "_logged_user_data"] = $data;
    }

    /**
      * Stores user's information in session.
      *
      * @param Object $data User's data to store.
      *
      * @return void
      */
    public static function set_user_data($data) {
      $identifier = self::get_identifier();
      $_SESSION[$identifier . "_logged_user_data"] = $data;
    }

    /**
      * Gets the session-stored user.
      *
      * @return Object
      */
    public static function get_user() {
      $identifier = self::get_identifier();
      $key = $identifier . "_logged_user";
      return isset($_SESSION[$key]) ? $_SESSION[$key] : NULL;
    }

    /**
      * Gets the session-stored user's data.
      *
      * @return Object
      */
    public static function get_user_data() {
      $identifier = self::get_identifier();
      $key = $identifier . "_logged_user_data";
      return isset($_SESSION[$key]) ? $_SESSION[$key] : NULL;
    }

    /**
      * Return True if the user has logged in.
      *
      * @return boolean
      */
    public static function is_logged_in() {
      $identifier = self::get_identifier();
      return isset($_SESSION[$identifier . "_logged_user"]);
    }

    /**
      * Sets a user's attribute.
      *
      * @param string $name Attribute's name.
      * @param Object $value Attribute's value.
      *
      * @return void
      */
    public static function set_user_attribute($name, $value) {
      $data = self::get_user_data();
      $data[$name] = $value;
      self::set_user_data($data);
    }

    /**
      * Gets a user's attribute.
      *
      * @param string $name Attribute's name.
      *
      * @return void
      */
    public static function get_user_attribute($name) {
      $data = self::get_user_data();

      return isset($data[$name]) ? $data[$name] : NULL;
    }

    /**
      * Gets the identifier associated to the application.
      *
      * @return string
      */
    public static function get_identifier() {
      return static::$identifier;
    }

    /**
      * Sets the language.
      *
      * @param string $language Language to set.
      *
      * @return void
      */
    public static function set_language($language) {
      $identifier = self::get_identifier();
      $_SESSION[$identifier . "_language"] = $language;
    }

    /**
      * Gets the selected language.
      *
      * @return string
      */
    public static function get_language() {
      $available_languages = Configuration::get_available_languages();
      $default_language = Configuration::get_default_language();

      if (empty($available_languages))
      	return NULL;

      $identifier = self::get_identifier();
      $selected_language = isset($_SESSION[$identifier . "_language"]) ?
      						$_SESSION[$identifier . "_language"] : NULL;

      if ($selected_language && in_array($selected_language, $available_languages)) {
      	return $selected_language;
      }

      $request_language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

      if ($request_language && in_array($request_language, $available_languages)) {
        self::set_language($request_language);
        return $request_language;
      }

      return $default_language;
    }

    /**
      * Gets user id.
      *
      * @return string
      */
    public static function get_user_id() {
      return NULL;
    }

    /**
      * Gets user name.
      *
      * @return string
      */
    public static function get_user_name() {
      return NULL;
    }

    /**
      * Logs in.
      *
      * @param string $user User's account.
      * @param string $password User's password.
      *
      * @return void
      */
    abstract public static function log_in($user, $password);

    /**
      * Returns the path to the 'log in'.
      *
      * @return string
      */
    abstract public static function get_log_in();
  }
