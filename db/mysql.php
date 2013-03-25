<?php
$host = '85.214.202.153';
$db = 'OrderTool2';
$user = 'ecommerce';
$password = 'sFWe5ZWqfvTB4JKH';

class MySQLError extends Exception {
	function __construct($message = null, $code = null, $previous = null) {
		if ($message == null) {
			$message = mysql_error();
		}
		if ($code == null) {
			$code = mysql_errno();
		}
		parent::__construct($message, $code, $previous);
	}
}

class MySQL {
	protected static $data;
	protected static $connection;
	protected static $errors = array();
	
	public static function connect($host, $username, $password) {
		static::$data = array(
				'host' => $host,
				'username' => $username,
				'password' => $password,
			);
			
		static::__connect();
	}
	
	protected static function __connect() {
		static::$connection = mysql_connect(
				static::$data['host'],
				static::$data['username'],
				static::$data['password']
			);
	}
	
	protected static function get_connection() {
		if (!static::$connection) {
			throw new MySQLError(TRUE, 'Please connect first!', 0);
		}
		
		if (!mysql_ping(static::$connection)) {
			static::__connect();
			if (isset(static::$data['db'])) {
				static::select_db(static::$data['db']);
			}
		}
		
		return static::$connection;
	}
	
	protected static function error($error = TRUE, $msg = null, $code = null) {
		if ($msg == null) {
			$msg = mysql_error(static::$connection);
		}
		if ($code == null) {
			$code = mysql_errno(static::$connection);
		}
		static::$errors[] = new MySQLError($msg, $code, static::last_error());
		if ($error) {
			throw static::last_error();
		}
		return FALSE;
	}
	
	public static function build_where_clause($conditions, $operator = 'AND') {
		$where = array();
		foreach ($conditions as $field => $value) {
			$field = static::quote($field);
			if (is_array($value)) {
				foreach ($value as $val) {
					$values[] = (is_object($val)) ? $val->id : $val;
				}
				$where[] = "$field IN (" . join(', ', $values) . ")";
			} elseif (is_object($value)) {
				$where[] = "$field = '" . $value->id . "'";
			} else {
				$where[] = "$field = '$value'";
			}
		}
		return ($where) ? join(" $operator ", $where) : '1';
	}
	
	public static function last_error() {
		$count = count(static::$errors);
		if ($count > 0) {
			return static::$errors[$count - 1];
		}
		return null;
	}
	
	public static function select_db($db) {
		if (!mysql_select_db($db, static::get_connection())) {
			return static::error();
		}
		return (bool) static::$data['db'] = $db;
	}
	
	public static function escape($string) {
		return mysql_real_escape_string($string, static::get_connection());
	}
	
	public static function quote($field) {
		return "`$field`";
	}
	
	public static function query($query, $error = TRUE) {
		if ($result = mysql_query($query, static::get_connection())) {
			return $result;
		}
		return static::error($error);
	}
	
	public static function get($conditions, $table, $error = TRUE) {
		$where = static::build_where_clause($conditions);
		
		$query = "	SELECT *
					FROM " . static::quote($table) . "
					WHERE $where";
		
		if ($result = static::query($query)) {
			switch (static::num_rows($result)) {
				case 0:
					return static::error($error, "No entry fulfills the conditions: $where", 0);
				case 1:
					return MySQL::fetch($result);
				default:
					return static::error($error, "Conditions not unique: $where", 0);
			}
		} else {
			return static::error($error);
		}
	}
	
	public static function get_row_by_id($id, $table, $error = TRUE) {
		return static::get(array('id' => $id), $table, $error);
	}
	
	public static function fetch($result) {
		return mysql_fetch_assoc($result);
	}
	
	public static function insert_id() {
		return mysql_insert_id(static::$connection);
	}
	
	public static function affected_rows() {
		return mysql_affected_rows(static::$connection);
	}
	
	public static function num_rows($result) {
		return mysql_num_rows($result);
	}
	
	public static function start_transaction() {
		static::query("START TRANSACTION");
	}
	
	public static function commit() {
		static::query("COMMIT");
	}
	
	public static function rollback() {
		static::query("ROLLBACK");
	}
	
	public static function get_data() {
		return static::$data;
	}
}

MySQL::connect($host, $user, $password);
MySQL::select_db($db);

function get_row_by_id($id, $table, $exception = TRUE, $identifier='id') {
	$query = "	SELECT *
				FROM $table
				WHERE $identifier = '" . MySQL::escape($id) . "'";
	
	if ($result = MySQL::query($query)) {
		switch (MySQL::num_rows($result)) {
			case 0:
				if ($exception) throw new MySQLError("$identifier '$id' in table '$table' does not exist!", 0);
				return array();
			case 1:
				return MySQL::fetch($result);
			default:
				if ($exception) throw new MySQLError("$identifier '$id' in table '$table' not unique!", 0);
				return array();
		}
	} else {
		if ($exception) throw new MySQLError(mysql_error(), mysql_errno());
		return array();
	}
}

function create_where_clause($conditions, $operator = 'AND') {
	$where = array();
	foreach ($conditions as $field => $value) {
		if (is_array($value)) {
			foreach ($value as $val) {
				$values[] = (is_object($val)) ? $val->id : $val;
			}
			$where[] = "$field IN (" . join(', ', $values) . ")";
		} elseif (is_object($value)) {
			$where[] = "$field = '" . $value->id . "'";
		} else {
			$where[] = "$field = '$value'";
		}
	}
	return ($where) ? join(" $operator ", $where) : '1';
}

function insert($table, array $values) {
	$insert = array();
	foreach ($values as $field => $value) {
		$insert[MySQL::quote($field)] = $value;
	}
	$query = "	INSERT INTO $table
					(" . join(', ', array_keys($insert)) . ")
				VALUES (" . join(', ', $insert) . ")";
	if ($result = MySQL::query($query)) {
		return MySQL::insert_id();
	}
	throw new MySQLError();
}

function select($table, $conditions = array(), $fields = '*') {
	$query = "	SELECT " . ((is_array($fields)) ? join(', ', $fields) : $fields) . "
				FROM $table
				WHERE " . create_where_clause($conditions);
	
	if ($result = MySQL::query($query)) {
		return $result;
	}
	
	throw new MySQLError();
}

function build_select($table, $ids, $order_by = array()) {
	$attributes = Attribute::filter(array('ref_table' => $table));

	$select = array();
	$atr_ids = array();
	foreach ($attributes as $attribute) {
		$id = $attribute->id;
		$atr_ids[] = $id;
		$select[] = "MAX(CASE WHEN v.attribute_id = '$id' THEN v.data END) AS '$id'";
	}
	
	$order= '';
	if ($order_by) {
		$order = 'ORDER BY ' . join(', ', $order_by);
	}

	$query = "	SELECT v.ref_id, " . join(', ', $select) . "
				FROM ot_value AS v
				WHERE v.attribute_id IN (" . join(', ', $atr_ids) . ")
				AND v.ref_id IN (" . join(', ', $ids) . ")
				GROUP BY v.ref_id
				$order";

	return $query;
}