<?php
  namespace Singular;

  class Controller {
    protected static $flash_message = NULL;

    ////////////////////////////////////////////////////////////////////////////
    //                     GET: requires authentication
    //                          Checks authorisation
    ////////////////////////////////////////////////////////////////////////////
    public static function get_private($path, $entity, $action, $handler) {
      $api = Configuration::obtener_api();
      $api->get($path, self::check_authentication_and_authorisation($entity, $action), $handler);
    }

    ////////////////////////////////////////////////////////////////////////////
    //               PUBLIC GET: it doesn't check authentication
    ////////////////////////////////////////////////////////////////////////////
    public static function get_public($path, $handler) {
      $api = Configuration::obtener_api();
      $api->get($path, $handler);
    }

    ////////////////////////////////////////////////////////////////////////////
    //                     POST: requires authentication
    //                          Checks authorisation
    ////////////////////////////////////////////////////////////////////////////
    public static function post_private($path, $entity, $action, $handler) {
      $api = Configuration::obtener_api();
      $api->post($path, self::check_authentication_and_authorisation($entity, $action), $handler);
    }

    ////////////////////////////////////////////////////////////////////////////
    //                PUBLIC POST: it doesn't check authentication
    ////////////////////////////////////////////////////////////////////////////
    public static function post_public($path, $handler) {
      $api = Configuration::obtener_api();
      $api->post($path, $handler);
    }

    ////////////////////////////////////////////////////////////////////////////
    //                                Redirect
    ////////////////////////////////////////////////////////////////////////////
    public static function redirect($path) {
      $index_path = Configuration::get_index();
      $api = Configuration::obtener_api();
      $api->redirect("$index_path$path");
    }

    ////////////////////////////////////////////////////////////////////////////
    //                           Get variables
    ////////////////////////////////////////////////////////////////////////////
    // Prevent dot replacing
    private static function get_input($source) {
      $pairs = explode("&", $source == 'POST' ? file_get_contents("php://input") : $_SERVER['QUERY_STRING']);

      $vars = array();

      foreach ($pairs as $pair) {
          $nv = explode("=", $pair);
          $name = urldecode($nv[0]);
          $value = urldecode($nv[1]);
          $vars[$name] = $value;
      }

      return $vars;
    }

    public static function get_post_variable($name, $default = NULL) {
      $post = self::get_input('POST');

      return isset($post[$name]) ? $post[$name] : $default;
    }

    public static function get_get_variable($name, $default = NULL) {
      $get = self::get_input('GET');

      return isset($get[$name]) ? $get[$name] : $default;
    }

    ////////////////////////////////////////////////////////////////////////////
    //                             Flash message
    ////////////////////////////////////////////////////////////////////////////
    public static function flash($message) {
      Flash::set_message($message);
    }

    ////////////////////////////////////////////////////////////////////////////
    //                             Debug message
    ////////////////////////////////////////////////////////////////////////////
    public static function debug($message) {
      Debug::set_message($message);
    }

    ////////////////////////////////////////////////////////////////////////////
    //                   Authentication and Authorisation
    ////////////////////////////////////////////////////////////////////////////
    private static function check_authentication_and_authorisation($entity, $action) {
      return function () use ($entity, $action) {
        // Redirect if no session started
        if (!Authentication::is_logged_in()) {
          $api = Configuration::obtener_api();
          $login = Authentication::get_log_in();

          $api->redirect($login);
        }

        // Redirect if the user has no permissions to access the path
        if (!Authorisation::is_allowed($entity, $action)) {
          $api = Configuration::obtener_api();
          $login = Authentication::get_log_in();

          $api->redirect($login);
        }
      };
    }
  }
