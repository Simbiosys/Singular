<?php
  /**
  * Singular's Controller
  */
  namespace Singular;

  /**
  * Singular's Controller Class
  */
  class Controller {
    /**
      * GET: requires authentication. Checks authorisation
      *
      * @param string $path URL path.
      * @param string $entity Entity's name to check authorisation.
      * @param string $action Action's name to check authorisation.
      * @param string $handler Handler to execute when the user visits this path.
      *
      * @return void
      */
    public static function get_private($path, $entity, $action, $handler) {
      $api = Configuration::get_api();
      $api->get($path, self::check_authentication_and_authorisation($entity, $action), $handler);
    }

    /**
      * PUBLIC GET: it doesn't check authentication
      *
      * @param string $path URL path.
      * @param string $handler Handler to execute when the user visits this path.
      *
      * @return void
      */
    public static function get_public($path, $handler) {
      $api = Configuration::get_api();
      $api->get($path, $handler);
    }

    /**
      * POST: requires authentication. Checks authorisation
      *
      * @param string $path URL path.
      * @param string $entity Entity's name to check authorisation.
      * @param string $action Action's name to check authorisation.
      * @param string $handler Handler to execute when the user visits this path.
      *
      * @return void
      */
    public static function post_private($path, $entity, $action, $handler) {
      $api = Configuration::get_api();
      $api->post($path, self::check_authentication_and_authorisation($entity, $action), $handler);
    }

    /**
      * PUBLIC POST: it doesn't check authentication
      *
      * @param string $path URL path.
      * @param string $handler Handler to execute when the user visits this path.
      *
      * @return void
      */
    public static function post_public($path, $handler) {
      $api = Configuration::get_api();
      $api->post($path, $handler);
    }

    /**
      * Redirects to a path
      *
      * @param string $path URL path.
      *
      * @return void
      */
    public static function redirect($path) {
      $index_path = Configuration::get_index();
      $api = Configuration::get_api();
      $api->redirect("$index_path$path");
    }

    /**
      * Sets a content type
      *
      * @param string $content_type Content type.
      *
      * @return void
      */
    public static function set_content_type($content_type) {
      $api = Configuration::get_api();
      $api->response->headers->set('Content-Type', $content_type);
    }

    /**
      * Groups request data by entity.
      *
      * @param string $default_model Default model.
      *
      * @return Array
      */
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

    /**
      * Groups request data. Prevents dot replacing of $_POST and $_GET.
      *
      * @param Object $source Source.
      *
      * @return Array
      */
    private static function get_input($source) {
      if ($source == 'POST') {
        parse_str(file_get_contents("php://input"), $data);
        // Cast it to an object
        return $data;
      }
      else {
        $pairs = explode("&", $_SERVER['QUERY_STRING']);

        $vars = array();

        foreach ($pairs as $pair) {
            $nv = explode("=", $pair);
            $name = urldecode($nv[0]);
            $value = urldecode($nv[1]);
            $vars[$name] = $value;
        }

        return $vars;
      }
    }

    /**
      * Returns POST data
      *
      * @return Object
      */
    public static function get_post() {
      return self::get_input('POST');
    }

    /**
      * Return GET data.
      *
      * @return Object
      */
    public static function get_get() {
      return self::get_input('GET');
    }

    /**
      * Returns a POST variable.
      *
      * @param string $name Variable's name.
      * @param Object|null $default Default value to assign.
      *
      * @return Object
      */
    public static function get_post_variable($name, $default = NULL) {
      $post = self::get_input('POST');

      return isset($post[$name]) ? $post[$name] : $default;
    }

    /**
      * Returns a GET variable.
      *
      * @param string $name Variable's name.
      * @param Object|null $default Default value to assign.
      *
      * @return Object
      */
    public static function get_get_variable($name, $default = NULL) {
      $get = self::get_input('GET');

      return isset($get[$name]) ? $get[$name] : $default;
    }

    /**
      * Sets a flash message.
      *
      * @param string $message Message to send to the view.
      *
      * @return void
      */
    public static function flash($message) {
      Flash::set_message($message);
    }

    /**
      * Sets a debug message.
      *
      * @param string $message Message to send to the view.
      *
      * @return void
      */
    public static function debug($message) {
      Debug::set_message($message);
    }

    /**
      * Checks if the user is logged in and has access to an action
      *
      * @param string $entity Entity to check authorisation.
      * @param string $action Action to check authorisation.
      *
      * @return void
      */
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
