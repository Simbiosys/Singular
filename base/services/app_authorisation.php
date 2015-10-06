<?php
  class AppAuthorisation extends \Singular\AuthorisationMethod  {
    protected static $configuration_file = NULL;

    ////////////////////////////////////////////////////////////////////////////
    //        Check if the user is allowed to do an action in an entity
    ////////////////////////////////////////////////////////////////////////////
    public static function is_allowed($entity, $action) {
      $configuration = self::load_configuration();
      $rule = isset($configuration[$entity]) ? $configuration[$entity] : array();

      $rule_for_action = isset($rule[$action]) ? $rule[$action] : array();
      $user_role = self::get_user_role();

      $rule_for_role = isset($rule_for_action[$user_role]) ? $rule_for_action[$user_role] : TRUE;

      return $rule_for_role;
    }

    ////////////////////////////////////////////////////////////////////////////
    //                              Get user role
    ////////////////////////////////////////////////////////////////////////////
    private static function get_user_role() {
      $user = \Singular\Authentication::get_user();

      $role = "usuario";

      if (isset($user["role"])) {
        $role = $user["role"];
      }

      return $role;
    }

    ////////////////////////////////////////////////////////////////////////////
    //                      Load authorisation.json file
    ////////////////////////////////////////////////////////////////////////////
    private static function load_configuration() {
      if (empty(static::$configuration_file)) {
        $path = \getcwd();
        $configuration_file = "$path/config/authorisation.json";

        if (!file_exists($configuration_file)) {
          throw new \Exception("Please provide a authorisation.json file.");
        }

        static::$configuration_file = json_decode(file_get_contents($configuration_file), TRUE);
      }

      return static::$configuration_file;
    }
  }
