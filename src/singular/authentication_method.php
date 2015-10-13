<?php
  namespace Singular;

  abstract class AuthenticationMethod {
    protected static $identifier = NULL;

    public static function log_out() {
      session_unset();
    }

    public static function set_user($user, $data = NULL) {
      $identifier = self::get_identifier();
      $_SESSION[$identifier . "_logged_user"] = $user;
      $_SESSION[$identifier . "_logged_user_data"] = $data;
    }

    public static function set_user_data($data) {
      $identifier = self::get_identifier();
      $_SESSION[$identifier . "_logged_user_data"] = $data;
    }

    public static function get_user() {
      $identifier = self::get_identifier();
      $key = $identifier . "_logged_user";
      return isset($_SESSION[$key]) ? $_SESSION[$key] : NULL;
    }

    public static function get_user_data() {
      $identifier = self::get_identifier();
      $key = $identifier . "_logged_user_data";
      return isset($_SESSION[$key]) ? $_SESSION[$key] : NULL;
    }

    public static function is_logged_in() {
      $identifier = self::get_identifier();
      return isset($_SESSION[$identifier . "_logged_user"]);
    }

    public static function set_user_attribute($name, $value) {
      $data = self::get_user_data();
      $data[$name] = $value;
      self::set_user_data($data);
    }

    public static function get_user_attribute($name) {
      $data = self::get_user_data();

      return isset($data[$name]) ? $data[$name] : NULL;
    }

    public static function get_identifier() {
      return static::$identifier;
    }
    
    public static function set_language($language) {
      $identifier = self::get_identifier();
      $_SESSION[$identifier . "_language"] = $language;
    }
    
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

    abstract public static function log_in($user, $password);

    abstract public static function get_log_in();
  }
