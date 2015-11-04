<?php
  /**
  * Singular's Memcache Cache
  */
  namespace Singular;

  /**
  * Singular's Memcache Cache Class
  */
  class MemcacheCache extends \Singular\Cache {
    /** @var Object|null $memcache Memcache instance. */
    private $memcache = NULL;

    /**
      * Constructor
      *
      * @param string $table_name Table's name.
      *
      * @return void
      */
    function __construct($table_name) {
      parent::__construct($table_name);

      $config = Configuration::get_configuration();
      $cache_server = $config["cache_server"];
      $cache_port = $config["cache_port"];

      $this->memcache = new \Memcache();
      $this->memcache->pconnect($cache_server, $cache_port);
    }

    /**
      * Clears the cache.
      *
      * @return void
      */
    public function clear() {
      $identifier = $this->get_identifier($this->table_name);
      $this->memcache->set($identifier, NULL, 0, 0);
    }

    /**
      * Returns a stored object by name.
      *
      * @param string $path Path to the object.
      *
      * @return Object|null
      */
    public function get($path) {
      $identifier = $this->get_identifier($this->table_name);
      $info = $this->memcache->get($identifier);

      $path_identifier = $this->get_identifier($path);

      return isset($info[$path_identifier]) ? $info[$path_identifier] : NULL;
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
      $identifier = $this->get_identifier($this->table_name);
      $info = $this->memcache->get($identifier);

      $path_identifier = $this->get_identifier($path);

      if (!$info)
        $info = array();

      $info[$path_identifier] = $data;

      $this->memcache->set($identifier, $info, 0, 0);
    }
  }
