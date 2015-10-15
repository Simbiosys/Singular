<?php
  namespace Singular;

  class Helper {
    public static function get_helpers() {
      return array(
        // Compares one value to another
        'select_equals' => function ($args) {
          return $args[0] == $args[1] ? "selected" : "";
        }

      );
    }
  }
