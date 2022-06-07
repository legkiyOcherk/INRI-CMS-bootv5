<?
/**
 * @author Mayer Roman (majer.rv@gmail.com)
 */

/*global $pdo;
$pdo = db_open();*/

class db {
	static function connect($host, $login, $password, $db_name, $charset = null) {
		$charset="utf8";
		$link = @mysql_connect($host, $login, $password);
		if ( $link && mysql_select_db($db_name)) {
			if ($charset) mysql_query("SET CHARACTER SET $charset");
			// if ($charset) mysql_query("SET NAMES $charset");
			return $link;
		} else {
			echo mysql_error();
			return false;
		}
	}

	static $prefix = "";

	static function disconnect($link) {
		mysql_close($link);
	}

	static function q($sql) {
		//		$res = mysql_query($sql);
		$res = self::get_query($sql);
		return $res;
	}

	static function arr($sql) {
    global $PDO;
		#$res = mysql_query($sql);
    
    $res = $PDO->query($sql);
		if ($res) {
			$output = array();
			#while ($rows = mysql_fetch_array($res, MYSQL_ASSOC)) {
      while ($rows = $res->fetch()) {
				$output[] = $rows;
			}
			return $output;
		}
		return false;
	}

	static function select($fields, $table, $where = null, $orderby = null, $limit = null, $one_row = null, $debug = false) {
		$table = self::$prefix.$table;
		//		$where = addslashes($where);
		$sql = "SELECT $fields FROM $table".( ($where) ? " WHERE $where" : '').(!is_null($orderby) ? " ORDER BY $orderby" : '').(!is_null($limit) ? " LIMIT $limit" : '');
    #echo "sql = $sql";
    if ($debug) return $sql;
		$arr = self::arr($sql);
		if(is_null($one_row)){
      return $arr;
    }else{
      if(isset($arr[0]))
        return $arr[0];
    }
	}


	static function row($fields, $table, $where = null, $orderby = null, $debug = false) {
		return self::select($fields, $table, $where, $orderby, 1, 1, $debug);
	}



	static function value($field, $table, $where, $debug = false) {
		$value = self::select($field, $table, $where, null, 1, 1, $debug);
		if ($debug) return $value;
		return (isset($value[$field])) ? $value[$field] : false;
	}


	static function insert($table, $data = array(), $debug = false) {
    global $PDO;
		if(!count($data)) return false;
		$table = self::$prefix.$table;
    $fields = $values = '';
		foreach ($data as $f => $v) {
			$fields .= "`$f`, ";
			if (!ctype_digit($v)) {
				if (is_null($v)) {
					$v = "NULL";
				} else {
					$v = "'$v'";
				}
			}
			$values .= "$v, ";
		}
		$sql = "INSERT INTO `$table` (".substr($fields, 0, -2).") VALUES (".substr($values, 0, -2).") ";
		#print $sql;
		if ($debug) return $sql;
		$res = self::get_query($sql);
		#return ($res) ? mysql_insert_id() : false;
    return ($res) ? $PDO->lastInsertId() : false;
	}

	static function update($table, $data = array(), $where, $limit = 1, $debug = false) {
		if(!count($data)) return false;
		$table = self::$prefix.$table;
    $condition = '';
		foreach ($data as $f => $v) {
			if (!ctype_digit($v)) {
				if (is_null($v)) {
					$v = "NULL";
				} else {
					$v = "'$v'";
				}
			}
			$condition .= "`$f`=$v, ";
		}
		$limit = ($limit) ? "LIMIT $limit" : '';
		if ($where) $where = "WHERE $where";
		$sql = "UPDATE `$table` SET ".substr($condition, 0, -2)." $where $limit";
		//	$res = mysql_query($sql);
		$res = self::get_query($sql);
		if ($debug)	return $sql;
		return $res;
	}

	static function delete($table, $where, $limit = 1, $debug = false) {
		if (!$where) return false;
		$table = self::$prefix.$table;
		if ($limit) $limit = " LIMIT $limit";
		$sql = "DELETE FROM $table WHERE $where $limit";
		if ($debug)	return $sql;
		$res = self::get_query($sql);
		return $res;
	}


	static private function get_query($sql) {
		#$res = @mysql_query($sql);
    
    global $PDO;
    $res = $PDO->query($sql);
    if (($res === false) and (class_exists(Log))) {
			Log::write(__CLASS__, mysql_error());
		}
		return $res;
	}

}
?>