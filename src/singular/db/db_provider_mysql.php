<?php
	/**
	* Singular's MySQL Provider
	*/
	namespace Singular;

	/**
	* Singular's MySQL Provider Class
	*/
	class MySQLProvider extends DataBaseProvider {
		////////////////////////////////////////////////////////////////////////////
    //                            FIELD FORMATTERS
    ////////////////////////////////////////////////////////////////////////////

		/**
      * Formats data as integer.
      *
      * @param Object $data Data to format.
      *
      * @return integer
      */
    private static function integer_formatter($data) {
      return intval($data);
    }

    	/**
      * Formats data as double.
      *
      * @param Object $data Data to format.
      *
      * @return double
      */
    private static function double_formatter($data) {
      return doubleval($data);
    }

		/**
      * Formats data as string.
      *
      * @param Object $data Data to format.
      *
      * @return string
      */
    private static function string_formatter($data) {
      return $data;
    }

		/**
      * Formats data as timestamp.
      *
      * @param Object $data Data to format.
      *
      * @return string
      */
		private static function timestamp_formatter($data) {
			return date('d/m/Y H:i:s', strtotime($data));
    }

		/**
      * Formats data as boolean.
      *
      * @param Object $data Data to format.
      *
      * @return boolean
      */
		private static function boolean_formatter($data) {
			return $data === 1 || $data === "1" ? TRUE : FALSE;
    }

		/** @var Array $formatter Relation between field types and formatters. */
		/*
    private static $formatter = array(
      "integer" => integer_formatter,
      "string" => string_formatter,
			"timestamp" => timestamp_formatter,
			"boolean" => boolean_formatter,
			"binary" => NULL
    );
    */

    private static function format_value($format, $value){
    	switch ($format) {
    		case 'integer':
    			return self::integer_formatter($value);
    			break;

    		case 'string':
    			return self::string_formatter($value);
    			break;

    		case 'timestamp':
    			return self::timestamp_formatter($value);
    			break;

    		case 'boolean':
    			return self::boolean_formatter($value);
    			break;

    		case 'binary':
    			return $value;
    			break;

    		case 'double':
    			return self::double_formatter($value);
    			break;
    	}
    }

		////////////////////////////////////////////////////////////////////////////
    //                            FIELD GENERATOR
    ////////////////////////////////////////////////////////////////////////////

		/** @var Array $data_types Relation between field types and database types. */
		private static $data_types = array(
			"integer" => "int",
			"string" => "varchar",
			"timestamp" => "timestamp",
			"boolean" => "boolean",
			"binary" => "blob",
			"double" => "double"
		);

		/**
      * Returns the syntax to create a field.
      *
      * @param string $field_name Field name.
			* @param string $field_structure Field structure.
      *
      * @return string
      */
		private static function field_generator($field_name, $field_structure) {
			$field_type = isset($field_structure["type"]) ? $field_structure["type"] : "string";
			$field_data_type = static::$data_types[$field_type];
			$field_size = isset($field_structure["size"]) ? $field_structure["size"] : NULL;
			$field_nullness = isset($field_structure["null"]) ? $field_structure["null"] : TRUE;
			$field_auto_increment = isset($field_structure["auto_increment"]) ? $field_structure["auto_increment"] : FALSE;
			$field_default = isset($field_structure["default"]) ? $field_structure["default"] : "NULL";

			$result = "$field_name $field_data_type";

			if ($field_size) {
				$result .= "($field_size)";
			}

			$result .= $field_nullness ? " NULL" : " NOT NULL";

			if ($field_default != NULL) {
				if ($field_default != "NULL" && $field_default != "CURRENT_TIMESTAMP") {
					$field_default = "'$field_default'";
				}

				if ($field_default != "NULL") {
					$result .= " DEFAULT $field_default";
				}
			}

			if ($field_auto_increment) {
				$result .= " AUTO_INCREMENT";
			}

			return $result;
		}

		////////////////////////////////////////////////////////////////////////////
    //                                METHODS
    ////////////////////////////////////////////////////////////////////////////

		/**
		* Connects to a database
		*
		* @param string $host Database server.
		* @param string $user Database user.
		* @param string $pass Database password.
		* @param string $dbname Database name.
    *
		* @return void
		*/
		public function connect($server, $user, $password, $data_base) {
			$this->database_resource = new \mysqli($server, $user, $password, $data_base);

			if ($this->database_resource->connect_errno) {
				// Connection fails
				error_log($this->database_resource->connect_error);
			}

			return $this->database_resource;
		}

		/**
		* Disconnect from a database.
    *
		* @return void
		*/
		public function disconnect() {
			return $this->database_resource->close();
		}

		/**
		* Returns the number of error.
    *
		* @return integer
		*/
		public function get_error_number() {
			return $this->database_resource->errno;
		}

		/**
		* Returns the error information.
    *
		* @return Object
		*/
		public function get_error() {
			return $this->database_resource->error;
		}

		/**
		* Performs a query.
		*
		* @param string $q Query to execute.
    *
		* @return void
		*/
		public function query($q) {
      return $this->database_resource->query($q);
    }

		/**
		* Returns the number of rows.
		*
		* @param Object $resource Database resource.
    *
		* @return integer
		*/
    public function number_of_rows($database_resource) {
      $number_of_rows = 0;

      if ($database_resource && is_object($database_resource)) {
        $number_of_rows = $database_resource->num_rows;
      }

      return $number_of_rows;
    }

		/**
		* Returns the array from a resource.
		*
		* @param Object $resource Database resource.
    *
		* @return Array
		*/
    public function get_array($result) {
      return $result->fetch_assoc();
    }

		/**
		* Checks if there is a connection to the database.
    *
		* @return boolean
		*/
    public function is_there_connection() {
      return !is_NULL($this->database_resource);
    }

		/**
		* Escapes a parameter.
		*
		* @param string $var Parameter to escape.
    *
		* @return string
		*/
    public function escape($var) {
      return $this->database_resource->real_escape_string($var);
    }

		/**
		* Returns the id.
    *
		* @return string
		*/
    public function get_id() {
      return $this->database_resource->insert_id;
    }

		/**
		* Changes the selected database.
		*
		* @param string $database Database to set.
    *
		* @return void
		*/
    public function change_database($database) {
      return $this->database_resource->select_db($database);
    }

		/**
		* Sets a specified charset.
		*
		* @param string $charset Charset to set.
    *
		* @return void
		*/
    public function set_charset($charset) {
      $result = $this->database_resource->set_charset($charset);
			$this->database_resource->query('SET NAMES utf8');

			return $result;
    }

		/**
		* Format data according to the field structure
		*
		* @param Array $data Data to format.
		* @param Array $fields_structure Field structure.
    *
		* @return Array
		*/
		public function format_fields($data, $fields_structure) {
      foreach ($data as $key => $value) {
        if (isset($fields_structure[$key])) {
          $field_structure = $fields_structure[$key];
          $field_type = isset($field_structure["type"]) ? $field_structure["type"] : "string";

				/*
				 $formatter = isset(self::$formatter[$field_type]) ? self::$formatter[$field_type] : NULL;

				 if ($formatter) {
					 $value = self::$formatter($value);

					 $data[$key] = $value;
				 }
				 */
				 $value = self::format_value($field_type, $value);
        }
      }

      return $data;
    }

		/**
		* Gets the query from a model.
		*
		* @param Array $model Model.
		* @param boolean $with_dependencies True to apply dependencies.
    *
		* @return string
		*/
		public function get_query($model, $query_fields, $with_dependencies = FALSE) {
			$query = $model->get_query();

			$table = $model->get_table();
			$filter = $model->get_filter();

			$dependencies = $model->get_dependencies();

			if (!$query_fields || empty($query_fields)) {
				$query_fields = array("*");
			}

			for ($i = 0; $i < count($query_fields); $i++) {
				$field = $query_fields[$i];

				$parts = explode(".", $field);

				$field_table = "";
				$field_name = "";

				if (count($parts) > 1) {
					$field_table = $parts[0];
					$field_name = $parts[1];
				}
				else {
					$field_table = $table;
					$field_name = $field;
				}

				$query_fields[$i] = "$field_table.$field_name";
			}

			$query_fields = implode(",", $query_fields);

			if (!isset($query)) {
        $query = "SELECT $query_fields FROM $table";
      }

			if (!empty($dependencies) && $with_dependencies) {
				foreach ($dependencies as $dependency => $info) {
					$entity = isset($info["entity"]) ? $info["entity"] : "";
					$key = isset($info["key"]) ? $info["key"] : "";
					$filter = isset($info["filter"]) ? $info["filter"] : "";

					if (empty($entity)) {
						continue;
					}

					$object = new $entity();
		      $dependency_table = $object->get_table();

					$join = " LEFT JOIN $dependency_table ON $dependency_table.$key = $table.id";

					if (!empty($filter)) {
						$join .= " AND $filter";
					}

					$query .= $join;
				}
			}

      if (strpos($query, "WHERE") === FALSE) {
          $query .= $this->get_filter($model);
      }

      return $query;
		}

		/**
		* Gets the number of occurrences for a condition.
		*
		* @param Array $model Model.
		* @param Array $condition Condition to search.
    *
		* @return string
		*/
		public function get_count($model, $condition = NULL) {
			$table = $model->get_table();

			if (empty($condition)) {
				$condition = "1 = 1";
			}

			return "SELECT COUNT(1) AS count FROM $table WHERE deleted = 0 AND $condition";
		}

		/**
		* Gets the query for a condition.
		*
		* @param Array $model Model.
		* @param Array $condition Condition to search.
    *
		* @return string
		*/
		public function get_query_by_condition($model, $query_fields, $condition) {
			$result = $this->get_query($model, $query_fields, FALSE);

			$order = $model->get_order();

			if (!empty($condition)) {
	      $result .= " AND $condition";
			}

      $result .= $this->get_order($model);

      return $result;
		}

		/**
		* Gets the query for a key-value pair.
		*
		* @param Array $model Model.
		* @param string $field Field's name.
		* @param string $value Field's value.
    *
		* @return string
		*/
		public function get_query_by_value($model, $field, $value) {
			$result = $this->get_query($model, $model->get_query_fields(), FALSE);
      $result .= " AND $field = ?";
      $result .= $this->get_order($model);

      return $result;
		}

		/**
		* Gets the filter of a model.
		*
		* @param Array $model Model.
    *
		* @return string
		*/
		public function get_filter($model) {
			$filter = $model->get_filter();
			$deletion = $model->get_deletion();

      if (empty($filter)) {
        $filter = $this->get_default_filter();
      }

			if (empty($deletion)) {
				$deletion = $this->get_default_deletion($model);
			}

      return " WHERE $filter AND $deletion";
		}

		/**
		* Gets the default filter.
		*
		* @return string
		*/
		public function get_default_filter() {
			return "1 = 1";
		}

		/**
		* Gets the deleted filter.
		*
		* @return string
		*/
		public function get_default_deletion($model) {
			$table = $model->get_table();

			return "$table.deleted = 0";
		}

		/**
		* Gets the order criteria of a model.
		*
		* @param Array $model Model.
    *
		* @return string
		*/
		public function get_order($model) {
			$order = $model->get_order();

			if (!isset($order) || empty($order)) {
        return "";
      }

      $order = implode(",", $order);

      return " ORDER BY $order";
		}

		/**
		* Gets the update query of a model.
		*
		* @param Array $table Table's name.
		* @param Array $columns Columns to update.
		* @param Array $id Register's identifier.
    *
		* @return string
		*/
		public function get_update($table, $columns, $id) {
			if (!$columns) {
				return NULL;
			}

			$columns = join(" = ?, ", $columns);
			$columns .= ' = ?';

			return "UPDATE $table SET $columns WHERE id = '$id'";
		}

		/**
		* Gets the insert query of a model.
		*
		* @param Array $table Table's name.
		* @param Array $columns Columns to update.
    *
		* @return string
		*/
		public function get_insert($table, $columns) {
			if (count($columns) > 0) {
				$params = join(", ", array_fill(0, count($columns), "?"));
				$columns = join(", ", $columns);
			}
			else {
				$params = "";
				$columns = "";
			}

			return "INSERT INTO $table ($columns) VALUES ($params)";
		}

		/**
		* Gets the delete query of a model.
		*
		* @param Array $table Table's name.
		* @param Array $id Register's identifier.
    *
		* @return string
		*/
		public function get_delete($table, $id) {
			return "UPDATE $table SET deleted = 1, deletion = now() WHERE id = '$id'";
		}

		/**
		* Gets the delete query of a model.
		*
		* @param Array $table Table's name.
		* @param string $condition Condition to apply.
    *
		* @return string
		*/
		public function get_delete_by_condition($table, $condition) {
			return "UPDATE $table SET deleted = 1, deletion = now() WHERE $condition";
		}

		////////////////////////////////////////////////////////////////////////////
		//                            AUTO GENERATION
		////////////////////////////////////////////////////////////////////////////

		/**
		* Checks that a table exists, if not it is created.
		*
		* @param string $table Table's name.
		* @param Array $fields Table's fields.
		* @param string $primary_key Table's primary key.
    *
		* @return void
		*/
		public function check_table($table, $fields, $primary_key) {
			$field_list = array();

			foreach ($fields as $field_name => $field_structure) {
				array_push($field_list, self::field_generator($field_name, $field_structure));
			}

			if ($primary_key) {
				array_push($field_list, "PRIMARY KEY($primary_key)");
			}

			$field_list = implode(", ", $field_list);

			$sql_query = "CREATE TABLE IF NOT EXISTS $table ($field_list);";
			$result = $this->database_resource->query($sql_query);

			if ($result === FALSE) {
				var_dump($this->database_resource->error);
				var_dump($sql_query);
			}

			return $result;
		}

		/**
		* Checks that one table fields exist, if not they are created.
		*
		* @param string $table Table's name.
		* @param Array $fields Table's fields.
		* @param string $primary_key Table's primary key.
    *
		* @return void
		*/
		public function check_fields($table, $fields, $primary_key_definition) {
			// Table structure
			// Check existing fields in table

			$structure = $this->database_resource->query("DESCRIBE $table");
			$processed_fields = array();
			$primary_keys = array();

			while ($field = $structure->fetch_assoc()) {
			  $field_name = $field["Field"];
				$field_is_primary_key = $field["Key"] === "PRI";
				$processed_fields[$field_name] = TRUE;

				if ($field_is_primary_key) {
					array_push($primary_keys, $field_name);
				}

				$field_should_exist = isset($fields[$field_name]);

				if (!$field_should_exist) {
					$this->delete_field($table, $field_name);
				}
				else {
					$this->check_field_attributes($table, $field, $fields[$field_name]);
				}
			}

			// Check remaining fields in data structure

			foreach ($fields as $field_name => $field_attributes) {
				if (isset($processed_fields[$field_name]))
					continue;

				$this->create_field($table, $field_name, $field_attributes);
			}

			// Check primary key

			$primary_keys = implode(",", $primary_keys);
			$primary_key_definition = str_replace(" ", "", $primary_key_definition);

			if ($primary_keys != $primary_key_definition) {
				$this->change_primary_key($table, $primary_key_definition);
			}
		}

		/**
		* Creates a field.
		*
		* @param string $table Table's name.
		* @param string $field_name Field's name.
		* @param string $field_attributes Field's structure.
		*
		* @return void
		*/
		private function create_field($table, $field_name, $field_attributes) {
			$sentence = self::field_generator($field_name, $field_attributes);
			$sql_query = "ALTER TABLE $table ADD COLUMN $sentence;";
			$this->execute_sentence($sql_query);
		}

		/**
		* Deletes a field.
		*
		* @param string $table Table's name.
		* @param string $field_name Field's name.
		*
		* @return void
		*/
		private function delete_field($table, $field_name) {
			$sql_query = "ALTER TABLE $table DROP COLUMN $field_name;";
			$this->execute_sentence($sql_query);
		}

		/**
		* Modifies a field.
		*
		* @param string $table Table's name.
		* @param string $field_name Field's name.
		* @param string $field_attributes Field's structure.
		*
		* @return void
		*/
		private function modify_field($table, $field_name, $field_attributes) {
			$sentence = self::field_generator($field_name, $field_attributes);
			$sql_query = "ALTER TABLE $table MODIFY COLUMN $sentence;";
			$this->execute_sentence($sql_query);
		}

		/**
		* Changes the primary key.
		*
		* @param string $table Table's name.
		* @param string $primary_key Primary key.
		*
		* @return void
		*/
		private function change_primary_key($table, $primary_key) {
			$sql_query = "ALTER TABLE $table DROP PRIMARY KEY, ADD PRIMARY KEY($primary_key);";
			$this->execute_sentence($sql_query);
		}

		/**
		* Checks a field attributes.
		*
		* @param string $table Table's name.
		* @param string $field Field's name.
		* @param string $field_attributes Field's structure.
		*
		* @return void
		*/
		private function check_field_attributes($table, $field, $field_attributes) {
			$equal = TRUE;

			// Field settings
			$field_name = $field["Field"];
			$field_type = str_replace(" ", "", strtolower($field["Type"]));
			$field_nullness = isset($field["Null"]) ? strtoupper($field["Null"]) === "YES" : TRUE;
			$field_key = $field["Key"];
			$field_default = $field["Default"];
			$field_extra = strtolower($field["Extra"]);
			$field_auto_increment = (strpos($field_extra, "auto_increment") !== FALSE);

			// How the field should be
			$field_attributes_type = isset($field_attributes["type"]) ? strtolower($field_attributes["type"]) : "string";
			$field_attributes_data_type = static::$data_types[$field_attributes_type];
			$field_attributes_size = isset($field_attributes["size"]) ? $field_attributes["size"] : NULL;
			$field_attributes_nullness = isset($field_attributes["null"]) ? $field_attributes["null"] : TRUE;
			$field_attributes_default = isset($field_attributes["default"]) ? $field_attributes["default"] : NULL;
			$field_attributes_auto_increment = isset($field_attributes["auto_increment"]) ? $field_attributes["auto_increment"] : FALSE;

			// Type and size
			if ($field_attributes_size) {
				$field_attributes_size_whole = "$field_attributes_data_type($field_attributes_size)";

				$equal = $equal && ($field_type === $field_attributes_size_whole);
			}
			else {
				$field_type_type = explode("(", $field_type, 2);
				$field_type_type = $field_type_type[0];

				$equal = $equal && ($field_type_type === $field_attributes_data_type);
			}

			// Nullness
			$equal = $equal && $field_nullness === $field_attributes_nullness;

			// Default
			$equal = $equal && $field_default == $field_attributes_default;

			// Auto increment
			$equal = $equal && $field_auto_increment == $field_attributes_auto_increment;

			if (!$equal) {
				$this->modify_field($table, $field_name, $field_attributes);
			}
		}

		/**
		* Executes a sentence.
		*
		* @param string $sentence Sentence to execute.
		*
		* @return void
		*/
		private function execute_sentence($sentence) {
			$this->set_charset('utf-8');
			$result = $this->database_resource->query($sentence);

			if ($result === FALSE) {
				var_dump($this->database_resource->error);
			}
		}

		/**
		* Gets the NULL identifier for the database.
		*
		* @return string
		*/
		public function get_null() {
			return "NULL";
		}

		/**
		* Gets the NOW function.
		*
		* @return string
		*/
		public function get_now() {
			return "now()";
		}
	}
