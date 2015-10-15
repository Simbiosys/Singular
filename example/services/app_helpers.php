<?php
  class AppHelpers {
    public static function get_helpers() {
      return array(
        // Compares one value to another
        'select_equals' => function ($args) {
          return $args[0] == $args[1] ? "selected" : "";
        },
        // Compares one language to default
        'idioma_habilitado' => function ($args) {
          return $args[0] == AppAuthentication::get_language() ? "display-block" : "display-none";
        }
      );
    }
  }
