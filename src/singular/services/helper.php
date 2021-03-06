<?php
  /**
  * Singular's Helper
  */
  namespace Singular;

  /**
  * Singular's Helper Class
  */
  class Helper {
    /**
      * Returns the helpers that can be used in server views.
      *
      * @return Array
      */
    public static function get_helpers() {
      return array(
        // Compares one value to another
        'select_equals' => function ($args) {
          return $args[0] == $args[1] ? "selected" : "";
        }

      );
    }
  }
