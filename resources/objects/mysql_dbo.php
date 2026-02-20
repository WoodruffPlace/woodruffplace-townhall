<?php
function mysqlDB()
{
	global $config;
	$db = new mysql(
					$config['db']['db1']['host'],
					$config['db']['db1']['port'],
					$config['db']['db1']['username'],
					$config['db']['db1']['password'],
					$config['db']['db1']['dbname']
					);
	$db->connect();
	$db->select();
	return $db;
}
$db = mysqlDB();
?>
