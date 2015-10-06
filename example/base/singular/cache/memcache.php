<?php
  namespace Singular;

  class MemcacheCache extends \Singular\Cache {
    private $memcache = NULL;

    function __construct($table_name) {
      parent::__construct($table_name);

      $config = Configuration::get_configuration();
      $cache_server = $config["cache_server"];
      $cache_port = $config["cache_port"];

      $this->memcache = new \Memcache();
      $this->memcache->pconnect($cache_server, $cache_port);
    }

    public function clear() {
      $identifier = $this->get_identifier($this->table_name);
      $this->memcache->set($identifier, NULL, 0, 0);
    }

    public function get($path) {
      $identifier = $this->get_identifier($this->table_name);
      $info = $this->memcache->get($identifier);

      $path_identifier = $this->get_identifier($path);

      return isset($info[$path_identifier]) ? $info[$path_identifier] : NULL;
    }

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
