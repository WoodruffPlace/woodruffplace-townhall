<?php
class User
{
	// Class variables
	private $userID;
	private $name_first;
	private $name_last;
	private $email;
	private $can_login;
	private $is_admin;
	private $db;

	// Constructor
	function __construct($userID, $db = NULL)
	{
		if ($db == null)
		{
			global $db;
			$this->db = $db;
		}
		$this->userID = $userID;
		try
		{
			/* Populate object values */
			$vars = $this->user_setinfo();
			if (isset($vars))
			{
				$this->name_first					= $vars[0]['name_first'];
				$this->name_last					= $vars[0]['name_last'];
				$this->email						= $vars[0]['email'];
				$this->can_login					= $vars[0]['can_login'];
				$this->is_admin						= $vars[0]['is_admin'];
			}
		}
		catch (Exception $e)
		{
			throw new Exception( 'Error: class: "User" requires valid User ID', 0, $e);
		}
	}

	/**
	 *  Provide a business' info attributes
	 */
	function user_get_attribute($attribute = NULL)
	{
		switch ($attribute)
		{
			case 'userID':
				if (isset($this->userID)){ return $this->userID; }
			break;
			case 'name_first':
			case 'first_name':
				if (isset($this->name_first)){ return $this->name_first; }
			break;
			case 'name_last':
			case 'last_name':
				if (isset($this->name_last)){ return $this->name_last; }
			break;
			case 'email':
				if (isset($this->email)){ return $this->email; }
			break;
			case 'can_login':
				if (isset($this->can_login)){ return $this->can_login; }
			break;
			case 'is_admin':
				return (self::user_is_admin($this->userID)) ? TRUE : FALSE;
			break;
		}
	}

	/**
	 *  Sets this object's info from the data store
	 */
	function user_setinfo()
	{
		global $db;
		if (!empty($this->userID))
		{
			try
			{
				$query = "SELECT * FROM `users` WHERE userID = '".$this->userID."'";
				$result = $db->fetch_assoc($db->query($query));
				if (!empty($result))
				{
					return $result;
				}
				else
				{
					throw new Exception( 'Error: class: "User" requires valid User ID');
				}
			}
			catch (Exception $e)
			{
				throw new Exception( 'Error: class: "User" requires valid User ID', 0, $e);
			}
		}
	}

	/**
	 *  Updates columns in the db for this user
	 */
	function user_update($column, $value)
	{
		global $db;
		$query = "UPDATE users SET ".$column." = '".$value."' WHERE userID = '".$this->userID."'";
		$result = $db->query($query);
		// Reset the object
		$this->user_setinfo();
		return $result;
	}

	// Set the flag to invalidate a user's login key
	function user_invalidate_loginkey()
	{
		// Valid login - update the db and finish the login
		$query = "UPDATE users SET login_key_used = 1 WHERE userID = ?";
		// Assign types
		$types = "i";
		// Execute query
		$data = $this->db->safe_insert($query, $types, $this->userID);
	}


	/* * * * * * * * * * * * * * *
	 *
	 *  Class/static methods
	 *
	 * * * * * * * * * * * * * * */



	/**
	 *  Returns whether a user exists based on email address
	 *  If true, returns the User ID. If not, returns FALSE.
	 */
	static function user_email_exists($email)
	{
		global $db;
		$data = $db->safe_query("SELECT userID FROM users WHERE email = ?", "s", trim($email));
		return (!empty($data[0]['userID']) && is_int($data[0]['userID'])) ? $data[0]['userID'] : FALSE;
	}


	// Whether an email address is valid for login. If so, returns User ID
	static function user_can_login($email)
	{
		global $db;
		$data = $db->safe_query("SELECT * FROM users WHERE email = ? AND can_login = 1", "s", trim($email));
		if (!empty($data))
		{
			return $data[0]['userID'];
		}
		else
		{
			return FALSE;
		}
	}


	// Returns a boolean to indicate whether this user is an admin
	public static function user_is_admin($userID)
	{
		$user = new User($userID);
		return ($user->is_admin == 1) ? TRUE : FALSE;
	}

} // end class
