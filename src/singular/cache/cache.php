<?php
  /**
  * Singular's Abstract Cache
  */
  namespace Singular;

  /**
  * Singular's Abstract Cache Class
  */
  abstract class Cache {
    /** @var string|null $table_name Table's name. */
    protected $table_name = NULL;

    /**
      * Constructor
      *
      * @param string $table_name Table's name.
      *
      * @return void
      */
    function __construct($table_name) {
      $this->table_name = $table_name;
    }

    /**
      * Returns a MD5 identifier based on a name.
      *
      * @param string $table_name Name to encode.
      *
      * @return string
      */
    protected function get_identifier($name) {
      return "c_" . hash('md5', $name);
    }

    /**
      * Clears the cache.
      *
      * @return void
      */
    abstract public function clear();

    /**
      * Returns a stored object by name.
      *
      * @param string $path Path to the object.
      *
      * @return Object|null
      */
    abstract public function get($path);

    /**
      * Stores an object in a path.
      *
      * @param string $path Path to the object.
      * @param string $data Object to store.
      *
      * @return void
      */
    abstract public function set($path, $data);
  }
