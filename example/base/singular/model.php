<?php
  namespace Singular;

  class Model {
    private static $data_base;
    protected static $table;
    protected static $sql_query;
    protected static $order;

    protected static $fields;
    protected static $primary_key = NULL;

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

    ////////////////////////////////////////////////////////////////////////////
    //                          DATABASE CONNECTION
    ////////////////////////////////////////////////////////////////////////////

    private static function get_connection() {
      $configuration = Configuration::get_database_configuration();
      self::$data_base = Database::get_connection($configuration["provider"], $configuration["server"], $configuration["user"], $configuration["password"], $configuration["data_base"]);
    }

    ////////////////////////////////////////////////////////////////////////////
    //                                CACHE
    ////////////////////////////////////////////////////////////////////////////

    private static function get_cache() {
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

    private static function get_query() {
      self::auto_generation();

      $sql_query = static::$sql_query;

      if (!isset($sql_query)) {
        $sql_query = "SELECT * FROM " . static::$table;
      }

      if (strpos($sql_query, "WHERE") === false) {
          $sql_query .= " WHERE deleted = 0";
      }

      return $sql_query;
    }

    private static function get_order() {
      $order = static::$order;

      if (!isset($order)) {
        return "";
      }

      return " ORDER BY $order";
    }

    ////////////////////////////////////////////////////////////////////////////
    //                             QUERY METHODS
    ////////////////////////////////////////////////////////////////////////////

    public static function find($id) {
      $results = self::find_all_by_value('id', $id);

      return $results[0];
    }

    public static function find_all_by_value($field, $value) {
      $cache = self::get_cache();
      $cache_identifier = "$field_$value";

      if ($cache) {
        $cached_data = $cache->get($cache_identifier);

        if ($cached_data)
          return $cached_data;
      }

      $class = get_called_class();

      $objs = NULL;
      self::get_connection();

      $sql_query = self::get_query();
      $sql_query .= " AND " . $field . " = ?";
      $sql_query .= self::get_order();

      $results = self::$data_base->run($sql_query, NULL, array($value));

      if ($results) {
        for ($i = 0; $i < sizeof($results); $i++) {
          $fields = self::$data_base->format_fields($results[$i], static::$fields);
          $obj = new $class();
          $objs[] = $obj->process($fields);
        }
      }

      if ($cache) {
        $cache->set($cache_identifier, $objs);
      }

      return $objs;
    }

    public static function get_all($condition = NULL) {
      $cache = self::get_cache();
      $cache_identifier = "condition_$condition";

      if ($cache) {
        $cached_data = $cache->get($cache_identifier);

        if ($cached_data)
          return $cached_data;
      }

      $class = get_called_class();

      $objs = NULL;
      self::get_connection();

      $sql_query = self::get_query();

      if ($condition) {
        $sql_query .= " AND $condition ";
      }

      $sql_query .= self::get_order();

      $results = self::$data_base->run($sql_query, NULL, NULL);

      if ($results) {
          foreach ($results as $index => $data) {
            $fields = self::$data_base->format_fields($data, static::$fields);
            $obj = new $class();
            $objs[] = $obj->process($fields);
          }
      }

      if ($cache) {
        $cache->set($cache_identifier, $objs);
      }

      return $objs;
    }

    public function save() {
      self::auto_generation();
      self::get_connection();

      $values = $this->attributes;
      $filtered = NULL;

      foreach ($values as $key => $value) {
          if ($value !== NULL && $value !== '' && $key !== 'id') {
              if ($value === false) {
                  $value = 0;
              }

              $filtered[$key] = $value;
          }
      }

      $columns = array_keys($filtered);
      $id = $this->get_attribute("id");

      if ($id) {
          $columns = join(" = ?, ", $columns);
          $columns .= ' = ?';
          $sql_query = "UPDATE " . static ::$table . " SET $columns WHERE id = " . $id;
      } else {
          $params = join(", ", array_fill(0, count($columns), "?"));
          $columns = join(", ", $columns);
          $sql_query = "INSERT INTO " . static ::$table . " ($columns) VALUES ($params)";
      }

      $result = self::$data_base->run($sql_query, NULL, $filtered);

      if ($result) {
        $result = array('error' => false, 'message' => self::$data_base->get_id());

        // Cache invalidation
        $cache = self::get_cache();

        if ($cache) {
          $cache->clear();
        }
      } else {
        $result = array('error' => true, 'message' => self::$data_base->get_error());
      }

      return $result;
    }

    public static function update($id, $values) {
      $values["id"] = $id;

      $class = get_called_class();
      $obj = new $class($values);

      $obj->save();
    }

    public static function create($values) {
      $class = get_called_class();
      $obj = new $class($values);

      $obj->save();
    }

    public static function delete($id) {
      self::get_connection();
      $sql_query = "UPDATE " . static ::$table . " SET deleted = 1 WHERE id = '$id'";

      $result = self::$data_base->run($sql_query, NULL, NULL);

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

    private static function auto_generation() {
      self::check_table();
      self::check_fields();
    }

    private static function check_table() {
      $table = static::$table;
      $fields = static::$fields;
      $primary_key = static::$primary_key;

      self::$data_base->check_table($table, $fields, $primary_key);
    }

    private static function check_fields() {
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
