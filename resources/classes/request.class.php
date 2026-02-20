<?php
class Request
{
	// Set class variables
	private $db;
	private $requestID;
	private $title;
	private $customer;
	private $wpcl_member;
	private $is_wp_event;
	private $sponsor_name_first;
	private $sponsor_name_last;
	private $sponsor_email;
	private $sponsor_phone;
	private $security_deposit;
	private $cleaning_fee;
	private $notes_event;
	private $notes_fee_waiver;
	private $fee_waiver_cleaning;
	private $fee_waiver_security;
	private $status;

	// Constructor

	function __construct($requestID, $db = NULL)
	{
		if ($db == null)
		{
			global $db;
			$this->db = $db;
		}
		$this->requestID = $requestID;
		try
		{
			// Populate object values
			$vars = $this->setinfo();
			if (isset($vars))
			{
				$this->title				= $vars[0]['title'];
				$this->customer				= $vars[0]['customerID'];
				$this->wpcl_member			= $vars[0]['customer_is_wpcl_member'];
				$this->sponsor_name_first	= $vars[0]['wpcl_sponsor_name_first'];
				$this->sponsor_name_last	= $vars[0]['wpcl_sponsor_name_last'];
				$this->sponsor_email		= $vars[0]['wpcl_sponsor_email'];
				$this->sponsor_phone		= $vars[0]['wpcl_sponsor_phone'];
				$this->is_wp_event			= $vars[0]['is_wp_event'];
				$this->security_deposit		= $vars[0]['security_deposit'];
				$this->cleaning_fee			= $vars[0]['cleaning_fee'];
				$this->notes_event			= $vars[0]['notes_event'];
				$this->notes_fee_waiver		= $vars[0]['notes_fee_waiver'];
				$this->fee_waiver_cleaning	= $vars[0]['fee_waiver_cleaning'];
				$this->fee_waiver_security	= $vars[0]['fee_waiver_security'];
				$this->status				= $vars[0]['status'];
			}
		}
		catch (Exception $e)
		{
			throw new Exception( 'Error: class: "Request" requires valid Request ID', 0, $e);
		}
	}

	/**
	 *  Provide a request's info attributes
	 */
	function request_get($attribute = NULL)
	{
		switch ($attribute)
		{
			case 'requestID':
				if (isset($this->requestID)){ return $this->requestID; }
			break;
			case 'title':
				if (isset($this->title)){ return $this->title; }
			break;
			case 'customer':
				if (isset($this->customer)){ return $this->customer; }
			break;
			case 'wpcl_member':
				if (isset($this->wpcl_member)){ return $this->wpcl_member; }
			break;
			case 'wpcl_sponsor':
				$sponsor = Array();
				$sponsor['name_first']	= (!empty($this->sponsor_name_first)) ? $this->sponsor_name_first : '';
				$sponsor['name_last']	= (!empty($this->sponsor_name_last)) ? $this->sponsor_name_last : '';
				$sponsor['email']		= (!empty($this->sponsor_email)) ? $this->sponsor_email : '';
				$sponsor['phone']		= (!empty($this->sponsor_phone)) ? $this->sponsor_phone : '';
				return $sponsor;
			break;
			case 'is_wp_event':
				if (isset($this->is_wp_event)){ return $this->is_wp_event; }
			break;
			case 'security_deposit':
				if (isset($this->security_deposit)){ return $this->security_deposit; }
			break;
			case 'cleaning_fee':
				if (isset($this->cleaning_fee)){ return $this->cleaning_fee; }
			break;
			case 'notes_event':
				if (isset($this->notes_event)){ return $this->notes_event; }
			break;
			case 'notes_fee_waiver':
				if (isset($this->notes_fee_waiver)){ return $this->notes_fee_waiver; }
			break;
			case 'status':
				if (isset($this->status)){ return $this->status; }
			break;
		}
	}

	/**
	 *  Get the event sessions tied to a particular request
	 */
	function request_get_sessions()
	{
		$query = "SELECT eventID FROM event_sessions WHERE requestID = '".$this->requestID."' ORDER BY event_start ASC";
		$result = $this->db->fetch_assoc($this->db->query($query));
		if (!empty($result))
		{
			$return = [];
			// Loop and fill
			foreach ($result as $row)
			{
				$return[] = $row['eventID'];
			}
			return $return;
		}
	}


	/**
	 *  Get the total cost for this request
	 */
	function request_get_total_cost()
	{
		$query =
		"SELECT
		SUM(amount)
		FROM
		(
		SELECT amount FROM prices
		JOIN event_sessions ON event_sessions.rental_fee = prices.priceID
		WHERE event_sessions.fee_waiver_rental = 0 AND requestID = '".$this->requestID."'
		UNION ALL
		SELECT amount FROM prices
		JOIN event_sessions ON event_sessions.alcohol_fee = prices.priceID
		WHERE event_sessions.fee_waiver_alcohol = 0 AND requestID = '".$this->requestID."'
		UNION ALL
		SELECT amount FROM prices
		JOIN requests ON requests.cleaning_fee = prices.priceID
		WHERE requests.fee_waiver_cleaning = 0 AND requestID = '".$this->requestID."'
		UNION ALL
		SELECT amount FROM prices
		JOIN requests ON requests.security_deposit = prices.priceID
		WHERE requests.fee_waiver_security = 0 AND requestID = '".$this->requestID."'
		) AS sum_total;";

		$result = $this->db->fetch_row($this->db->query($query));
		if (!empty($result))
		{
			return $result[0];
		}
	}

	/**
	 *  Automatically waive all fees for WP events
	 */
	function request_process_waivers()
	{
		// Determine the waiver for all fees
		$fee_waiver_setting = ($this->is_wp_event == "1") ? "1" : "0";
		// Process waiver for request + sessions
		$this->request_update('status', $status);
	}


	/**
	 *  Performs a quick status update
	 */
	function status_update($status)
	{
		if (self::status_is_valid($status))
		{
			$this->request_update('status', $status);
		}
	}

	/**
	 *  Returns whether or not specific fees are waived
	 */
	function fee_waived($fee)
	{
		switch ($fee)
		{
			case 'cleaning':
				return ($this->fee_waiver_cleaning == "1") ? true : false;
			break;
			case 'security':
				return ($this->fee_waiver_security == "1") ? true : false;
			break;
		}
	}

	/**
	 *  Returns whether this request is editable (basic information)
	 */
	function is_editable()
	{
		$return = false;
		switch ($this->status)
		{
			case 'initiated':
			case 'review':
			case 'approved':
			case 'scheduled':
			case 'in_progress':
			case 'expired':
				$return = true;
		}
		return $return;
	}

	/**
	 *  Returns whether this request is editable (basic information)
	 */
	function is_prepayment()
	{
		$return = false;
		switch ($this->status)
		{
			case 'initiated':
			case 'review':
				$return = true;
		}
		return $return;
	}


	/**
	 *  Returns whether or not this request's status can be changed
	 */
	function is_status_editable()
	{
		$return = false;
		switch ($this->status)
		{
			case 'initiated':
			case 'review':
			//case 'approved':
				$return = true;
		}
		return $return;
	}

	/**
	 *  Returns whether or not this request's sessions can be edited
	 */
	function is_sessions_editable()
	{
		$return = false;
		switch ($this->status)
		{
			case 'initiated':
			case 'review':
			//case 'approved':
				$return = true;
		}
		return $return;
	}

	/**
	 *  Returns whether or not this request is actionable (should show the menu)
	 */
	function is_actionable()
	{
		$return = false;
		switch ($this->status)
		{
			case 'initiated':
			case 'review':
			case 'approved':
			case 'scheduled':
			case 'in_progress':
			case 'expired':
			case 'ended_pending':
				$return = true;
		}
		return $return;
	}

	/**
	 *  Request can be cancelled only after it is approved and paid
	 */
	function is_cancelable()
	{
		$return = false;
		switch ($this->status)
		{
			case 'approved':
			case 'scheduled':
			case 'in_progress':
			case 'ended_abandoned':
				$return = true;
		}
		return $return;
	}

	/**
	 *  Request can be cancelled only after it is approved and paid
	 */
	function is_notes_editable()
	{
		$return = true;
		switch ($this->status)
		{
			case 'complete_deposit_refunded':
			case 'complete_deposit_retained':
				$return = false;
		}
		return $return;
	}


	/**
	 *  Returns the status as a pretty-printed label
	 */
	function status_display()
	{
		$return = Array();
		switch ($this->status)
		{
			case 'initiated':
				$return['status'] = 'Initiated';
				$return['color-text'] = 'text-primary';
				$return['color-btn'] = 'btn-primary';
			break;
			case 'review':
				$return['status'] = 'In review';
				$return['color-text'] = 'text-info';
				$return['color-btn'] = 'btn-primary';
			break;
			case 'denied':
				$return['status'] = 'Denied';
				$return['color-text'] = 'text-danger';
				$return['color-btn'] = 'btn-danger';
			break;
			case 'approved':
				$return['status'] = 'Approved';
				$return['status_long'] = 'Approved';
				$return['color-text'] = 'text-success';
				$return['color-btn'] = 'btn-success';
			break;
			case 'scheduled':
				$return['status'] = 'Scheduled';
				$return['color-text'] = 'text-success text-opacity-75';
				$return['color-pill'] = 'text-success';
				$return['color-btn'] = 'btn-success';
			break;
			case 'in_progress':
				$return['status'] = 'In progress';
				$return['color-text'] = 'text-success';
				$return['color-btn'] = 'btn-success';
			break;
			case 'ended_pending':
				$return['status'] = 'Ended, inspection pending';
				$return['color-text'] = 'text-warning';
				$return['color-btn'] = 'btn-warning';
			break;
			case 'ended_abandoned':
				$return['status'] = 'Ended. No checklist submitted.';
				$return['color-text'] = 'text-primary text-opacity-50';
				$return['color-btn'] = 'btn-secondary';
			break;
			case 'complete_deposit_refunded':
				$return['status'] = 'Complete';
				$return['color-text'] = 'text-primary text-opacity-50';
				$return['color-btn'] = 'btn-secondary';
			break;
			case 'complete_deposit_retained':
				$return['status'] = 'Complete (deposit retained)';
				$return['color-text'] = 'text-primary text-opacity-50';
				$return['color-btn'] = 'btn-secondary';
			break;
			default:
				$return['status'] = ucfirst($this->status);
				$return['color-text'] = 'text-primary text-opacity-50';
		}
		return $return;
	}

	/**
	 *  Sets this object's info from the data store
	 */
	function setinfo()
	{
		global $db;
		if (!empty($this->requestID))
		{
			try
			{
				$query = "SELECT * FROM requests WHERE requestID = '".$this->requestID."'";
				$result = $db->fetch_assoc($db->query($query));
				if (!empty($result))
				{
					return $result;
				}
				else
				{
					throw new Exception( 'Error: class: "Request" requires valid Request ID');
				}
			}
			catch (Exception $e)
			{
				throw new Exception( 'Error: class: "Request" requires valid Request ID', 0, $e);
			}
		}
	}

	/**
	 *  Process the approval for an internal event
	 */
	function request_process_internal_approval($new_status)
	{
		global $mail;
		// Internal approvals are easy. Flip the status and notify the requestor.
		$this->status_update($new_status);
		// Notify the requestor
		$requestor = new Customer($this->request_get('customer'));
		$message = Messages::customer_internal_request_approval($this);
		Utility::mailer_helper($mail, $requestor->customer_get('email'), "Town Hall request approved - " . $this->request_get('title'), $message, 'Woodruff Place Town Hall');
	}

	/**
	 *  Process the DENIAL for an internal event
	 */
	function request_process_internal_denial()
	{
		global $mail;
		// Internal approvals are easy. Flip the status and notify the requestor.
		$this->status_update('denied');
		// Notify the requestor
		$requestor = new Customer($this->request_get('customer'));
		$message = Messages::customer_internal_request_denial($this);
		Utility::mailer_helper($mail, $requestor->customer_get('email'), "Town Hall request declined - " . $this->request_get('title'), $message, 'Woodruff Place Town Hall');
	}


	/**
	 *  Encapsulate the process of creating a new invoice and adding items
	 *  Note: applies only to external events
	 */
	function request_initiate_invoice()
	{
		// Set an error flag
		$valid = true;

		// We need a customer in order to create a new invoice
		$customer = new Customer($this->request_get('customer'));

		// Create the invoice -- pass the customer object
		if (!$invoice = StripeInterface::stripe_invoice_create($customer, $this->request_get('title')))
		{
			$valid = false;
		}

		// Recreate the customer object to ensure we have the stripe_customer_id
		unset($customer);
		$customer = new Customer($this->request_get('customer'));

		// Create a new local invoice record
		if (!Invoice::invoice_create($this->request_get('requestID'), $invoice->id, $invoice->status))
		{
			$valid = false;
		}

		// Instantiate the prices array
		$invoice_items = Array();

		/**
		 *  Cleaning fee
		 */
		// Add the cleaning fee
		$fee_cleaning = new Price($this->request_get('cleaning_fee'));

		// Set whether the cleaning fee is waived
		$fee_cleaning_discount = ($this->fee_waived('cleaning') == TRUE) ? $GLOBALS['settings']->get('invoice.discount.100') : false;

		// Push to the array
		array_push($invoice_items, Array('price' => $fee_cleaning->price_get('stripe_price_id'), 'discount' => $fee_cleaning_discount, 'description' => $fee_cleaning->price_get('description_invoice')));

		/**
		 *  Security deposit
		 */

		// Add the security deposit
		$fee_security_deposit = new Price($this->request_get('security_deposit'));

		// Set whether the security deposit is waived
		$fee_security_deposit_discount = ($this->fee_waived('security') == TRUE) ? $GLOBALS['settings']->get('invoice.discount.100') : false;

		// Push to the array
		array_push($invoice_items, Array('price' => $fee_security_deposit->price_get('stripe_price_id'), 'discount' => $fee_security_deposit_discount, 'description' => $fee_security_deposit->price_get('description_invoice')));

		/**
		 *  Event sessions
		 */

		// Loop through the various event sessions and add them to the invoice
		$sessions = $this->request_get_sessions();
		foreach ($sessions as $session)
		{
			// Gather data
			$event = new Event($session);
			$fee_rental = new Price($event->event_get('fee_rental'));
			$fee_alcohol = new Price($event->event_get('fee_alcohol'));

			// Set whether the rental fee is waived
			$fee_rental_discount = ($event->fee_waived('rental') == TRUE) ? $GLOBALS['settings']->get('invoice.discount.100') : false;
			// Push to the array
			array_push($invoice_items, Array('price' => $fee_rental->price_get('stripe_price_id'), 'discount' => $fee_rental_discount, 'description' => $event->event_get('title') . ' - ' . $fee_rental->price_get('description_invoice')));

			// Set whether the alcohol fee is waived
			$fee_alcohol_discount = ($event->fee_waived('alcohol') == TRUE) ? $GLOBALS['settings']->get('invoice.discount.100') : false;
			// Push to the array
			array_push($invoice_items, Array('price' => $fee_alcohol->price_get('stripe_price_id'), 'discount' => $fee_alcohol_discount, 'description' => $event->event_get('title') . ' - ' . $fee_alcohol->price_get('description_invoice')));
		}

		// Loop through the $prices array and add the items
		foreach ($invoice_items as $item)
		{
			// Supply the event session name
			if (!StripeInterface::stripe_invoice_item_add($invoice->id, $customer->customer_get('stripe_customer_id'), $item['price'], $item['description'], $item['discount']))
			{
				$valid = false;
			}
		}

		return $valid;
	}


	/**
	 *  Check for upcoming events
	 *    + send an email with expectations and a link to the checklist
	 *    + Flip the status from "scheduled" to "in progress"
	 */
	function request_process_upcoming()
	{
		global $mail;
		$customer = new Customer($this->request_get('customer'));
		$message = Messages::customer_upcoming_event_notification($this, $customer);
		if (Utility::mailer_helper($mail, $customer->customer_get('email'), "Your upcoming event at Town Hall", $message))
		{
			$this->status_update('in_progress');
			$this->request_update('customer_reminder_sent', '1');
		}
		else
		{
			Log::log("'request_process_upcoming' failed to send email for requestID: ". $this->requestID);
		}
	}


	/**
	 *  Get the date of the last session for this request
	 */
	function request_has_session_within_past_week()
	{
		$data = $db->safe_query("SELECT event_end FROM event_sessions
		LEFT JOIN requests on event_sessions.requestID = requests.requestID
		WHERE requestID = ? AND
		(NOW() - INTERVAL 7 DAY) > event_sessions.event_end", "s", $this->requestID);

	}




	/* * * * * * * * * * * * * * *
	 *
	 *  Class/static methods
	 *
	 * * * * * * * * * * * * * * */

	/**
	 *  Get all requests
	 */
	public static function request_get_requests($all = FALSE)
	{
		global $db;
		$query = ($all == TRUE) ? "SELECT * FROM requests" : "SELECT requestID FROM requests";
		$result = $db->fetch_assoc($db->query($query));
		if (!empty($result))
		{
			if ($all != TRUE)
			{
				$return = [];
				// Loop and fill
				foreach ($result as $row)
				{
					$return[] = $row['requestID'];
				}
			}
			else
			{
				$return = $result;
			}
			return $return;
		}
	}


	/**
	 *  Create a new request
	 */
	public static function request_create($title, $customerID, $customer_is_wpcl_member, $sponsor_name_first, $sponsor_name_last, $sponsor_email, $sponsor_phone, $is_wp_event, $security_deposit_priceID, $cleaning_fee_priceID)
	{
		global $db;
		$status = 'initiated';
		$data = $db->safe_insert("INSERT INTO requests (title, customerID, customer_is_wpcl_member, wpcl_sponsor_name_first, wpcl_sponsor_name_last, wpcl_sponsor_email, wpcl_sponsor_phone, is_wp_event, security_deposit, cleaning_fee, status) VALUES (?,?,?,?,?,?,?,?,?,?,?)",'siissssiiis',
		$title, $customerID, $customer_is_wpcl_member, $sponsor_name_first, $sponsor_name_last, $sponsor_email, $sponsor_phone, $is_wp_event, $security_deposit_priceID, $cleaning_fee_priceID, $status);
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
	 *  Updates columns in the db for this user
	 */
	function request_update($column, $value)
	{
		global $db;
		$query = "UPDATE requests SET ".$column." = '".$value."' WHERE requestID = '".$this->requestID."'";
		$result = $db->query($query);
		// Reset the object
		$this->setinfo();
		return $result;
	}

	/**
	 *  Accept an entire request array as a parameter
	 *  and determine the appropriate cleaning fee, if any
	 *  Returns the appropriate internal price ID for the
	 *  cleaning fee product
	 *
	 *  Fee assessed:
	 *    + events 1–30 = $0
	 *    + events 31-60
	 *
	 */
	public static function request_get_fees($request_sessions, $productID_cleaning, $productID_security)
	{
		// Cleaning fee product ID: 4
		$product_cleaning_fee = new Product($productID_cleaning);

		// Security deposit product ID: 3
		$product_security_deposit = new Product($productID_security);

		// Instantiate return array
		$return_price = Array();

		// Maintain a running request total attendance
		$attendance_overall = 0;

		//  Loop the sessions
		//  The fee is assigned only once and based on the largest rental size
		//  Note: this function is used for both PRE-submission requests and saved requests

		// Get the price
		foreach ($request_sessions as $session)
		{
			// In this case, session is the price ID submitted by the request form
			if (gettype($session) == 'array')
			{
				$price = new Price($session['session_attendance']);
			}
			// In this case, the "session" is the eventID stored in the DB.
			// So, create an event object and grab the price ID of the associated rental fee
			else if (gettype($session) == 'string')
			{
				$event = new Event($session);
				$price = new Price($event->event_get('fee_rental'));
			}

			// Determine event size based on the label
			// Increment the running total if appropriate
			if (intval($price->price_get('attendance_min')) >= $attendance_overall)
			{
				$attendance_overall = intval($price->price_get('attendance_min'));
			}
		}
		// Determine cleaning fee
		foreach ($product_cleaning_fee->product_get_prices() as $priceID)
		{
			$tempPrice = new Price($priceID);
			if ($tempPrice->price_get('attendance_min') == $attendance_overall)
			{
				$return_price['cleaning'] = $priceID;
			}
		}
		// Determine security deposit
		foreach ($product_security_deposit->product_get_prices() as $priceID)
		{
			$tempPrice = new Price($priceID);
			if ($tempPrice->price_get('attendance_min') == $attendance_overall)
			{
				$return_price['security'] = $priceID;
			}
		}
		return $return_price;
	}

	/**
	 *  Returns whether a business is valid based on ID
	 */
	static function request_is_valid($requestID)
	{
		global $db;
		$data = $db->safe_query("SELECT * FROM requests WHERE requestID = ?", "s", $requestID);
		if (!empty($data))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 *  Holds the valid values for request status
	 */
	static function status_is_valid($status)
	{
		$statuses = Array('initiated', 'review', 'denied', 'approved', 'expired', 'scheduled', 'in_progress', 'ended_pending', 'ended_abandoned', 'ended_inspect', 'complete_deposit_refunded', 'complete_deposit_retained', 'cancelled');
		return (in_array($status, $statuses)) ? true : false;

	}
}
