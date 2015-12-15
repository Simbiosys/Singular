<?php
  /**
  * Singular's Configuration
  */
  namespace Singular;

  /**
  * Singular's Configuration Class
  */
  class Configuration {
    /** @var Object|null $api Slim instance. */
    protected static $api = NULL;
    /** @var Object|null $configuration Singleton's instance for framework settings. */
    protected static $configuration = NULL;
    /** @var Object|null $app_settings Singleton's instance for application settings. */
    protected static $app_settings = NULL;

    /**
      * Returns Slim API instance.
      *
      * @return Object
      */
    public static function get_api() {
      if (empty(static::$api)) {
        static::$api = new \Slim\Slim();
      }

      return static::$api;
    }

    /**
      * Returns the framework configuration for the active mode.
      *
      * @return Object
      */
    public static function get_configuration() {
      return self::get_current_configuration();
    }

    /**
      * Returns the host.
      *
      * @return string
      */
    public static function get_index() {
      return self::get_host();
    }

    /**
      * Returns the app configuration.
      *
      * @param Object|null $properties Property chain to follow.
      * @param string|null $default Default value.
      *
      * @return Object
      */
    public static function get_app_settings($properties = NULL, $default = NULL) {
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
          return $default;
        }

        $value = $value[$property];
      }

      return $value;
    }

    /**
      * Returns the whole framework configuration.
      *
      * @return Object
      */
    private static function get_full_configuration() {
      if (empty(static::$configuration)) {
        static::$configuration = self::load_configuration();
      }

      return static::$configuration;
    }

    /**
      * Returns the framework configuration for the active mode.
      *
      * @return Object
      */
    private static function get_current_configuration() {
      if (empty(static::$configuration)) {
        static::$configuration = self::load_configuration();
      }

      return static::$configuration['current'];
    }

    /**
      * Returns True if debug is enabled in active mode.
      *
      * @return boolean
      */
    public static function debug_enabled() {
      $config = self::get_current_configuration();
      return isset($config['debug']) ? $config['debug'] : FALSE;
    }

    /**
      * Returns the root for active mode.
      *
      * @return string
      */
    public static function get_root() {
      $config = self::get_current_configuration();
      return $config['root'];
    }

    /**
      * Returns the host for active mode.
      *
      * @return string
      */
    public static function get_host() {
      $config = self::get_current_configuration();
      return $config['host'];
    }

    /**
      * Returns the path where app controllers are stored.
      *
      * @return string
      */
    public static function get_controller_path() {
      return self::get_configuration_path("controllers");
    }

    /**
      * Returns the path where app models are stored.
      *
      * @return string
      */
    public static function get_model_path() {
      return self::get_configuration_path("models");
    }

    /**
      * Returns the path where app services are stored.
      *
      * @return string
      */
    public static function get_service_path() {
      return self::get_configuration_path("services");
    }

    /**
      * Returns the path where app views are stored.
      *
      * @return string
      */
    public static function get_view_path() {
      return self::get_configuration_path("views");
    }

    /**
      * Returns the path where app language files are stored.
      *
      * @return string
      */
    public static function get_languages_path() {
      return self::get_configuration_path("languages", FALSE);
    }

    /**
      * Returns the list of available languages for the app.
      *
      * @return Array
      */
    public static function get_available_languages() {
      return self::get_configuration_element('available_languages', FALSE);
    }

    /**
      * Returns app default language.
      *
      * @return string
      */
    public static function get_default_language() {
      return self::get_configuration_element('default_language', FALSE);
    }

    /**
      * Returns the path where app layouts are stored.
      *
      * @return string
      */
    public static function get_layout_path() {
      return self::get_configuration_path("layouts");
    }

    /**
      * Returns the app authentication configuration.
      *
      * @return string
      */
    public static function get_authentication() {
      $configuration = self::get_current_configuration();
      return $configuration['authentication'];
    }

    /**
      * Returns the app authorisation configuration.
      *
      * @return string
      */
    public static function get_authorisation() {
      $configuration = self::get_current_configuration();
      return $configuration['authorisation'];
    }

    /**
      * Returns the app helper configuration.
      *
      * @return string
      */
    public static function get_helpers() {
      $configuration = self::get_current_configuration();
      return $configuration['helpers'];
    }

    /**
      * Returns the app database configuration.
      *
      * @return Object
      */
    public static function get_database_configuration() {
      $configuration = self::get_current_configuration();
      return $configuration['database'];
    }

    /**
      * Returns the app cache configuration.
      *
      * @return Object
      */
    public static function get_cache() {
      $configuration = self::get_current_configuration();
      return $configuration['cache'];
    }
    
    /**
      * Returns the app autogen configuration, TRUE if database tables are generated automatically based on the model fields.
      *
      * @return Object
      */
    public static function get_autogen() {
      $configuration = self::get_current_configuration();
      return isset($configuration['autogen']) ? $configuration['autogen'] : FALSE;
    }

    /**
      * Loads app settings.
      *
      * @return Object
      */
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

    /**
      * Loads and checks the framework configuration file.
      *
      * @return Object
      */
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

    /**
      * Returns a configuration path, prefixes the root path.
      *
      * @param string $element Configuration's key.
      * @param boolean $required Determines whether the key is mandatory.
      *
      * @return string|null
      */
    private static function get_configuration_path($element, $required = TRUE) {
      $root = self::get_root();
      $element_path = self::get_configuration_element($element, $required);

      if (empty($element_path)) {
        return NULL;
      }

      if (is_string($element_path)) {
        return "$root$element_path";
      }
      else {
        foreach ($element_path as $element => $value) {
          if (is_array($element_path[$element])) {
            $result = array();

            foreach ($element_path[$element] as $item) {
              array_push($result, "$root$item");
            }

            $element_path[$element] = $result;
          }
          else {
            $element_path[$element] = "$root$value";
          }
        }

        return $element_path;
      }
    }

    /**
      * Returns a configuration element.
      *
      * @param string $element Configuration's key.
      * @param boolean $required Determines whether the key is mandatory.
      *
      * @return Object|null
      */
    private static function get_configuration_element($element, $required = TRUE) {
      $configuration = self::get_full_configuration();

      if (!isset($configuration[$element])) {
      	if ($required) {
          throw new \Exception("singular.json has no attribute '$element'.");
        }
        else {
          return NULL;
        }
      }

      return $configuration["$element"];
    }
  }
