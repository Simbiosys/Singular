<?php
  namespace Singular;

  class Router {
    public static function run() {
      $api = Configuration::obtener_api();

      self::load();

      // Run API
      $api->run();
    }

    private static function load() {
      $paths = self::get_paths();

      // Load application services
      Utils::load_files($paths["service_path"]);

      // Load application models
      Utils::load_files($paths["model_path"]);

      // Load application controllers
      Utils::load_files($paths["controller_path"]);
    }

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
