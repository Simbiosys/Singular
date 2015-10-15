<?php
  namespace Singular;

  class Model {
    protected static $data_base;
    protected static $table;
    protected static $query;
    protected static $filter = NULL;
    protected static $order;

    protected static $fields;
    protected static $primary_key = NULL;
    protected static $dependencies = NULL;

    protected static $cache = NULL;

    function __construct($values = NULL) {
      self::get_connection();
      self::get_cache();

      if (!empty($values)) {
        $this->set($values);
      }
    }

    protected $attributes = array(

    );

    public function set($values) {
      foreach ($values as $key => $value) {
        $this->attributes[$key] = $value;
      }
    }

    public function set_attribute($name, $value) {
      $this->attributes[$name] = $value;
    }

    public function get_attribute($name) {
      return isset($this->attributes[$name]) ? $this->attributes[$name] : NULL;
    }

    public static function get_table() {
      return static::$table;
    }

    ////////////////////////////////////////////////////////////////////////////
    //                          DATABASE CONNECTION
    ////////////////////////////////////////////////////////////////////////////

    protected static function get_connection() {
      $configuration = Configuration::get_database_configuration();
      self::$data_base = Database::get_connection($configuration["provider"], $configuration["server"], $configuration["user"], $configuration["password"], $configuration["data_base"]);
    }

    ////////////////////////////////////////////////////////////////////////////
    //                                CACHE
    ////////////////////////////////////////////////////////////////////////////

    protected static function get_cache() {
      if (empty(static::$cache)) {
        $cache = Configuration::get_cache();

        if ($cache) {
          $r = new \ReflectionClass($cache);
          static::$cache = $r->newInstanceArgs(array(static::$table));
        }
      }

      return static::$cache;
    }

    ////////////////////////////////////////////////////////////////////////////
    //                        QUERY AUXILIARY METHODS
    ////////////////////////////////////////////////////////////////////////////

    protected function init() {
      // Lets you perform initial tasks
      static::$filter = self::$data_base->get_default_filter();
    }

    protected static function get_query() {
      self::auto_generation();

      $query = static::$query;
      $table = static::$table;
      $filter = static::$filter;

      return self::$data_base->get_query($query, $table, $filter);
    }

    protected static function get_filter() {
      $filter = static::$filter;

      return self::$data_base->get_filter($filter);
    }

    protected static function get_order() {
      $order = static::$order;

      return self::$data_base->get_order($order);
    }

    protected static function wrap_data(&$data, $model, $info) {
      $data[$model] = $info;

      return $data;
    }

    protected static function set_dependencies($id, &$data, $cache_identifier) {
      $dependencies = static::$dependencies;

      if (empty($dependencies))
        return $data;

      foreach ($dependencies as $dependency) {
        $entity = isset($dependency["entity"]) ? $dependency["entity"] : NULL;

        $filter = isset($dependency["filter"]) ? $dependency["filter"] : NULL;
        $order = isset($dependency["order"]) ? $dependency["order"] : NULL;
        $dependent = isset($dependency["dependent"]) ? $dependency["dependent"] : FALSE;

        $table = self::get_table_by_entity($entity);
        $key = self::get_dependency_key($table);

        $condition = "$key = '$id'";
        $query = self::$data_base->get_query_by_condition(NULL, $table, $filter, $order, $condition);

        $dependency_cache_identifier = $cache_identifier . "_" . $table . "_" . $key . "_" . $filter;
        $results = self::process_query_results($table, $query, NULL, $dependency_cache_identifier);

        $data[$table] = $results;
      }

      return $data;
    }

    private static function get_table_by_entity($entity) {
      return call_user_func(array($entity, 'get_table'));
    }

    private static function get_cached_data($identifier) {
      $cache = self::get_cache();

      if ($cache) {
        $cached_data = $cache->get($cache_identifier);

        if ($cached_data)
          return $cached_data;
      }

      return NULL;
    }

    private static function process_query_results($table, $query, $params, $cache_identifier) {
      $results = self::$data_base->run($query, NULL, $params);

      $class = get_called_class();
      $obj = new $class();
      $obj->init();

      $objs = NULL;

      if ($results) {
        for ($i = 0; $i < sizeof($results); $i++) {
          $fields = self::$data_base->format_fields($results[$i], static::$fields);
          $id = isset($fields["id"]) ? $fields["id"] : NULL;

          $data = array();
          self::wrap_data($data, $table, $obj->process($fields));
          self::set_dependencies($id, $data, $cache_identifier);

          $objs[] = $data;
        }
      }

      $cache = self::get_cache();

      if ($cache) {
        $cache->set($cache_identifier, $objs);
      }

      return $objs;
    }

    private static function filter_by_entity($entities) {
      $filtered_data = array();

      foreach ($entities as $entity => $rows) {
        if (count($rows) == 0) {
          array_push($rows, array());
        }

        foreach ($rows as $row) {
          $filtered = array();
          $id = NULL;

          foreach ($row as $key => $value) {
            if ($value !== NULL && $value !== '' && $key !== 'id') {
              if ($value === false) {
                $value = 0;
              }

              $filtered[$key] = $value;
            }
            else if ($key === 'id') {
              $id = $value;
            }
          }

          if (!self::is_dependency($entity) || $id !== NULL || count($filtered) > 0) {
            array_push($filtered_data, array(
              "entity" => $entity,
              "id" => $id,
              "filtered" => $filtered
            ));
          }
        }
      }

      return $filtered_data;
    }

    private static function is_dependency($table) {
      $dependencies = static::$dependencies;

      return isset($dependencies[$table]);
    }

    private static function get_dependency_key($table) {
      $dependencies = static::$dependencies;
      $dependency = isset($dependencies[$table]) ? $dependencies[$table] : array();

      return isset($dependency["key"]) ? $dependency["key"] : $table . "_id";
    }

    ////////////////////////////////////////////////////////////////////////////
    //                             QUERY METHODS
    ////////////////////////////////////////////////////////////////////////////

    public static function find($id) {
      $results = self::find_all_by_value('id', $id);

      return count($results) > 0 ? $results[0] : NULL;
    }

    public static function find_all_by_value($field, $value) {
      $cache_identifier = "$field_$value";
      $cached = self::get_cached_data($cache_identifier);

      if ($cached) {
        return $cached;
      }

      self::get_connection();

      $query = self::$data_base->get_query_by_value(static::$query, static::$table, static::$filter, static::$order, $field, $value);

      return self::process_query_results(static::$table, $query, array($value), $cache_identifier);
    }

    public static function get_all($condition = NULL) {
      $cache_identifier = "condition_$condition";
      $cached = self::get_cached_data($cache_identifier);

      if ($cached) {
        return $cached;
      }

      self::get_connection();

      $query = self::$data_base->get_query_by_condition(static::$query, static::$table, static::$filter, static::$order, $condition);

      return self::process_query_results(static::$table, $query, NULL, $cache_identifier);
    }

    public function save() {
      self::auto_generation();
      self::get_connection();

      $values = $this->attributes;
      $filtered_data = self::filter_by_entity($values);

      $table = static::$table;
      //$main_entity_id = isset($filtered_data[$table]["id"]) ? $filtered_data[$table]["id"] : NULL;
      $main_entity_id = self::get_attribute("id");

      // Sort entities, this model must be first to generate ID in INSERT
      // to have it for following INSERTs

      usort($filtered_data, function($a, $b) use ($table) {
        $a_entity = $a["entity"];
        $b_entity = $b["entity"];

        if ($a_entity == $table) {
          return -1;
        }

        if ($b_entity == $table) {
          return 1;
        }

        return 0;
      });

      for ($i = 0; $i < count($filtered_data); $i++) {
        $info = $filtered_data[$i];

        $entity = $info["entity"];
        $filtered = $info["filtered"];
        $id = $info["id"];

        // Set foreign key in dependent entities
        if (self::is_dependency($entity)) {
          $key = self::get_dependency_key($entity);

          if (!isset($filtered[$key])) {
            $filtered[$key] = $main_entity_id;
          }
        }

        $columns = array_keys($filtered);

        if ($id) {
          $query = self::$data_base->get_update($entity, $columns, $id);
        }
        else {
          $query = self::$data_base->get_insert($entity, $columns, $params);
        }

        $result = self::$data_base->run($query, NULL, $filtered);

        if ($result) {
          $new_id = self::$data_base->get_id();
          $result = array('error' => FALSE, 'message' => $new_id);

          if (empty($main_entity_id) && !self::is_dependency($entity)) {
            $main_entity_id = $new_id;
          }

          // Cache invalidation
          $cache = self::get_cache();

          if ($cache) {
            $cache->clear();
          }
        } else {
          return array('error' => TRUE, 'message' => self::$data_base->get_error());
        }
      }

      return $result;
    }

    public static function update($id, $values) {
      $table = static::$table;

      if (!isset($values[$table])) {
        $values[$table] = array();
      }

      $values[$table]["id"] = $id;

      $class = get_called_class();
      $obj = new $class($values);
      $obj->init();

      $obj->save();
    }

    public static function create($values) {
      $class = get_called_class();
      $obj = new $class($values);
      $obj->init();

      $obj->save();
    }

    public static function delete($id) {
      self::get_connection();
      $query = self::$data_base->get_delete(static::$table, $id);

      $result = self::$data_base->run($query, NULL, NULL);

      // Cache invalidation
      $cache = self::get_cache();

      if ($cache) {
        $cache->clear();
      }
    }

    ////////////////////////////////////////////////////////////////////////////
    //                             DATA PROCESSING
    ////////////////////////////////////////////////////////////////////////////

    protected function process($data) {
      return $data;
    }

    ////////////////////////////////////////////////////////////////////////////
    //                             AUTO GENERATION
    ////////////////////////////////////////////////////////////////////////////

    protected static function auto_generation() {
      self::check_table();
      self::check_fields();

      // Check table and fields for dependencies
      $dependencies = static::$dependencies;

      foreach ($dependencies as $key => $info) {
        $entity = isset($info["entity"]) ? $info["entity"] : NULL;

        if (empty($entity)) {
          continue;
        }

        call_user_func(array($entity, 'check_table'));
        call_user_func(array($entity, 'check_fields'));
      }
    }

    protected static function check_table() {
      $table = static::$table;
      $fields = static::$fields;
      $primary_key = static::$primary_key;

      self::$data_base->check_table($table, $fields, $primary_key);
    }

    protected static function check_fields() {
      $table = static::$table;
      $fields = static::$fields;
      $primary_key = static::$primary_key;

      // Deleted
      if (!isset($fields["deleted"])) {
        $fields["deleted"] = array(
          "type" => "boolean",
          "null" => FALSE,
          "default" => "0"
        );
      }

      self::$data_base->check_fields($table, $fields, $primary_key);
    }
  }
