<?php
class Webhook
{
	// Class variables
	private $db;

	// Constructor
	function __construct($db = NULL)
	{
		if ($db == null)
		{
			global $db;
			$this->db = $db;
		}
	}

}
