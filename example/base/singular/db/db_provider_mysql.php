<?php	namespace Singular;	class ProveedorMySQL extends DataBaseProvider {		////////////////////////////////////////////////////////////////////////////    //                            FIELD FORMATTERS    ////////////////////////////////////////////////////////////////////////////    private static function integer_formatter($data) {      return intval($data);    }    private static function string_formatter($data) {      return $data;    }		private static function timestamp_formatter($data) {			return date('d/m/Y H:i:s', strtotime($data));    }		private static function boolean_formatter($data) {			return $data === 1 ? TRUE : FALSE;    }    private static $formatter = array(      "integer" => integer_formatter,      "string" => string_formatter,			"timestamp" => timestamp_formatter,			"boolean" => boolean_formatter,			"binary" => NULL    );		////////////////////////////////////////////////////////////////////////////    //                            FIELD GENERATOR    ////////////////////////////////////////////////////////////////////////////		private static $data_types = array(			"integer" => "int",			"string" => "varchar",			"timestamp" => "timestamp",			"boolean" => "boolean",			"binary" => "blob"		);		private static function field_generator($field_name, $field_structure) {			$field_type = isset($field_structure["type"]) ? $field_structure["type"] : "string";			$field_data_type = static::$data_types[$field_type];			$field_size = isset($field_structure["size"]) ? $field_structure["size"] : NULL;			$field_nullness = isset($field_structure["null"]) ? $field_structure["null"] : TRUE;			$field_auto_increment = isset($field_structure["auto_increment"]) ? $field_structure["auto_increment"] : FALSE;			$field_default = isset($field_structure["default"]) ? $field_structure["default"] : "NULL";			$result = "$field_name $field_data_type";			if ($field_size) {				$result .= "($field_size)";			}			$result .= $field_nullness ? " NULL" : " NOT NULL";			if ($field_default) {				if ($field_default != "NULL" && $field_default != "CURRENT_TIMESTAMP") {					$field_default = "'$field_default'";				}				if ($field_nullness || $field_default != "NULL") {					$result .= " DEFAULT $field_default";				}			}			if ($field_auto_increment) {				$result .= " AUTO_INCREMENT";			}			return $result;		}		////////////////////////////////////////////////////////////////////////////    //                                METHODS    ////////////////////////////////////////////////////////////////////////////		public function connect($servidor, $usuario, $clave, $base_datos) {			$this->database_resource = new \mysqli($servidor, $usuario, $clave, $base_datos);			if ($this->database_resource->connect_errno) {				// Connection fails				error_log($this->database_resource->connect_error);			}			return $this->database_resource;		}		public function disconnect() {			return $this->database_resource->close();		}		public function get_error_number() {			return $this->database_resource->errno;		}		public function get_error() {			return $this->database_resource->error;		}		public function query($q) {      return $this->database_resource->query($q);    }    public function number_of_rows($database_resource) {      $number_of_rows = 0;      if ($database_resource) {        $number_of_rows = $database_resource->num_rows;      }      return $number_of_rows;    }    public function get_array($result) {      return $result->fetch_assoc();    }    public function is_there_connection() {      return !is_NULL($this->database_resource);    }    public function escape($var) {      return $this->database_resource->real_escape_string($var);    }    public function get_id() {      return $this->database_resource->insert_id;    }    public function change_database($database) {      return $this->database_resource->select_db($database);    }    public function set_charset($charset) {      $result = $this->database_resource->set_charset($charset);			$this->database_resource->query('SET NAMES utf8');			return $result;    }		public function format_fields($data, $fields_structure) {      foreach ($data as $key => $value) {        if (isset($fields_structure[$key])) {          $field_structure = $fields_structure[$key];          $field_type = isset($field_structure["type"]) ? $field_structure["type"] : "string";          $formatter = isset(self::$formatter[$field_type]) ? self::$formatter[$field_type] : NULL;          if ($formatter) {            $value = self::$formatter($value);            $data[$key] = $value;          }        }      }      return $data;    }		////////////////////////////////////////////////////////////////////////////		//                            AUTO GENERATION		////////////////////////////////////////////////////////////////////////////		public function check_table($table, $fields, $primary_key) {			$field_list = array();			foreach ($fields as $field_name => $field_structure) {				array_push($field_list, self::field_generator($field_name, $field_structure));			}			if ($primary_key) {				array_push($field_list, "PRIMARY KEY($primary_key)");			}			$field_list = implode(", ", $field_list);			$sql_query = "CREATE TABLE IF NOT EXISTS $table ($field_list);";			$result = $this->database_resource->query($sql_query);			if ($result === FALSE) {				var_dump($this->database_resource->error);			}			return $result;		}		public function check_fields($table, $fields, $primary_key_definition) {			// Table structure			// Check existing fields in table			$structure = $this->database_resource->query("DESCRIBE $table");			$processed_fields = array();			$primary_keys = array();			while ($field = $structure->fetch_assoc()) {			  $field_name = $field["Field"];				$field_is_primary_key = $field["Key"] === "PRI";				$processed_fields[$field_name] = TRUE;				if ($field_is_primary_key) {					array_push($primary_keys, $field_name);				}				$field_should_exist = isset($fields[$field_name]);				if (!$field_should_exist) {					$this->delete_field($table, $field_name);				}				else {					$this->check_field_attributes($table, $field, $fields[$field_name]);				}			}			// Check remaining fields in data structure			foreach ($fields as $field_name => $field_attributes) {				if (isset($processed_fields[$field_name]))					continue;				$this->create_field($table, $field_name, $field_attributes);			}			// Check primary key			$primary_keys = implode(",", $primary_keys);			$primary_key_definition = str_replace(" ", "", $primary_key_definition);			if ($primary_keys != $primary_key_definition) {				$this->change_primary_key($table, $primary_key_definition);			}		}		private function create_field($table, $field_name, $field_attributes) {			$sentence = self::field_generator($field_name, $field_attributes);			$sql_query = "ALTER TABLE $table ADD COLUMN $sentence;";			$this->execute_sentence($sql_query);		}		private function delete_field($table, $field_name) {			$sql_query = "ALTER TABLE $table DROP COLUMN $field_name;";			$this->execute_sentence($sql_query);		}		private function modify_field($table, $field_name, $field_attributes) {			$sentence = self::field_generator($field_name, $field_attributes);			$sql_query = "ALTER TABLE $table MODIFY COLUMN $sentence;";			$this->execute_sentence($sql_query);		}		private function change_primary_key($table, $primary_key) {			$sql_query = "ALTER TABLE $table DROP PRIMARY KEY, ADD PRIMARY KEY($primary_key);";			$this->execute_sentence($sql_query);		}		private function check_field_attributes($table, $field, $field_attributes) {			$equal = TRUE;			// Field settings			$field_name = $field["Field"];			$field_type = str_replace(" ", "", strtolower($field["Type"]));			$field_nullness = isset($field["Null"]) ? strtoupper($field["Null"]) === "YES" : TRUE;			$field_key = $field["Key"];			$field_default = $field["Default"];			$field_extra = strtolower($field["Extra"]);			$field_auto_increment = (strpos($field_extra, "auto_increment") !== FALSE);			// How the field should be			$field_attributes_type = isset($field_attributes["type"]) ? strtolower($field_attributes["type"]) : "string";			$field_attributes_data_type = static::$data_types[$field_attributes_type];			$field_attributes_size = isset($field_attributes["size"]) ? $field_attributes["size"] : NULL;			$field_attributes_nullness = isset($field_attributes["null"]) ? $field_attributes["null"] : TRUE;			$field_attributes_default = isset($field_attributes["default"]) ? $field_attributes["default"] : NULL;			$field_attributes_auto_increment = isset($field_attributes["auto_increment"]) ? $field_attributes["auto_increment"] : FALSE;			// Type and size			if ($field_attributes_size) {				$field_attributes_size_whole = "$field_attributes_data_type($field_attributes_size)";				$equal = $equal && ($field_type === $field_attributes_size_whole);			}			else {				$field_type_type = explode("(", $field_type, 2);				$field_type_type = $field_type_type[0];				$equal = $equal && ($field_type_type === $field_attributes_data_type);			}			// Nullness			$equal = $equal && $field_nullness === $field_attributes_nullness;			// Default			$equal = $equal && $field_default == $field_attributes_default;			// Auto increment			$equal = $equal && $field_auto_increment == $field_attributes_auto_increment;			if (!$equal) {				$this->modify_field($table, $field_name, $field_attributes);			}		}		private function execute_sentence($sentence) {			$this->set_charset('utf-8');			$result = $this->database_resource->query($sentence);			if ($result === FALSE) {				var_dump($this->database_resource->error);			}		}	}