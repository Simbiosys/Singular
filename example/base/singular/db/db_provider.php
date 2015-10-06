<?php	namespace Singular;	abstract class DataBaseProvider {		protected $resource;		public abstract function connect($host, $user, $pass, $dbname);		public abstract function disconnect();		public abstract function get_error_number();		public abstract function get_error();		public abstract function query($q);		public abstract function number_of_rows($resource);		public abstract function get_array($resource);		public abstract function is_there_connection();		public abstract function escape($var);		public abstract function get_id();		public abstract function change_database($database);		public abstract function set_charset($charset);		public abstract function check_table($table, $fields, $primary_key);		public abstract function check_fields($table, $fields, $primary_key);		public abstract function format_fields($data, $fields_structure);	}