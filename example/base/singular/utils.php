<?php
  namespace Singular;

  class Utils {
    ////////////////////////////////////////////////////////////////////////////
    //                              LOAD FILES
    // Load all the PHP files from a directory and include them with 'require'
    ////////////////////////////////////////////////////////////////////////////

    public static function load_files($path, $extension = "php") {
      self::process_files($path, $extension, function($file_path) {
        require_once($file_path);
      });
    }

    public static function get_files($path, $extension = "php") {
      $files = array();

      self::process_files($path, $extension, function($file_path) use (&$files) {
        array_push($files, $file_path);
      });

      return $files;
    }

    private static function process_files($path, $extension_to_check, $callback) {
      $directory = opendir($path);

      // First we process files, so we store directories to process them at the
      // end.
      $dir_list = array();

      while (false !== ($file_name = readdir($directory))) {
        if ($file_name === "." || $file_name == "..")
          continue;

        $file_path = "$path/$file_name";
        $is_directory = is_dir($file_path);

        if ($is_directory) {
          array_push($dir_list, $file_path);
        }
        else {
          $extension = pathinfo($file_name, PATHINFO_EXTENSION);

          if ($extension == $extension_to_check) {
            $callback($file_path);
          }
        }
      }

      foreach ($dir_list as $dir) {
        self::process_files($dir, $extension_to_check, $callback);
      }
    }
  }
