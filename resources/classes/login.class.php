<?php
class Login
{
	// Class variables
	private $db;
	private $email;
	private $mailer;

	// Constructor
	function __construct($email, $mailer, $db = NULL)
	{
		$this->email = $email;
		$this->mailer = $mailer;
		if ($db == null)
		{
			global $db;
			$this->db = $db;
		}
	}

	function login_begin()
	{
		global $config;
		global $mail;
		if ($userID = User::user_can_login($this->email))
		{
			// Create the login key and link
			$login_key = $this->login_create_key($userID);

			// Create the login link
			$link = $this->login_create_link($userID, $login_key);

			// Construct message
			$message = Messages::user_login($link);

			// Send the email to the user
			if (Utility::mailer_helper($mail, $this->email, "Your requested login link", $message))
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			// Return true for "security" -- basically, to not "give away" whether a user can login
			return TRUE;
		}
	}

	// Create the one-time login link
	function login_create_key($userID)
	{
		$length = 50;
		$key = (string)bin2hex(random_bytes(ceil($length / 2)));

		// Store a hashed version of the key. Return it in plain text to return to the user.
		$query = "UPDATE users SET login_key = ?, login_key_created = NOW(), login_key_used = 0 WHERE userID = ?";

		// Assign types
		$types = "si";

		// Execute query
		$data = $this->db->safe_insert($query, $types, password_hash($key, PASSWORD_DEFAULT), $userID);

		// Return the key
		return $key;
	}

	// Create the login link
	function login_create_link($userID, $key)
	{
		// Precede a key with a fixed 3-digit user ID -- forcing leading zeros if necessary
		$user_salt = sprintf('%03d', $userID);

		return (string)$GLOBALS['env']['protocol'] . "://" . $GLOBALS['env']['host'] . "/login/auth/" . $user_salt . $key;
	}

	/* * * * * * * * * * * * * * *
	 *
	 *  Class/static methods
	 *
	 * * * * * * * * * * * * * * */

	// Log a user in
	static function login_user_login($user)
	{
		$_SESSION['authenticated'] = TRUE;
		$_SESSION['userID'] = $user;
	}

	static function login_is_authenticated()
	{
		return (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] == TRUE) ? TRUE : FALSE;
	}

	// Evaluate a login key
	static function login_process($key)
	{
		// If it's not a valid length, reject immediately
		if (strlen($key) != 50 && strlen($key) != 53)
		{
			return false;
		}
		else
		{
			// The first three digits should be the userID -- remove any leading zeros
			// Split the key into the userID and the key
			$userID = ltrim(substr($key, 0, 3), '0');
			$key = substr($key, 3);

			global $db;

			// Get the data. Login key must have been created within the past 10 mins to be considered valid.
			$data = $db->safe_query("SELECT userID, login_key FROM users WHERE userID = ? AND login_key_used = 0 AND login_key_created >= NOW() - INTERVAL 10 MINUTE", "i", $userID);
			if (empty($data))
			{
				return FALSE;
			}
			else
			{
				// Does the hash/stored login_key match the currently supplied key
				if (password_verify($key, $data[0]['login_key']))
				{
					$user = new User($data[0]['userID']);

					// Invalidate the user's login key
					$user->user_invalidate_loginkey();

					// Return the user
					return $user;
				}
			}
		}
	}

// end class
}
