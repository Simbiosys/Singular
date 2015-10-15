<?php
  namespace Singular;

  class Authorisation {
    protected static $authorisation_method = NULL;

    public static function is_allowed($entity, $action) {
      $authorisation_method = self::load_authorisation_method();

      return empty($authorisation_method) ? TRUE : $authorisation_method->is_allowed($entity, $action);
    }

    private static function load_authorisation_method() {
      if (empty(static::$authorisation_method)) {
        $authorisation_method = Configuration::get_authorisation();

        if ($authorisation_method) {
          $r = new \ReflectionClass($authorisation_method);
          static::$authorisation_method = $r->newInstanceArgs(array());
        }
      }

      return static::$authorisation_method;
    }
  }
