<?php
class Log
{
	// Class variables
	private $db;
	private $message;

	/**
	 *  Create / insert a new log record
	 */
	static function log($message)
	{
		if (!empty($message))
		{
			$data = $db->safe_insert("INSERT INTO `logs` (message) VALUES (?)",'s', $message);
			if (!empty($db->last_id()))
			{
				$return = $db->last_id();
			}
		}
		return $return;
	}
}
