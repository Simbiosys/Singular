<?php
  namespace Singular;

  class Configuration {
    protected static $api = NULL;
    protected static $configuration = NULL;
    protected static $app_settings = NULL;

    ////////////////////////////////////////////////////////////////////////////
    //                            Get API instance
    ////////////////////////////////////////////////////////////////////////////
    public static function obtener_api() {
      if (empty(static::$api)) {
        static::$api = new \Slim\Slim();
      }

      return static::$api;
    }

    ////////////////////////////////////////////////////////////////////////////
    //                            Get configuration
    ////////////////////////////////////////////////////////////////////////////
    public static function get_configuration() {
      return self::get_current_configuration();
    }

    ////////////////////////////////////////////////////////////////////////////
    //                                 Get index
    ////////////////////////////////////////////////////////////////////////////
    public static function get_index() {
      //$index = self::get_configuration_element("index");
      $host = self::get_host();

      //return "$host$index";
      return $host;
    }

    ////////////////////////////////////////////////////////////////////////////
    //                            Get app settings
    ////////////////////////////////////////////////////////////////////////////
    public static function get_app_settings($properties = NULL) {
      if (empty(static::$app_settings)) {
        static::$app_settings = self::load_app_settings();
      }

      $settings = static::$app_settings;

      if (empty($properties) || empty($settings)) {
        return $settings;
      }

      if (!is_array($properties)) {
        $properties = array($properties);
      }

      $value = $settings;

      for ($i = 0; $i < count($properties); $i++) {
        $property = $properties[$i];

        if (!isset($value[$property])) {
          return NULL;
        }

        $value = $value[$property];
      }

      return $value;
    }

    ////////////////////////////////////////////////////////////////////////////
    //                          Get full configuration
    ////////////////////////////////////////////////////////////////////////////
    private static function get_full_configuration() {
      if (empty(static::$configuration)) {
        static::$configuration = self::load_configuration();
      }

      return static::$configuration;
    }

    ////////////////////////////////////////////////////////////////////////////
    //                        Get current configuration
    ////////////////////////////////////////////////////////////////////////////
    private static function get_current_configuration() {
      if (empty(static::$configuration)) {
        static::$configuration = self::load_configuration();
      }

      return static::$configuration['current'];
    }

    ////////////////////////////////////////////////////////////////////////////
    //                             Debug enabled
    ////////////////////////////////////////////////////////////////////////////
    public static function debug_enabled() {
      $config = self::get_current_configuration();
      return isset($config['debug']) ? $config['debug'] : FALSE;
    }

    ////////////////////////////////////////////////////////////////////////////
    //                             Get root path
    ////////////////////////////////////////////////////////////////////////////
    public static function get_root() {
      $config = self::get_current_configuration();
      return $config['root'];
    }

    ////////////////////////////////////////////////////////////////////////////
    //                                  Get host
    ////////////////////////////////////////////////////////////////////////////
    public static function get_host() {
      $config = self::get_current_configuration();
      return $config['host'];
    }

    ////////////////////////////////////////////////////////////////////////////
    //                          Get controller path
    ////////////////////////////////////////////////////////////////////////////
    public static function get_controller_path() {
      return self::get_configuration_path("controllers");
    }

    ////////////////////////////////////////////////////////////////////////////
    //                             Get model path
    ////////////////////////////////////////////////////////////////////////////
    public static function get_model_path() {
      return self::get_configuration_path("models");
    }

    ////////////////////////////////////////////////////////////////////////////
    //                            Get service path
    ////////////////////////////////////////////////////////////////////////////
    public static function get_service_path() {
      return self::get_configuration_path("services");
    }

    ////////////////////////////////////////////////////////////////////////////
    //                             Get view path
    ////////////////////////////////////////////////////////////////////////////
    public static function get_view_path() {
      return self::get_configuration_path("views");
    }

    ////////////////////////////////////////////////////////////////////////////
    //                           Get authentication
    ////////////////////////////////////////////////////////////////////////////
    public static function get_authentication() {
      $configuration = self::get_current_configuration();
      return $configuration['authentication'];
    }

    ////////////////////////////////////////////////////////////////////////////
    //                           Get authorisation
    ////////////////////////////////////////////////////////////////////////////
    public static function get_authorisation() {
      $configuration = self::get_current_configuration();
      return $configuration['authorisation'];
    }

    ////////////////////////////////////////////////////////////////////////////
    //                             Get dababase
    ////////////////////////////////////////////////////////////////////////////
    public static function get_database_configuration() {
      $configuration = self::get_current_configuration();
      return $configuration['database'];
    }

    ////////////////////////////////////////////////////////////////////////////
    //                              Get cache
    ////////////////////////////////////////////////////////////////////////////
    public static function get_cache() {
      $configuration = self::get_current_configuration();
      return $configuration['cache'];
    }

    ////////////////////////////////////////////////////////////////////////////
    //                           Load App settings
    ////////////////////////////////////////////////////////////////////////////
    private static function load_app_settings() {
      $configuration = self::get_full_configuration();
      $settings_path = $configuration['settings'];
      $root = self::get_root();

      if (!$settings_path)
        return NULL;

      $settings_path = "$root$settings_path";

      if (!file_exists($settings_path))
        return NULL;

      return json_decode(file_get_contents($settings_path), TRUE);
    }

    ////////////////////////////////////////////////////////////////////////////
    //                           Load configuration
    ////////////////////////////////////////////////////////////////////////////
    private static function load_configuration() {
      $path = \getcwd();
      $configuration_file = "$path/singular.json";

      if (!file_exists($configuration_file)) {
        throw new \Exception("Please provide a singular.json file.");
      }

      $configuration_file = json_decode(file_get_contents($configuration_file), TRUE);

      if (!$configuration_file) {
        throw new \Exception("singular.json contains errors.");
      }

      if (!isset($configuration_file["mode"])) {
        throw new \Exception("singular.json has no attribute 'mode'.");
      }

      $mode = $configuration_file["mode"];

      if (!isset($configuration_file["modes"])) {
        throw new \Exception("singular.json has no attribute 'modes'.");
      }

      $modes = $configuration_file["modes"];

      if (!isset($configuration_file["modes"][$mode])) {
        throw new \Exception("singular.json has no attribute 'modes[$mode]'.");
      }

      $current_mode = $configuration_file["modes"][$mode];
      $configuration_file['current'] = $current_mode;

      return $configuration_file;
    }

    ////////////////////////////////////////////////////////////////////////////
    //                        Load configuration path
    ////////////////////////////////////////////////////////////////////////////
    private static function get_configuration_path($element) {
      $root = self::get_root();
      $element_path = self::get_configuration_element($element);

      if (is_string($element_path)) {
        return "$root$element_path";
      }
      else {
        foreach ($element_path as $element => $value) {
          $element_path[$element] = "$root$value";
        }

        return $element_path;
      }
    }

    ////////////////////////////////////////////////////////////////////////////
    //                        Load configuration element
    ////////////////////////////////////////////////////////////////////////////
    private static function get_configuration_element($element) {
      $configuration = self::get_full_configuration();

      if (!isset($configuration[$element])) {
        throw new \Exception("singular.json has no attribute '$element'.");
      }

      return $configuration["$element"];
    }
  }
