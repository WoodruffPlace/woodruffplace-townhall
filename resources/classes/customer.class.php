<?php
class Customer
{
	// Class variables
	private $customerID;
	private $name_first;
	private $name_last;
	private $email;
	private $phone;
	private $organization;
	private $stripe_customer_id;
	private $db;

	// Constructor
	function __construct($customerID, $db = NULL)
	{
		if ($db == null)
		{
			global $db;
			$this->db = $db;
		}
		$this->customerID = $customerID;
		try
		{
			/* Populate object values */
			$vars = $this->customer_setinfo();
			if (isset($vars))
			{
				$this->name_first			= $vars[0]['name_first'];
				$this->name_last			= $vars[0]['name_last'];
				$this->email				= $vars[0]['email'];
				$this->phone				= $vars[0]['phone'];
				$this->organization			= $vars[0]['organization'];
				$this->stripe_customer_id	= $vars[0]['stripe_customer_id'];
			}
		}
		catch (Exception $e)
		{
			throw new Exception( 'Error: class: "Customer" requires valid Customer ID', 0, $e);
		}
	}

	/**
	 *  Provide a business' info attributes
	 */
	function customer_get($attribute = NULL)
	{
		switch ($attribute)
		{
			case 'customerID':
				if (isset($this->customerID)){ return $this->customerID; }
			break;
			case 'name_first':
				if (isset($this->name_first)){ return $this->name_first; }
			break;
			case 'name_last':
				if (isset($this->name_last)){ return $this->name_last; }
			break;
			case 'email':
				if (isset($this->email)){ return $this->email; }
			break;
			case 'phone':
				if (isset($this->phone)){ return $this->phone; }
			break;
			case 'organization':
				if (isset($this->organization)){ return $this->organization; }
			break;
			case 'stripe_customer_id':
				if (isset($this->stripe_customer_id)){ return $this->stripe_customer_id; }
			break;

		}
	}

	/**
	 *  Sets this object's info from the data store
	 */
	function customer_setinfo()
	{
		global $db;
		if (!empty($this->customerID))
		{
			try
			{
				$query = "SELECT * FROM customers WHERE customerID = '".$this->customerID."'";
				$result = $db->fetch_assoc($db->query($query));
				if (!empty($result))
				{
					return $result;
				}
				else
				{
					throw new Exception( 'Error: class: "Customer" requires valid Customer ID');
				}
			}
			catch (Exception $e)
			{
				throw new Exception( 'Error: class: "Customer" requires valid Customer ID', 0, $e);
			}
		}
	}

	/**
	 *  Updates columns in the db for this user
	 */
	function customer_update($column, $value)
	{
		global $db;
		$query = "UPDATE customers SET ".$column." = '".$value."' WHERE customerID = '".$this->customerID."'";
		$result = $db->query($query);
		// Reset the object
		$this->customer_setinfo();
		return $result;
	}

	/**
	 *
	 *
	 *  Serve as a master function for the next few:
	 *    + Lookup for a local Stripe ID
	 *    + If not, look for a remote Stripe ID
	 *      + If so, update the local stripe_id and return the ID
	 *      + If not, create a new customer and return the new ID
	 *        + Update the local ID while we're at it
	 *
	 */

	function customer_stripe_id_lookup_process()
	{
		// Do we have a local Stripe customer_id
		if (!empty($this->stripe_customer_id))
		{
			return $this->stripe_customer_id;
		}
		else
		{
			// No local ID. Do we have a remote Stripe customer_id?
			$id = self::customer_has_remote_stripe_customer_id($this->email);
			if ($id != FALSE)
			{
				return $id;
			}
			// No. Create the customer in Stripe
			else
			{
				$customer = self::customer_stripe_create($this->name_first . ' ' . $this->name_last, $this->email);
				if (!empty($customer) && $customer != FALSE)
				{
					// Update the local customer with the new Stripe customer_id
					$this->customer_update('stripe_customer_id', $customer->id);
					return $customer->id;
				}
			}
		}
	}

	/**
	 *  Updates Stripe if any customer info has changed
	 */
	function update_stripe_if_updated($customer_info_new)
	{
		$update = false;
		if (
			($customer_info_new['name_first']	!= $this->name_first) ||
			($customer_info_new['name_last']	!= $this->name_last) ||
			($customer_info_new['email']		!= $this->email) ||
			($customer_info_new['phone']		!= $this->phone)
			)
		{
			// Build the update array
			$update = Array(
				'name' => $customer_info_new['name_first'] . ' ' . $customer_info_new['name_last'],
				'email' => $customer_info_new['email'],
				'phone' => $customer_info_new['phone']
			);

			// Instantiate a new Stripe object
			$stripe = new \Stripe\StripeClient($GLOBALS['config']['stripe_api_key']);

			// Update Stripe
			$stripe_process = StripeInterface::stripe_customer_update($this->stripe_customer_id, $update);
			if ($stripe_process == FALSE)
			{
				return FALSE;
			}
			else
			{
				return TRUE;
			}
		}






	}


	/* * * * * * * * * * * * * * *
	 *
	 *  Class/static methods
	 *
	 * * * * * * * * * * * * * * */

	// Create a new customer
	public static function customer_create($name_first, $name_last, $email, $phone, $organization)
	{
		global $db;
		$data = $db->safe_insert(
		"INSERT INTO customers (name_first, name_last, email, phone, organization)
		VALUES (?,?,?,?,?)",'sssss',
		$name_first, $name_last, $email, $phone, $organization);
		if (!empty($db->last_id()))
		{
			return $db->last_id();
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 *  Returns whether a customer exists based on email address
	 *  If true, returns the ID. If not, returns FALSE.
	 */
	static function customer_email_exists($email)
	{
		global $db;
		$data = $db->safe_query("SELECT customerID FROM customers WHERE email = ?", "s", $email);
		return (!empty($data[0]['customerID']) && is_int($data[0]['customerID'])) ? $data[0]['customerID'] : FALSE;
	}


	// Combine the local and remote lookup
	static function customer_has_stripe_customer_id($email)
	{
		$id = self::customer_has_local_stripe_customer_id($email);
		if ($id == FALSE)
		{
			$id = self::customer_has_remote_stripe_customer_id($email);
		}
		return ($id != FALSE) ? $id : FALSE;
	}

	// Looks up email address to see if valid user and has a Stripe customer_id
	static function customer_has_local_stripe_customer_id($email)
	{
		global $db;
		$data = $db->safe_query("SELECT stripe_customer_id FROM customers WHERE email = ?", "s", $email);
		return (!empty($data[0]['stripe_customer_id'])) ? $data[0]['stripe_customer_id'] : false;
	}

	// Check Stripe to see if the user has a customer_id in Stripe
	static function customer_has_remote_stripe_customer_id($email)
	{
		// Instantiate a new Stripe object
		$stripe = new \Stripe\StripeClient($GLOBALS['config']['stripe_api_key']);

		// Use the customers/search endpoint
		$customers = $stripe->customers->search(['query' => 'email:"'.$email.'"']);

		// User exists in Stripe -- there might be others...just use the first one
		return !empty($customers->data[0]['id']) ? $customers->data[0]['id'] : FALSE;
	}

	/**
	 *  Create a new Stripe customer if no customer_id exists
	 */
	static function customer_stripe_create($name, $email)
	{
		// Instantiate a new Stripe object
		$stripe = new \Stripe\StripeClient($GLOBALS['config']['stripe_api_key']);

		// Use the customers/search endpoint
		$customer = $stripe->customers->create(['name' => $name, 'email' => $email]);

		// User exists in Stripe -- there might be others...just use the first one
		return $customer;
	}

	/**
	 *  Update a local user to populate the Stripe customer_id
	 */
	static function customer_update_stripe_id($customerID, $stripe_customer_id)
	{
		$customer = new Customer($customerID);
		$customer->customer_update('stripe_customer_id', $stripe_customer_id);
	}

} // end class
