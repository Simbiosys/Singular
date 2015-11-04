<?php
  namespace Singular;

  class Controller {
    protected static $flash_message = NULL;

    ////////////////////////////////////////////////////////////////////////////
    //                     GET: requires authentication
    //                          Checks authorisation
    ////////////////////////////////////////////////////////////////////////////
    public static function get_private($path, $entity, $action, $handler) {
      $api = Configuration::get_api();
      $api->get($path, self::check_authentication_and_authorisation($entity, $action), $handler);
    }

    ////////////////////////////////////////////////////////////////////////////
    //               PUBLIC GET: it doesn't check authentication
    ////////////////////////////////////////////////////////////////////////////
    public static function get_public($path, $handler) {
      $api = Configuration::get_api();
      $api->get($path, $handler);
    }

    ////////////////////////////////////////////////////////////////////////////
    //                     POST: requires authentication
    //                          Checks authorisation
    ////////////////////////////////////////////////////////////////////////////
    public static function post_private($path, $entity, $action, $handler) {
      $api = Configuration::get_api();
      $api->post($path, self::check_authentication_and_authorisation($entity, $action), $handler);
    }

    ////////////////////////////////////////////////////////////////////////////
    //                PUBLIC POST: it doesn't check authentication
    ////////////////////////////////////////////////////////////////////////////
    public static function post_public($path, $handler) {
      $api = Configuration::get_api();
      $api->post($path, $handler);
    }

    ////////////////////////////////////////////////////////////////////////////
    //                                Redirect
    ////////////////////////////////////////////////////////////////////////////
    public static function redirect($path) {
      $index_path = Configuration::get_index();
      $api = Configuration::get_api();
      $api->redirect("$index_path$path");
    }

    ////////////////////////////////////////////////////////////////////////////
    //                         Get Data By Entity
    ////////////////////////////////////////////////////////////////////////////
    public static function get_data_by_entity($default_model) {
      $parameters = \Singular\Controller::get_post();

      $data = array();
      $data_translations = array();

      foreach ($parameters as $name => $value) {
        $parts = explode("#", $name);

        $name = $parts[0];
        $language = count($parts) > 1 ? $parts[1] : NULL;

        $parts = explode(".", $name);

        $model = NULL;

        if (count($parts) > 1) {
          $model = $parts[0];
          $name = $parts[1];
        }

        if (empty($model)) {
          $model = $default_model;
        }

        if (!empty($language)) {
          if (!isset($data_translations[$model])) {
            $data_translations[$model] = array();
          }

          if (!isset($data_translations[$model][$language])) {
            $data_translations[$model][$language] = array();
          }

          $data_translations[$model][$language][$name] = $value;
        }
        else {
          if (!isset($data[$model])) {
            $data[$model] = array();
          }

          $data[$model][$name] = $value;
        }
      }

      foreach ($data_translations as $model => $languages) {
        $data[$model] = array();

        foreach ($languages as $language => $items) {
          $items["language"] = $language;

          array_push($data[$model], $items);
        }
      }

      return $data;
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

    public static function get_post() {
      return self::get_input('POST');
    }

    public static function get_get() {
      return self::get_input('GET');
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
          $api = Configuration::get_api();
          $login = Authentication::get_log_in();

          $api->redirect($login);
        }

        // Redirect if the user has no permissions to access the path
        if (!Authorisation::is_allowed($entity, $action)) {
          $api = Configuration::get_api();
          $login = Authentication::get_log_in();

          $api->redirect($login);
        }
      };
    }
  }
