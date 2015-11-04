<?php
  /**
  * Utils functions
  */
  namespace Singular;

  /**
  * Utils functions
  */
  class Utils {
    /**
      * Load all the files from a directory and include them with 'require'.
      *
      * @param string $path Directory to loop.
			* @param string $extension File extension to search.
      *
      * @return void
      */
    public static function load_files($path, $extension = "php") {
      self::process_files($path, $extension, function($file_path) {
        require_once($file_path);
      });
    }

    /**
      * Get all the files from a directory.
      *
      * @param string $path Directory to loop.
			* @param string $extension File extension to search.
      *
      * @return Array
      */
    public static function get_files($path, $extension = "php") {
      $files = array();

      self::process_files($path, $extension, function($file_path) use (&$files) {
        array_push($files, $file_path);
      });

      return $files;
    }

    /**
      * Process all the file from a directory.
      *
      * @param string $path Directory to loop.
			* @param string $extension_to_check File extension to search.
      * @param string $callback Handler to execute for each file.
      *
      * @return void
      */
    private static function process_files($path, $extension_to_check, $callback) {
      $directory = opendir($path);

      // First we process files, so we store directories to process them at the
      // end.
      $dir_list = array();
      $file_list = array();

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
            array_push($file_list, $file_path);
          }
        }
      }

      sort($file_list);

      foreach ($file_list as $file) {
      	$callback($file);
      }

      foreach ($dir_list as $dir) {
        self::process_files($dir, $extension_to_check, $callback);
      }
    }
  }
