<?php
  namespace Singular;

  class Model {
    protected $data_base;
    protected $table;
    protected $query;
    protected $filter = NULL;
    protected $order;

    protected $fields;
    protected $query_fields;
    protected $primary_key = NULL;
    protected $dependencies = NULL;

    protected $cache = NULL;

    function __construct($values = NULL) {
      $this->get_connection();
      $this->get_cache();

      if (!empty($values)) {
        $this->set($values);
      }

      $this->init();
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

    public function get_table() {
      return $this->table;
    }

    ////////////////////////////////////////////////////////////////////////////
    //                          DATABASE CONNECTION
    ////////////////////////////////////////////////////////////////////////////

    protected function get_connection() {
      $configuration = Configuration::get_database_configuration();
      $this->data_base = Database::get_connection($configuration["provider"], $configuration["server"], $configuration["user"], $configuration["password"], $configuration["data_base"]);
    }

    ////////////////////////////////////////////////////////////////////////////
    //                                CACHE
    ////////////////////////////////////////////////////////////////////////////

    protected function get_cache() {
      if (empty($this->cache)) {
        $cache = Configuration::get_cache();

        if ($cache) {
          $r = new \ReflectionClass($cache);
          $this->cache = $r->newInstanceArgs(array($this->table));
        }
      }

      return $this->cache;
    }

    ////////////////////////////////////////////////////////////////////////////
    //                        QUERY AUXILIARY METHODS
    ////////////////////////////////////////////////////////////////////////////

    protected function init() {
      // Lets you perform initial tasks
      $this->filter = $this->data_base->get_default_filter();
    }

    protected function get_query() {
      $this->auto_generation();

      $query = $this->query;
      $query_fields = $this->query_fields;
      $table = $this->table;
      $filter = $this->filter;

      return $this->data_base->get_query($query, $query_fields, $table, $filter);
    }

    protected function get_filter() {
      $filter = $this->filter;

      return $this->data_base->get_filter($filter);
    }

    protected function get_order() {
      $order = $this->order;

      return $this->data_base->get_order($order);
    }

    protected function wrap_data(&$data, $model, $info) {
      $data[$model] = $info;

      return $data;
    }

    private function is_assoc(array $array) {
      $keys = array_keys($array);
      return array_keys($keys) !== $keys;
    }

    protected function set_dependencies($entity, $id, &$data, $cache_identifier) {
      $object = new $entity();
      $dependencies = $object->dependencies;

      if (empty($dependencies)) {
        return $data;
      }

      foreach ($dependencies as $dependency) {
        $entity = isset($dependency["entity"]) ? $dependency["entity"] : NULL;

        $filter = isset($dependency["filter"]) ? $dependency["filter"] : NULL;
        $order = isset($dependency["order"]) ? $dependency["order"] : NULL;
        $dependent = isset($dependency["dependent"]) ? $dependency["dependent"] : FALSE;

        $table = $this->get_table_by_entity($entity);
        $key = $this->get_dependency_key($table);

        $condition = "$key = '$id'";
        $query_fields = NULL;
        $query = $this->data_base->get_query_by_condition(NULL, $query_fields, $table, $filter, $order, $condition);

        $dependency_cache_identifier = $cache_identifier . "_" . $table . "_" . $key . "_" . $filter;
        $results = $this->process_query_results($entity, $table, $query, NULL, $dependency_cache_identifier, FALSE);

        $data[$table] = $results;
      }

      return $data;
    }

    private function get_table_by_entity($entity) {
      $object = new $entity();
      return $object->get_table();
    }

    private function get_cached_data($identifier) {
      $cache = $this->get_cache();

      if ($cache) {
        $cached_data = $cache->get($cache_identifier);

        if ($cached_data)
          return $cached_data;
      }

      return NULL;
    }

    private function process_query_results($entity, $table, $query, $params, $cache_identifier, $wrap_data = TRUE) {
      $results = $this->data_base->run($query, NULL, $params);

      $class = get_called_class();
      $obj = new $class();

      $objs = NULL;

      if (empty($entity)) {
        $entity = $obj;
      }
      else {
        $entity = new $entity();
      }

      if ($results) {
        for ($i = 0; $i < sizeof($results); $i++) {
          $fields = $this->data_base->format_fields($results[$i], $this->fields);
          $id = isset($fields["id"]) ? $fields["id"] : NULL;

          $data = array();

          $processed_fields = $obj->process($fields);

          if ($wrap_data) {
            $this->wrap_data($data, $table, $processed_fields);
          }
          else {
            $data = $processed_fields;
          }

          $this->set_dependencies($entity, $id, $data, $cache_identifier);

          $objs[] = $data;
        }
      }

      $cache = $this->get_cache();

      if ($cache) {
        $cache->set($cache_identifier, $objs);
      }

      return $objs;
    }

    private function filter_by_entity($entities, $exists) {
      $filtered_data = array();

      foreach ($entities as $entity => $rows) {
        if (!is_array($rows)) {
          $rows = array($rows);
        }

        if (count($rows) == 0) {
          array_push($filtered_data, array(
            "entity" => $entity
          ));
        }
        else {
          if ($this->is_assoc($rows)) {
            $rows = array($rows);
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

            if ( (!$this->is_dependency($entity) && !$exists)
                  || $id !== NULL
                  || count($filtered) > 0 ) {
              array_push($filtered_data, array(
                "entity" => $entity,
                "id" => $id,
                "filtered" => $filtered
              ));
            }
          }
        }
      }

      return $filtered_data;
    }

    private function is_dependency($table) {
      $dependencies = $this->dependencies;

      return isset($dependencies[$table]);
    }

    private function get_dependency_key($table) {
      $dependencies = $this->dependencies;
      $dependency = isset($dependencies[$table]) ? $dependencies[$table] : array();

      return isset($dependency["key"]) ? $dependency["key"] : $table . "_id";
    }

    ////////////////////////////////////////////////////////////////////////////
    //                             QUERY METHODS
    ////////////////////////////////////////////////////////////////////////////

    public function find($id) {
      $results = $this->find_all_by_value('id', $id);

      return count($results) > 0 ? $results[0] : NULL;
    }

    public function find_all_by_value($field, $value) {
      $this->auto_generation();

      $cache_identifier = "$field_$value";
      $cached = $this->get_cached_data($cache_identifier);

      if ($cached) {
        return $cached;
      }

      $this->get_connection();

      $query = $this->data_base->get_query_by_value($this->query, $this->query_fields, $this->table, $this->filter, $this->order, $field, $value);

      return $this->process_query_results(NULL, $this->table, $query, array($value), $cache_identifier);
    }

    public function get_all($condition = NULL) {
      $this->auto_generation();

      $cache_identifier = "condition_$condition";
      $cached = $this->get_cached_data($cache_identifier);

      if ($cached) {
        return $cached;
      }

      $this->get_connection();

      $query = $this->data_base->get_query_by_condition($this->query, $this->query_fields, $this->table, $this->filter, $this->order, $condition);

      return $this->process_query_results(NULL, $this->table, $query, NULL, $cache_identifier);
    }

    public function save($the_id, $values) {
      $this->auto_generation();
      $this->get_connection();

      $filtered_data = $this->filter_by_entity($values, isset($the_id));

      $table = $this->table;

      $main_entity_id = $this->get_attribute("id");

      if (empty($main_entity_id)) {
        $main_entity_id = $the_id;
      }

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
        if ($this->is_dependency($entity)) {
          $key = $this->get_dependency_key($entity);

          if (!isset($filtered[$key])) {
            $filtered[$key] = $main_entity_id;
          }
        }

        $columns = NULL;

        if (!empty($filtered)) {
          $columns = array_keys($filtered);
        }

        if ($id) {
          $query = $this->data_base->get_update($entity, $columns, $id);
        }
        else {
          $query = $this->data_base->get_insert($entity, $columns, $params);
        }

        $result = $this->data_base->run($query, NULL, $filtered);

        if ($result) {
          $new_id = $this->data_base->get_id();
          $result = array('error' => FALSE, 'message' => $new_id);

          if (empty($main_entity_id) && !$this->is_dependency($entity)) {
            $main_entity_id = $new_id;
          }

          // Cache invalidation
          $cache = $this->get_cache();

          if ($cache) {
            $cache->clear();
          }
        } else {
          return array('error' => TRUE, 'message' => $this->data_base->get_error());
        }
      }

      return $result;
    }

    public function update($id, $values) {
      $result = $this->save($id, $values);

      if ($result["error"]) {
        Controller::debug("Update error: " . $result["message"]);
      }
    }

    public function create($values) {
      if (!isset($values[$this->table])) {
        $values[$this->table] = array();
      }

      $result = $this->save(NULL, $values);

      if ($result["error"]) {
        Controller::debug("Insert error: " . $result["message"]);
      }

      return $result;
    }

    public function delete($id) {
      $this->get_connection();
      $query = $this->data_base->get_delete($this->table, $id);
      $result = $this->data_base->run($query, NULL, NULL);

      if (!$result) {
        Controller::debug("Delete error: " . $this->data_base->get_error());
        return;
      }

      // Cascade delete
      $dependencies = $this->dependencies;

      foreach ($dependencies as $dependency) {
        $entity = isset($dependency["entity"]) ? $dependency["entity"] : NULL;
        $filter = isset($dependency["filter"]) ? $dependency["filter"] : NULL;
        $order = isset($dependency["order"]) ? $dependency["order"] : NULL;
        $dependent = isset($dependency["dependent"]) ? $dependency["dependent"] : FALSE;

        if ($dependent) {
          $table = $this->get_table_by_entity($entity);
          $key = $this->get_dependency_key($table);
          $condition = "$key = '$id'";

          $query = $this->data_base->get_query_by_condition(NULL, NULL, $table, $filter, $order, $condition);
          $dependency_cache_identifier = $cache_identifier . "_" . $table . "_" . $key . "_" . $filter;
          $results = $this->process_query_results($entity, $table, $query, NULL, $dependency_cache_identifier, FALSE);

          $object = new $entity();

          if ($results) {
            foreach ($results as $result) {
              $element_id = isset($result["id"]) ? $result["id"] : NULL;

              if ($element_id) {
                $query = $this->data_base->get_delete($object->table, $element_id);
                $result = $this->data_base->run($query, NULL, NULL);

                if (!$result) {
                  Controller::debug("Delete error: " . $this->data_base->get_error());
                  return;
                }
              }
            }

            // Cache invalidation
            $cache = $object->get_cache();

            if ($cache) {
              $cache->clear();
            }
          }
        }
      }

      // Cache invalidation
      $cache = $this->get_cache();

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

    protected function auto_generation() {
      $this->check_table();
      $this->check_fields();

      // Check table and fields for dependencies
      $dependencies = $this->dependencies;

      if (empty($dependencies)) {
        return;
      }

      foreach ($dependencies as $key => $info) {
        $entity = isset($info["entity"]) ? $info["entity"] : NULL;

        if (empty($entity)) {
          continue;
        }

        $object = new $entity();
        $object->check_table();
        $object->check_fields();
      }
    }

    protected function check_table() {
      $table = $this->table;
      $fields = $this->fields;
      $primary_key = $this->primary_key;

      $this->data_base->check_table($table, $fields, $primary_key);
    }

    protected function check_fields() {
      $table = $this->table;
      $fields = $this->fields;
      $primary_key = $this->primary_key;

      // Deleted
      if (!isset($fields["deleted"])) {
        $fields["deleted"] = array(
          "type" => "boolean",
          "null" => FALSE,
          "default" => "0"
        );
      }

      $this->data_base->check_fields($table, $fields, $primary_key);
    }
  }
