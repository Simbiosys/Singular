<?php
  /**
  * Router
  */
  namespace Singular;

  /**
  * Singular's router
  */
  class Router {
    /**
      * Starting point for the router.
      *
      * @return void
      */
    public static function run() {
      $api = Configuration::get_api();

      self::load();

      // Run API
      $api->run();
    }

    /**
      * Loads application's services, models and controllers.
      *
      * @return void
      */
    private static function load() {
      $paths = self::get_paths();

      // Loads application services
      Utils::load_files($paths["service_path"]);

      // Loads application models
      Utils::load_files($paths["model_path"]);

      // Loads application controllers
      Utils::load_files($paths["controller_path"]);
    }

    /**
      * Gets application's paths for controllers, models and services
      *
      * @return void
      */
    private static function get_paths() {
      $root = Configuration::get_root();
      $controller_path = Configuration::get_controller_path();
      $model_path = Configuration::get_model_path();
      $service_path = Configuration::get_service_path();

      return array(
        "controller_path" => $controller_path,
        "model_path" => $model_path,
        "service_path" => $service_path
      );
    }
  }
