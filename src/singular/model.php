<?php
  /**
  * Singular model
  */
  namespace Singular;

  /**
  * Singular's Basic Model
  */
  class BasicModel {
    /** @var string|null $query Default query for the model. */
    protected $query = NULL;
    /** @var Array|null $query_fields Fields to include in any query. */
    protected $query_fields = NULL;
    /** @var string|null $table Model's table. */
    protected $table = NULL;
    /** @var Array|null $filter Filter to apply to any query. */
    protected $filter = NULL;
    /** @var Array|null $deletion Deletion to apply to any query. */
    protected $deletion = NULL;
    /** @var Array|null $order Order criteria. */
    protected $order = NULL;
    /** @var Array|null $dependencies List of dependencies on other models. */
    protected $dependencies = NULL;
    /** @var Array|null $fields The fields that this model contains. */
    protected $fields = NULL;
    /** @var string|null $primary_key Primary key. */
    protected $primary_key = NULL;
    /** @var boolean $audit If true audits this table updates */
    protected $audit = FALSE;
    /** @var boolean $autogen If false disables autogeneration when it's enabled */
    protected $autogen = TRUE;

    /**
      * Constructor.
      *
      * @param Array $params Method parameters.
			* <ul>
			*		<li><strong>query:</strong> Default query for the model.</li>
			*		<li><strong>query_fields:</strong> Fields to include in any query.</li>
			*		<li><strong>table:</strong> Model's table.</li>
			*		<li><strong>filter:</strong> Filter to apply to any query.</li>
      *		<li><strong>deletion:</strong> Deletion filter to apply. By default it ignores deleted items.</li>
			*		<li><strong>order:</strong> Order criteria.</li>
			*		<li><strong>dependencies:</strong> List of dependencies on other models.</li>
      *		<li><strong>fields:</strong> The fields that this model contains.</li>
      *		<li><strong>primary_key:</strong> Primary key.</li>
      *		<li><strong>audit:</strong> Boolean to enable table audits.</li>
      *		<li><strong>autogen:</strong> Boolean to disable autogeneration.</li>
			*	</ul>
      *
      * @return void
      */
    public function __construct($params) {
      $this->preload($params);
    }

    public function preload($params) {
      if (empty($params)) {
        return;
      }

      if (isset($params["query"])) {
        $this->query = $params["query"];
      }

      if (isset($params["query_fields"])) {
        $this->query_fields = $params["query_fields"];
      }

      if (isset($params["table"])) {
        $this->table = $params["table"];
      }

      if (isset($params["filter"])) {
        $this->filter = $params["filter"];
      }

      if (isset($params["deletion"])) {
        $this->deletion = $params["deletion"];
      }

      if (isset($params["order"])) {
        $this->order = $params["order"];
      }

      if (isset($params["dependencies"])) {
        $this->dependencies = $params["dependencies"];
      }

      if (isset($params["fields"])) {
        $this->fields = $params["fields"];
      }

      if (isset($params["primary_key"])) {
        $this->order = $params["primary_key"];
      }

      if (isset($params["audit"])) {
        $this->audit = $params["audit"];
      }

      if (isset($params["autogen"])) {
        $this->autogen = $params["autogen"];
      }
    }

    /**
      * Returns the table of this model.
      *
      * @return string
      */
    public function get_table() {
      return $this->table;
    }

    /**
      * Returns the query of this model.
      *
      * @return string
      */
    public function get_query() {
      return $this->query;
    }

    /**
      * Returns the filter of this model.
      *
      * @return Array
      */
    public function get_filter() {
      return $this->filter;
    }

    /**
      * Returns the deletion of this model.
      *
      * @return Array
      */
    public function get_deletion() {
      return $this->deletion;
    }

    /**
      * Returns the order criteria of this model.
      *
      * @return Array
      */
    public function get_order() {
      return $this->order;
    }

    /**
      * Returns the fields that appears in any query.
      *
      * @return Array
      */
    public function get_query_fields() {
      return $this->query_fields;
    }

    /**
      * Returns the dependencies of this model on other models.
      *
      * @return Array
      */
    public function get_dependencies() {
      return $this->dependencies;
    }

    /**
      * Returns the fields that this model contains.
      *
      * @return string
      */
    public function get_fields() {
      return $this->fields;
    }

    /**
      * Returns the primary key.
      *
      * @return string
      */
    public function get_primary_key() {
      return $this->primary_key;
    }

    /**
      * Returns the audit flag.
      *
      * @return boolean
      */
    public function get_audit() {
      return $this->audit;
    }

    /**
      * Returns the autogeneration flag.
      *
      * @return boolean
      */
    public function get_autogen() {
      return $this->autogen;
    }
  }

  /**
  * Singular's Model. Any application model should inherit from this class
  */
  class Model extends BasicModel {
    /** @var Database $data_base Points to Singular's database instance. */
    protected $data_base;
    /** @var Cache|null $cache Points to the cache instance, when enabled. */
    protected $cache = NULL;
    /** @var Array $search_fields Fields to search in in the 'search' function. */
    protected $search_fields = array();
    /** @var Array $attributes Attributes. */
    protected $attributes = array();

    /**
      * Constructor.
      *
      * @param Array $values Values to initialise the model.
      *
      * @return void
      */
    public function __construct($values = NULL) {
      $this->get_connection();
      $this->get_cache();

      $this->set($values);

      $this->init();
    }

    /**
      * Sets values to this model's attributes.
      *
      * @param Array $values Values to initialise the model.
      *
      * @return void
      */
    public function set($values) {
      if (empty($values)) {
        return;
      }

      foreach ($values as $key => $value) {
        $this->attributes[$key] = $value;
      }
    }

    /**
      * Sets a value to an attribute.
      *
      * @param string $name Attribute's name.
      * @param string $value Attribute's value.
      *
      * @return void
      */
      public function set_attribute($name, $value) {
      $this->attributes[$name] = $value;
    }

    /**
      * Gets the value of an attribute.
      *
      * @param string $name Attribute's name.
      *
      * @return string
      */
    public function get_attribute($name) {
      return isset($this->attributes[$name]) ? $this->attributes[$name] : NULL;
    }

    /**
      * Gets the database connection.
      *
      * @return DataBaseProvider
      */
    protected function get_connection() {
      $configuration = Configuration::get_database_configuration();
      $this->data_base = Database::get_connection($configuration["provider"], $configuration["server"], $configuration["user"], $configuration["password"], $configuration["data_base"]);
    }

    /**
      * Gets the cache instance when enabled.
      *
      * @return Cache
      */
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

    /**
      * When overwritten the child class can perform initial tasks.
      *
      * @return void
      */
    protected function init() {
      // Lets you perform initial tasks
      $this->filter = $this->data_base->get_default_filter();
      $this->deletion = $this->data_base->get_default_deletion($this);
    }

    /**
      * Return the model's query.
      *
      * @return string
      */
    protected function get_the_query() {
      $this->auto_generation();

      return $this->data_base->get_query($this, $this->get_query_fields(), FALSE);
    }

    /**
      * Return the model's filter.
      *
      * @return string
      */
    protected function get_the_filter() {
      return $this->data_base->get_filter($this);
    }

    /**
      * Return the model's order criteria.
      *
      * @return string
      */
    protected function get_the_order() {
      return $this->data_base->get_order($this);
    }

    /**
      * Wraps data under a key.
      *
      * @param Array $data The whole data container.
      * @param string $model The surrounding key.
      * @param Array $info The inner data to wrap.
      *
      * @return Array
      */
    protected function wrap_data(&$data, $model, $info) {
      $data[$model] = $info;

      return $data;
    }

    /**
      * Returns True when the received array is associative.
      *
      * @param Array $array Array to check.
      *
      * @return boolean
      */
    private function is_assoc(array $array) {
      $keys = array_keys($array);
      return array_keys($keys) !== $keys;
    }

    protected function get_all_dependencies($entity, $cache_identifier) {
      $object = new $entity();
      $dependencies = $object->dependencies;

      $data = array();

      if (empty($dependencies)) {
        return $data;
      }

      foreach ($dependencies as $dependency) {
        $entity = isset($dependency["entity"]) ? $dependency["entity"] : NULL;
        $filter = isset($dependency["filter"]) ? $dependency["filter"] : NULL;
        $deletion = isset($dependency["deletion"]) ? $dependency["deletion"] : NULL;
        $order = isset($dependency["order"]) ? $dependency["order"] : NULL;
        $dependent = isset($dependency["dependent"]) ? $dependency["dependent"] : FALSE;
        $key = isset($dependency["key"]) ? $dependency["key"] : "id";
        $key_parent = isset($dependency["key_parent"]) ? $dependency["key_parent"] : "id";

        $table = $this->get_table_by_entity($entity);

        $key = $this->get_dependency_key($object, $table);
        $key_parent = $this->get_dependency_key_parent($object, $table);

        $fake_model = new BasicModel(array(
          "query" => NULL,
          "query_fields" => NULL,
          "table" => $table,
          "filter" => $filter,
          "deletion" => $deletion,
          "order" => $order
        ));

        $query = $this->data_base->get_query($fake_model, $fake_model->get_query_fields(), FALSE);

        $dependency_cache_identifier = $cache_identifier . "_" . $table . "_" . $key . "_" . $filter;
        $results = $this->process_query_results($entity, $table, $query, NULL, $dependency_cache_identifier, FALSE);

        if (!array_key_exists($table, $data)) {
          $data[$table] = array(
            "key" => $key,
            "key_parent" => $key_parent,
            "data" => array()
          );
        }

		    if (!empty($results)) {
		       foreach ($results as $result) {
		          $key_value = $result[$key];

		          if (!array_key_exists($key_value, $data[$table]["data"])) {
			          $data[$table]["data"][$key_value] = array();
		          }

		          array_push($data[$table]["data"][$key_value], $result);
		        }
        }
      }

      return $data;
    }

    /**
      * Gets the table's name associated to an entity.
      *
      * @param string $entity Entity's name.
      *
      * @return string
      */
    private function get_table_by_entity($entity) {
      $object = new $entity();
      return $object->get_table();
    }

    /**
      * Returns the cached data when available.
      *
      * @param string $identifier Stored data identifier.
      *
      * @return Array|null
      */
    private function get_cached_data($identifier) {
      $cache = $this->get_cache();

      if ($cache) {
        $cached_data = $cache->get($cache_identifier);

        if ($cached_data)
          return $cached_data;
      }

      return NULL;
    }

    ////////////////////////////////////////////////////////////////////////////
    //                                 SEARCH
    ////////////////////////////////////////////////////////////////////////////

    /**
      * When overwritten the child class can specify the source of the search data.
      * This function returns the items to search in.
      *
      * @param Array $condition Search condition.
      *
      * @return Array
      */
    protected function get_search_results($condition = NULL) {
      return $this->get_all($condition);
    }

    /**
      * Searchs the data returned by 'get_search_results' and returns the
      * occurrences.
      *
      * @param Array $terms Words to search in the items.
      * @param Array $condition Optional search condition.
      *
      * @return Array
      */
    public function search($terms, $condition = NULL) {
      $search_fields = $this->search_fields;
      $table = $this->table;

      preg_match_all('/"(?:\\\\.|[^\\\\"])*"|\S+/', $terms, $terms);
      $terms = $terms[0];

      //$terms = explode(" ", $terms);

      for ($i = 0; $i < count($search_fields); $i++) {
        $search_field = $search_fields[$i];

        $parts = explode(".", $search_field);

        if (count($parts) == 1) {
          array_unshift($parts, $table);
        }

        $search_fields[$i] = $parts;
      }

      if (empty($condition)) {
        $condition = array();
      }

      $results = $this->get_search_results($condition);
      $occurrences = array();

      foreach ($results as $result) {
        $valid = TRUE;

        foreach ($terms as $term) {
          $term = rtrim(ltrim(strtolower($term), '"'), '"');
          $found = FALSE;

          foreach ($search_fields as $search_field) {
            if ($this->search_term($search_field, $result, $term, 0)) {
              $found = TRUE;
              break;
            }
          }

          if (!$found) {
            $valid = FALSE;
            break;
          }
        }

        if ($valid) {
          array_push($occurrences, $result);
        }
      }

      return $occurrences;
    }

    /**
      * Inner recursive search function
      *
      * @param Array $search_field One search field parts. i.e.: [table, field]
      * @param Array $data One row to search in.
      * @param Array $term Term to check if exists in the search_field.
      * @param Array $level Recursive level through the search_field length.
      *
      * @return Array
      */
    private function search_term($search_field, $data, $term, $level) {
      if ($level >= count($search_field)) {
        return strpos(strtolower($data), strtolower($term)) !== FALSE;
      }

      if ($this->is_assoc($data)) {
        $data = array($data);
      }

      $part = $search_field[$level];

      foreach ($data as $row) {
        if (!isset($row[$part])) {
          continue;
        }

        $row = $row[$part];

        $partial_result = $this->search_term($search_field, $row, $term, $level + 1);

        if ($partial_result) {
          return TRUE;
        }
      }

      return FALSE;
    }

    /**
      * Function that loops the results obtained by a query.
      *
      * @param string $entity Entity's name.
      * @param string $table Table's name.
      * @param string $query Query to peform.
      * @param Array $params Query parameters.
      * @param string $cache_identifier Identifier to store cached data.
      * @param boolean $wrap_data Determines whether the data must be wrapped or not with the entity's name.
      *
      * @return Array
      */
    private function process_query_results($entity, $table, $query, $params, $cache_identifier, $wrap_data = TRUE) {
      $results = $this->data_base->run($query, NULL, $params);

      $class = get_called_class();
      $obj = new $class();

      $objs = NULL;

      if (!empty($entity)) {
        $obj = $entity = new $entity();
      }
      else {
        $entity = $obj;
      }

      $all_dependencies = $obj->get_all_dependencies($entity, $cache_identifier);

      if ($results) {
        for ($i = 0; $i < sizeof($results); $i++) {
          $fields = $this->data_base->format_fields($results[$i], $this->fields);
          $data = array();

          $processed_fields = $obj->process($fields);

          if ($wrap_data) {
            $obj->wrap_data($data, $table, $processed_fields);
          }
          else {
            $data = $processed_fields;
          }

          if (!array_key_exists("id", $fields)) {
            continue;
          }

          //$id = $fields["id"];

          foreach ($all_dependencies as $dependency => $values) {
            $key = $values["key"];
            $parent_key = $values["key_parent"];
            $dependency_data = $values["data"];

            $id = $fields[$parent_key];

            if (array_key_exists($id, $dependency_data)) {
              $data[$dependency] = $dependency_data[$id];
            }
          }

          $objs[] = $data;
        }
      }

      $cache = $this->get_cache();

      if ($cache) {
        $cache->set($cache_identifier, $objs);
      }

      return $objs;
    }

    /**
      * Split the data to save into groups based on the entity they belong to.
      *
      * @param Array $entities Data to group.
      * @param boolean $exists Determines whether the object exists so it will
      * be updated or should be created.
      *
      * @return Array
      */
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

    /**
      * Returns True if the passed table is a dependency to this model.
      *
      * @param string $table Table's name.
      *
      * @return boolean
      */
    private function is_dependency($table) {
      $dependencies = $this->dependencies;

      return isset($dependencies[$table]);
    }

    /**
      * Returns the dependent table foreign key.
      *
      * @param string $table Table's name.
      *
      * @return string
      */
    private function get_dependency_key($obj, $table) {
      $dependencies = $obj->dependencies;
      $dependency = isset($dependencies[$table]) ? $dependencies[$table] : array();


      return isset($dependency["key"]) ? $dependency["key"] : $table . "_id";
    }

    /**
      * Returns the dependent table parent foreign key.
      *
      * @param string $table Table's name.
      *
      * @return string
      */
    private function get_dependency_key_parent($obj, $table) {
      $dependencies = $obj->dependencies;
      $dependency = isset($dependencies[$table]) ? $dependencies[$table] : array();

      return isset($dependency["key_parent"]) ? $dependency["key_parent"] : "id";
    }

    ////////////////////////////////////////////////////////////////////////////
    //                             QUERY METHODS
    ////////////////////////////////////////////////////////////////////////////

    /**
      * Returns the object with a specified identifier
      *
      * @param string $id Identifier to find.
      *
      * @return Array|null
      */
    public function find($id) {
      $results = $this->find_all_by_value('id', $id);

      return count($results) > 0 ? $results[0] : NULL;
    }

    /**
      * Returns all the registers that have a field with a specified value.
      *
      * @param string $field Field's name.
      * @param string $value Field's value.
      *
      * @return Array
      */
    public function find_all_by_value($field, $value) {
      $this->auto_generation();

      $cache_identifier = "$field_$value";
      $cached = $this->get_cached_data($cache_identifier);

      if ($cached) {
        return $cached;
      }

      $this->get_connection();

      $query = $this->data_base->get_query_by_value($this, $field, $value);

      return $this->process_query_results(NULL, $this->table, $query, array($value), $cache_identifier);
    }

    /**
      * Returns the number of occurrences for a condition.
      *
      * @param Array $condition Condition to search.
      *
      * @return integer
      */
    public function number($condition = NULL) {
      $query = $this->data_base->get_count($this, $condition);
      $result = $this->data_base->run($query, NULL, $params);

      if (empty($result) || count($result) < 1) {
        return NULL;
      }

      $result = $result[0];

      return isset($result["count"]) ? $result["count"] : NULL;
    }

    /**
      * Returns the number of occurrences for the 'search' function.
      *
      * @param Array $terms Words to search.
      * @param Array $condition Condition to search.
      *
      * @return integer
      */
    public function number_search($terms, $condition = NULL) {
      $occurrences = $this->search($terms, $condition);

      return count($occurrences);
    }

    /**
      * Returns all the occurrences for a condition.
      *
      * @param Array $params Condition to search.
      * <ul>
      * <li><strong>condition:</strong> Condition to search.</li>
      * <li><strong>start:</strong> Register number to start with (used with pagination).</li>
      * <li><strong>limit:</strong> Number of register to return (used with pagination).</li>
      * </ul>
      *
      * @return Array
      */
    public function get_all($params = NULL) {
      $condition = $params;
      $start = 0;
      $limit = NULL;
      $settings_limit = Configuration::get_app_settings("page_limit", NULL);
      $query_fields = $this->get_query_fields();

      if (is_array($params)) {
        $condition = isset($params["condition"]) ? $params["condition"] : NULL;
        $start = isset($params["start"]) ? $params["start"] : $start;
        $limit = isset($params["limit"]) ? $params["limit"] : $settings_limit;
        $query_fields = isset($params["query_fields"]) ? $params["query_fields"] : $query_fields;
      }

      $this->auto_generation();

      $cache_identifier = "condition_$condition";
      $cached = $this->get_cached_data($cache_identifier);

      if ($cached) {
        return $cached;
      }

      $this->get_connection();

      $query = $this->data_base->get_query_by_condition($this, $query_fields, $condition);

      $results = $this->process_query_results(NULL, $this->table, $query, NULL, $cache_identifier);

      if (!empty($results)) {
        if ($start != 0 || $limit != NULL) {
          return array_slice($results, $start, $limit);
        }
        else {
          return $results;
        }
      }
      else {
        return array();
      }
    }

    /**
      * Saves a record, insert or update depending on the existence of the id.
      *
      * @param string|null $the_id Register's identifier.
      * @param Array $value Data to store.
      *
      * @return Array
      */
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

      $results = array();
      $main_result = NULL;

      for ($i = 0; $i < count($filtered_data); $i++) {
        $info = $filtered_data[$i];

        $entity = $info["entity"];
        $filtered = $info["filtered"];
        $id = $info["id"];

        // Set foreign key in dependent entities
        if ($this->is_dependency($entity)) {
          $key = $this->get_dependency_key($this, $entity);

          if (!isset($filtered[$key])) {
            $filtered[$key] = $main_entity_id;
          }
        }
        else {
          if (empty($id)) {
            $id = $main_entity_id;
          }
        }

        if (empty($filtered)) {
          $filtered = array();
        }

        $filtered["updating"] = new Unescaped($this->data_base->get_now());

        $columns = NULL;

        if (!empty($filtered)) {
          $columns = array_keys($filtered);
        }

        if ($id) {
          $query = $this->data_base->get_update($entity, $columns, $id);
        }
        else {
          $query = $this->data_base->get_insert($entity, $columns);
        }

		    $result = NULL;

		    if ($query) {
        	$result = $this->data_base->run($query, NULL, $filtered);
        }

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
        }
        else {
          $this->audit($entity, $id ? $id : "NEW", "ERROR", $this->data_base->get_error());
          return array('error' => TRUE, 'message' => $this->data_base->get_error());
        }

        if (!$this->is_dependency($entity)) {
          $main_result = $result;
        }

        array_push($results, $result);
      }

      if ($id) {
        $this->audit($entity, $id, "UPDATE");
      }
      else {
        $this->audit($entity, $main_result["id"], "INSERT");
      }

      return array(
        "main" => $main_result,
        "all" => $results
      );
    }

    /**
      * Updates a record.
      *
      * @param string $id Register's identifier.
      * @param Array $value Data to store.
      *
      * @return Array
      */
    public function update($id, $values) {
      $result = $this->save($id, $values);

      if ($result["error"]) {
        Controller::debug("Update error: " . $result["message"]);
        $this->audit($this->table, $id, "ERROR", $result["message"]);
      }

      $this->audit($this->table, $id, "UPDATE");
    }

    /**
      * Creates a new record.
      *
      * @param Array $value Data to store.
      *
      * @return Array
      */
    public function create($values = array()) {
      if (!isset($values[$this->table])) {
        $values[$this->table] = array();
      }

      $result = $this->save(NULL, $values);

      if (array_key_exists("main", $result) && array_key_exists("error", $result["main"]) && $result["main"]["error"] === TRUE) {
        Controller::debug("Insert error: " . $result["main"]["message"]);
        $this->audit($this->table, "NEW", "ERROR", $result["main"]["message"]);
      }

      if (array_key_exists("all", $result) && array_key_exists("error", $result["all"]) && $result["all"]["error"] === TRUE) {
        Controller::debug("Insert error: " . $result["all"]["message"]);
        $this->audit($this->table, "NEW", "ERROR", $result["all"]["message"]);
      }

      $this->audit($this->table, $result["main"]["id"], "INSERT");

      return $result;
    }

    /**
      * Deletes a record.
      *
      * @param string $id Register's identifier.
      *
      * @return void
      */
    public function delete($id) {
      $this->_delete("id = '$id'", $id);
    }

    /**
      * Deletes a record.
      *
      * @param string $condition Condition to apply.
      *
      * @return void
      */
    public function delete_by_condition($condition) {
      $this->_delete($condition);
    }

    private function _delete($condition, $id = NULL) {
      $this->get_connection();

      $all_to_remove = array(
        array("id" => $id)
      );

      if (empty($id)) {
        $all_to_remove = $this->get_all($condition);
        $all_to_remove = isset($all_to_remove[$this->table]) ? $all_to_remove[$this->table] : array();
      }

      $query = $this->data_base->get_delete_by_condition($this->table, $condition);
      $result = $this->data_base->run($query, NULL, NULL);

      if (!$result) {
        Controller::debug("Delete error: " . $this->data_base->get_error());
        $this->audit($this->table, $id ? $id : $condition, "ERROR", $this->data_base->get_error());
        return;
      }

      $this->audit($this->table, $id ? $id : $condition, "DELETE");

      // Cascade delete
      $dependencies = empty($this->dependencies) ? array() : $this->dependencies;

      foreach ($dependencies as $dependency) {
        $entity = isset($dependency["entity"]) ? $dependency["entity"] : NULL;
        $filter = isset($dependency["filter"]) ? $dependency["filter"] : NULL;
        $order = isset($dependency["order"]) ? $dependency["order"] : NULL;
        $dependent = isset($dependency["dependent"]) ? $dependency["dependent"] : FALSE;

        if ($dependent) {
          $table = $this->get_table_by_entity($entity);
          $key = $this->get_dependency_key($this, $table);

          for ($i = 0; $i < count($all_to_remove); $i++) {
            $item = $all_to_remove[$i];
            $id = $item["id"];

            $condition = "$key = '$id'";

            $fake_model = new BasicModel(array(
              "query" => NULL,
              "query_fields" => NULL,
              "table" => $table,
              "filter" => $filter,
              "order" => $order
            ));

            $query = $this->data_base->get_query_by_condition($fake_model, $fake_model->get_query_fields(), $condition);
            $cache_identifier = "condition_$condition";
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

    /**
      * When overwritten lets the new model modify any record returned by a query
      *
      * @param Array $data Each query register.
      *
      * @return Array
      */
    protected function process($data) {
      return $data;
    }

    ////////////////////////////////////////////////////////////////////////////
    //                             AUTO GENERATION
    ////////////////////////////////////////////////////////////////////////////

    /**
      * Model database table autogeneration.
      *
      * @return void
      */
    protected function auto_generation() {
      if (!$this->get_autogen() || !Configuration::get_autogen()) {
      	return;
      }

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

    /**
      * Checks if the table exists and creates it if not.
      *
      * @return void
      */
    protected function check_table() {
      $table = $this->table;
      $fields = $this->fields;
      $primary_key = $this->primary_key;

      $this->data_base->check_table($table, $fields, $primary_key);
    }

    /**
      * Checks table fields, creates new ones and modifies the ones that differ,
      *
      * @return void
      */
    protected function check_fields() {
      $table = $this->table;
      $fields = $this->fields;
      $primary_key = $this->primary_key;

      // ID
      if (!isset($fields["id"])) {
        $fields["id"] = array(
          "type" => "integer",
          "null" => FALSE,
          "auto_increment" => TRUE
        );
      }

      // Deleted
      if (!isset($fields["deleted"])) {
        $fields["deleted"] = array(
          "type" => "boolean",
          "null" => FALSE,
          "default" => "0"
        );
      }

      // Creation
      if (!isset($fields["creation"])) {
        $fields["creation"] = array(
          "type" => "timestamp",
          "null" => FALSE,
          "default" => "CURRENT_TIMESTAMP"
        );
      }

      // Update
      if (!isset($fields["updating"])) {
        $fields["updating"] = array(
          "type" => "timestamp",
          "null" => TRUE
        );
      }

      // Deletion
      if (!isset($fields["deletion"])) {
        $fields["deletion"] = array(
          "type" => "timestamp",
          "null" => TRUE
        );
      }

      $this->data_base->check_fields($table, $fields, $primary_key);
    }

    /**
      * Audits a register change
      *
      * @param integer $id Register identifier.
      * @param string $action Action to audit (i.e. UPDATE).
      *
      * @return void
      */
    protected function audit($table, $id, $action, $observations = NULL) {
      if (!$this->audit) {
        return;
      }

      $audit = new Audit();
      $audit->audit_action($table, $id, $action, $observations);
    }
  }

  ////////////////////////////////////////////////////////////////////////////////
  //                              Audits
  ////////////////////////////////////////////////////////////////////////////////

  class Audit extends Model {
    protected $table = "singular_audits";

    protected $fields = array(
      "id" => array(
        "type" => "integer",
        "null" => FALSE,
        "auto_increment" => TRUE
      ),
      "entity" => array(
        "type" => "string",
        "size" => 40,
        "null" => FALSE
      ),
      "entity_id" => array(
        "type" => "string",
        "size" => 100,
        "null" => FALSE
      ),
      "user_id" => array(
        "type" => "integer",
        "null" => TRUE
      ),
      "user_name" => array(
        "type" => "string",
        "size" => 100,
        "null" => TRUE
      ),
      "action" => array(
        "type" => "string",
        "size" => 15,
        "null" => FALSE
      ),
      "observations" => array(
        "type" => "string",
        "size" => 400,
        "null" => TRUE
      )
    );

    protected $primary_key = "id";

    public function audit_action($table, $id, $action, $observations = NULL) {
      $this->create(array(
        "singular_audits" => array(
          "entity" => $table,
          "entity_id" => $id,
          "user_id" => Authentication::get_user_id(),
          "user_name" => Authentication::get_user_name(),
          "observations" => $observations,
          "action" => $action
        )
      ));
    }
  }
