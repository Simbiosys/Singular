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

    abstract public static function log_in($user, $password);

    abstract public static function get_log_in();
  }
