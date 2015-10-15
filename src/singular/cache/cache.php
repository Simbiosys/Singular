<?php
  namespace Singular;

  abstract class Cache {
    protected $table_name = NULL;

    function __construct($table_name) {
      $this->table_name = $table_name;
    }

    protected function get_identifier($name) {
      return "c_" . hash('md5', $name);
    }

    abstract public function clear();
    abstract public function get($path);
    abstract public function set($path, $data);
  }
