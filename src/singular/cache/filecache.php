<?php
  /**
  * Singular's File Cache
  */
  namespace Singular;

  /**
  * Singular's File Cache Class
  */
  class FileCache extends \Singular\Cache {
    /** @var string|null $root Path root where files are stored. */
    protected $root = NULL;

    /**
      * Constructor
      *
      * @param string $table_name Table's name.
      *
      * @return void
      */
    function __construct($table_name) {
      parent::__construct($table_name);

      $this->root = Configuration::get_root() . "/cache/$table_name";

      // Create folder if not exists
      if (!file_exists($this->root)) {
        mkdir($this->root, 0777, true);
      }
    }

    /**
      * Clears the cache.
      *
      * @return void
      */
    public function clear() {
      $files = Utils::get_files($this->root, "json");

      foreach ($files as $file) {
        unlink($file);
      }
    }

    /**
      * Returns a stored object by name.
      *
      * @param string $path Path to the object.
      *
      * @return Object|null
      */
    public function get($path) {
      $file_path = $this->get_full_path($path);

      if (file_exists($file_path))
        return json_decode(file_get_contents($file_path), TRUE);

      return NULL;
    }

    /**
      * Stores an object in a path.
      *
      * @param string $path Path to the object.
      * @param string $data Object to store.
      *
      * @return void
      */
    public function set($path, $data) {
      $file_path = $this->get_full_path($path);

      file_put_contents($file_path, json_encode($data));
    }

    /**
      * Gets the full path for a partial path.
      *
      * @param string $path Partial path.
      *
      * @return string
      */
    private function get_full_path($path) {
      $root = $this->root;
      $identifier = $this->get_identifier($path);

      return "$root/$identifier.json";
    }
  }
