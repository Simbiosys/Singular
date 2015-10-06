<?php
  namespace Singular;

  class FileCache extends \Singular\Cache {
    protected $root = NULL;

    function __construct($table_name) {
      parent::__construct($table_name);

      $this->root = Configuration::get_root() . "/cache/$table_name";

      // Create folder if not exists
      if (!file_exists($this->root)) {
        mkdir($this->root, 0777, true);
      }
    }

    public function clear() {
      $files = Utils::get_files($this->root, "json");

      foreach ($files as $file) {
        unlink($file);
      }
    }

    public function get($path) {
      $file_path = $this->get_full_path($path);

      if (file_exists($file_path))
        return json_decode(file_get_contents($file_path), TRUE);

      return NULL;
    }

    public function set($path, $data) {
      $file_path = $this->get_full_path($path);

      file_put_contents($file_path, json_encode($data));
    }

    private function get_full_path($path) {
      $root = $this->root;
      $identifier = $this->get_identifier($path);

      return "$root/$identifier.json";
    }
  }
