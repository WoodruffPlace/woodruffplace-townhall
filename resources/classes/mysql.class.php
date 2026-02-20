<?php

class mysql {
	private $linkid;		// MySQL link identifier
	private $host;			// MySQL Host
	private $port;			// MySQL Port
	private $user;			// MySQL User
	private $pass;			// Mysql Password
	private $db;			// Mysql Database
	private $result;		// Query Result
	private $querycount;	// Total queries executed

	/* Constructor */
	function __construct($host, $port, $user, $pass, $db){
		$this->host = $host;
		$this->port = $port;
		$this->user = $user;
		$this->pass = $pass;
		$this->db = $db;
	}

	/* Connects to the Database Server */
	function connect(){
		try {
			$this->linkid = mysqli_connect($this->host,$this->user,$this->pass,$this->db,$this->port);
			if (! $this->linkid)
				throw new Exception("Server is unavailable, or is undergoing maintenance.");
		}
		catch (Exception $e) {
			die($e->getMessage());
		} // catch
	} // connect()

	/* Select your Database */
	function select(){
		try {
			if (! mysqli_select_db($this->linkid,$this->db))
				throw new Exception ("Could not connect to the MySQL Database!");
		}
		catch (Exception $e) {
			die ($e->getMessage());
		} // catch
	} // select()

	/* Execute Query */
	function query ($query) {
		try {
			//$this->result = mysqli_query($this->linkid,$query);
			$this->result = mysqli_query($this->linkid,$query);
			if(! $this->result)
				throw new Exception("The database query failed: " . mysqli_error($this->linkid) . '--' .'QUERY: '.$query);
		}
		catch (Exception $e) {
			echo ($e->getMessage());
		}
		$this->querycount++;
		return $this->result;
	} // query ()


	// function safe_query() {
	// 	$args = func_get_args();
	// 	$query = array_shift($args);
	// 	$args = @array_map("mysql_real_escape_string",(is_array($args[0]) ? $args[0] : $args));
	// 	$squery = vsprintf($query,$args);
	// 	$this->querycount++;
	// 	return $this->query($squery);
	// } // safe_query()

	/**
	 *  Utilize a parameterized query to make safe queries to the db
	 */
	function safe_query($query, $param_types, ...$params)
	{
		$stmt = $this->linkid->prepare($query);
		$stmt->bind_param($param_types, ...$params);
		$stmt->execute();
		$result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
		return $result;
	}

	/**
	 *  Utilize a parameterized query to make safe queries that don't return (like DELETE)
	 */
	function safe_execute($query, $param_types, ...$params)
	{
		$stmt = $this->linkid->prepare($query);
		$stmt->bind_param($param_types, ...$params);
		$stmt->execute();
	}

	/**
	 *  Parameterized INSERT query
	 */
	function safe_insert($query, $param_types, ...$params)
	{
		$stmt = $this->linkid->prepare($query);
		$stmt->bind_param($param_types, ...$params);
		$stmt->execute();
	}


	function mysqli_real_escape_string($string)
	{
		return mysqli_real_escape_string($this->linkid, $string);
	}

	// Tests for the existence of a table.
	function tableExists($table) {
		try {
			$test = @mysql_query("SELECT * FROM $table LIMIT 1", $this->linkid);
			if (!$test)
				throw new Exception("Table does not exist");
		}
		catch (Exception $e)
		{ return false; }

		return true;
	}

	/* Determine the total rows affected by the last query */
	function num_rows(){
		$count = mysqli_num_rows($this->result);
		return $count;
	} // num_rows()

	/* Return query result row as an object */
	function fetch_object() {
		$row = mysqli_fetch_object($this->result);
		return $row;
	} // fetch_object()

	/* Return query result row as an indexed array */
	function fetch_row() {
		$row = mysqli_fetch_row($this->result);
		return $row;
	} // fetch_row()

	/* Return query results as an associative array */
	function fetch_array() {
		$row = mysqli_fetch_array($this->result, MYSQLI_BOTH);
		return $row;
	} // fetch_array

	/* Return query results as an associative array */
	function fetch_assoc() {
		$row = Array();
			for ($i=0;$i<$this->num_rows();$i++){
				$row[$i] = mysqli_fetch_assoc($this->result);
			}
		return $row;
		// $row = mysqli_fetch_assoc($this->result);
		// return $row;
	}

	/* Return total number of queries executed during the lifetime of this object */
	function num_queries() {
		return $this->querycount;
	} // num_queries

	/* Returns the last ID by auto_increment */
	function last_id(){
		return mysqli_insert_id($this->linkid);
	} // last_id()

	/* Change charset -- useful when strange characters appearing on query results */
	function charset($which = "utf8"){
		mysql_query("SET NAMES '" . $which . "'", $this->linkid);
	}

	function print_row() {
		$aryRow = array();
		echo "<pre>";
		while ($aryRow = $this->fetch_row())
			print_r($aryRow);
		echo "</pre>";
	}

	/* Print Rows - send a formatted string */
	function printf($output_string) {
		$aryRow = array();
		while ($aryRow = $this->fetch_array(MYSQL_ASSOC)):
			vprintf($output_string, $aryRow);
		endwhile;
	}

	/* S-Print Rows - send a formatted string, returns an array of strings */
	function sprintf($output_string) {
		$aryRow = array();
		$aryOutput = array();

		while ($aryRow = $this->fetch_row()):
			array_push($aryOutput, vsprintf($output_string, $aryRow));
		endwhile;
		return $aryOutput;
	}

} // mysql class

?>
