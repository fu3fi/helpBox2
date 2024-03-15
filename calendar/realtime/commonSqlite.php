<?php

	class CommonSqlite {

		private $dbName;
		static $basePointer = null;
		static $classPointer = null;

		// NULL
		// INTEGER
		// REAL
		// TEXT
		// BLOB

		function __construct($dbName) {
			static::$basePointer = new PDO('sqlite:'.$dbName);
		}

		function __destruct() {}

		static function increment($value) {
			return ++$value;
		}

		static function decrement($value) {
			return --$value;
		}

		static function increase($value, $step) {
			return $value+$step;
		}

		static function decrease($value, $step) {
			return $value-$step;
		}

		static function wrapper($func) {
			return function() use ($func) {
				$args = func_get_args();
				return call_user_func($func, ...$args);
			};
		}

		static function getPointer($dbName) {
			if (static::$basePointer == null) {
				static::$classPointer = new static($dbName);
				return static::$classPointer;
			}
			return static::$classPointer;
		}

		function insert($tableName, $row) {
			$query = 'INSERT INTO '.$tableName.' VALUES ('.implode(',', array_fill(0, count($row), '?')).');';
			return static::$basePointer->prepare($query)->execute($row);
		}

		function select($tableName, $id=null) {
			if ($id != null) {
				$query = 'SELECT * FROM '.$tableName.' WHERE id='.$id.';';
			} else {
				$query = 'SELECT * FROM '.$tableName.';';
			}
			
			$result = static::$basePointer->query($query, PDO::FETCH_ASSOC);
			return $result->fetchAll();
		}

		function update($tableName, $fields, $values, $id=null) {
			$buff = implode(', ', array_map(function($field, $value) {
				return ' '.$field.' = '.$value.' ';
			}, $fields, $values));
			$query = "UPDATE ".$tableName." SET".$buff + $tail;
			$tail = ($id == null)? ";": "WHRER id=".$id.";";
			return static::$basePointer->exec($query);
			// return static::$basePointer->prepare($query)->execute($row);
		}

		function createTable($tableName, $info) {
			$buff = "";
			foreach ($info['columns'] as $column) {
				$buff .= $column['name']." ".$column['type']." ".$column['mods'].","; 
			}
			$buff = substr($buff, 0, strlen($buff)-1);
			
			$query = "CREATE TABLE IF NOT EXISTS ".$tableName." (".
				$buff.
			");";

			static::$basePointer->exec($query);
		}
	}

?>