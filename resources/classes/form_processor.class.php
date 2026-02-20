<?php
class FormProcessor
{

	/**
	 *  Capture the form fields
	 */
	public static function form_collect($edited_item = FALSE, $collect_session = FALSE)
	{
		// Set initials
		$is_valid = FALSE;

		// Requestor contact info
		$_SESSION['form']['name_first'] 	= Utility::form_field_parse($_POST['name_first']);
		$_SESSION['form']['name_last']		= Utility::form_field_parse($_POST['name_last']);
		$_SESSION['form']['email']			= Utility::form_field_parse($_POST['email']);
		$_SESSION['form']['phone'] 			= Utility::form_field_parse($_POST['phone']);
		$_SESSION['form']['organization'] 	= Utility::form_field_parse($_POST['organization']);
		$_SESSION['form']['wpcl_member'] 	= isset($_POST['wpcl_member']) ? Utility::form_field_parse($_POST['wpcl_member']) : NULL;
		$_SESSION['form']['is_wp_event'] 	= isset($_POST['is_wp_event']) ? Utility::form_field_parse($_POST['is_wp_event']) : NULL;
		$_SESSION['form']['event_title'] 	= Utility::form_field_parse($_POST['event_title']);
		$_SESSION['form']['agreement']	 	= isset($_POST['agreement']) ? Utility::form_field_parse($_POST['agreement']) : NULL;

		// Collect WPCL sponsor info
		$_SESSION['form']['wpcl_sponsor_name_first'] 	= Utility::form_field_parse($_POST['wpcl_sponsor_name_first']);
		$_SESSION['form']['wpcl_sponsor_name_last']		= Utility::form_field_parse($_POST['wpcl_sponsor_name_last']);
		$_SESSION['form']['wpcl_sponsor_email']			= Utility::form_field_parse($_POST['wpcl_sponsor_email']);
		$_SESSION['form']['wpcl_sponsor_phone'] 		= Utility::form_field_parse($_POST['wpcl_sponsor_phone']);


		if ($collect_session == TRUE)
		{
			// Instantiate arrays for session info
			if (!isset($_SESSION['form']['sessions']))
			{
				$_SESSION['form']['sessions'] = Array();
			}
			$temp_sessions = [];

			// Collect session info
			if (!empty($_POST['session_name']))
			{
				$temp_sessions['session_name']			= Utility::form_field_parse($_POST['session_name']);
				$temp_sessions['session_start_date']	= Utility::form_field_parse($_POST['session_start_date']);
				$temp_sessions['session_start_time']	= Utility::form_field_parse($_POST['session_start_time']);
				$temp_sessions['session_end_date']		= Utility::form_field_parse($_POST['session_end_date']);
				$temp_sessions['session_end_time']		= Utility::form_field_parse($_POST['session_end_time']);
				$temp_sessions['session_attendance']	= Utility::form_field_parse($_POST['session_attendance']);
				$temp_sessions['session_alcohol']		= Utility::form_field_parse($_POST['session_alcohol']);

				// Clear alerts
				unset($_SESSION['alert']);

				// Push session info to the main array
				if (!isset($edited_item) || !is_numeric($edited_item))
				{
					array_push($_SESSION['form']['sessions'], $temp_sessions);
					$_SESSION['alert'] = "added";
				}
				else
				{
					$_SESSION['form']['sessions'][$edited_item] = $temp_sessions;
					$_SESSION['alert'] = "edited";
				}

				// Empty the temp array
				$temp_sessions = [];
				//header("Location: " . $_SERVER['REQUEST_URI']);
				// Important: terminate script execution after redirect
				//exit();
			}
			else
			{
				// Set an alert
				$_SESSION['alert'] = 'info-required';
			}
		}
	}


	/**
	 *  Directly add a session to the db from the request edit screen
	 *  $req
	 */
	public static function form_process_session($flag, $request = NULL, $eventID = NULL)
	{
		// Set initials
		$is_valid = FALSE;

		$temp['session_name']		= Utility::form_field_parse($_POST['session_name']);
		$temp['session_start_date']	= Utility::form_field_parse($_POST['session_start_date']);
		$temp['session_start_time']	= Utility::form_field_parse($_POST['session_start_time']);
		$temp['session_end_date']	= Utility::form_field_parse($_POST['session_end_date']);
		$temp['session_end_time']	= Utility::form_field_parse($_POST['session_end_time']);
		$temp['session_attendance']	= Utility::form_field_parse($_POST['session_attendance']);
		$temp['session_alcohol']	= Utility::form_field_parse($_POST['session_alcohol']);
		$temp['fee_waiver_rental']	= (isset($_POST['fee_waiver_rental']) && Utility::form_field_parse($_POST['fee_waiver_rental']) !== null && Utility::form_field_parse($_POST['fee_waiver_rental']) == "y") ? "1" : "0";
		$temp['fee_waiver_alcohol']	= (isset($_POST['fee_waiver_alcohol']) && Utility::form_field_parse($_POST['fee_waiver_alcohol'])!== null && Utility::form_field_parse($_POST['fee_waiver_alcohol']) == "y") ? "1" : "0";

		// Set start and end datetimes
		$datetime_start = $temp['session_start_date'] . " " . $temp['session_start_time'];
		$datetime_end = $temp['session_end_date'] . " " . $temp['session_end_time'];

		// Process
		switch ($flag)
		{
			case 'new':
				$eventID = Event::event_create_session($temp['session_name'], $request->request_get('requestID'), $datetime_start, $datetime_end, $temp['session_attendance'], $temp['session_alcohol']);
				// Update - Woodruff events are fee-waived
				if ($request->request_get('is_wp_event') == "1")
				{
					Event::event_update_session('fee_waiver_rental', "1", $eventID);
					Event::event_update_session('fee_waiver_alcohol', "1", $eventID);
				}
			break;
			case 'edit':
				// name
				echo $eventID;
				Event::event_update_session('title', $temp['session_name'], $eventID);
				Event::event_update_session('event_start', $datetime_start, $eventID);
				Event::event_update_session('event_end', $datetime_end, $eventID);
				Event::event_update_session('rental_fee', $temp['session_attendance'], $eventID);
				Event::event_update_session('fee_waiver_rental', $temp['fee_waiver_rental'], $eventID);
				Event::event_update_session('fee_waiver_alcohol', $temp['fee_waiver_alcohol'], $eventID);

				// Alcohol
				if ($temp['session_alcohol'] == 'y')
				{
					$alcohol = new Product(Product::product_get_config('alcohol'));
					$price_alcohol = $alcohol->product_get_prices();
					$price_alcohol = $price_alcohol[0];
				}
				else
				{
					$price_alcohol = '0';
				}
				Event::event_update_session('alcohol_fee', $price_alcohol, $eventID);

				// Process fee waivers for Woodruff Place events
				$event = new Event($eventID);
				$request = new Request($event->event_get('requestID'));
				if ($request->request_get('is_wp_event') == "1")
				{
					Event::event_update_session('fee_waiver_rental', "1", $eventID);
					Event::event_update_session('fee_waiver_alcohol', "1", $eventID);
				}
			break;
		}
	}

	/**
	 *  Process a new rental request
	 */
	public static function new_request_initiate($form)
	{
		global $config;
		global $mail;
		$return = TRUE;
		/**
		 *  Customer
		 *
		 *  If customer exists, update their info and grab their customerID
		 */
		if ($customerID = Customer::customer_email_exists($form['email']))
		{
			$customer = new Customer($customerID);
			$customer->customer_update('name_first', $form['name_first']);
			$customer->customer_update('name_last', $form['name_last']);
			$customer->customer_update('email', $form['email']);
			$customer->customer_update('phone', $form['phone']);
			$customer->customer_update('organization', $form['organization']);
			$customer->customer_update('wpcl_member', Utility::process_yn_boolean($form['wpcl_member']));
			// Collect sponsor info if necessary
			if (Utility::process_yn_boolean($form['wpcl_member']) == "1")
			{
				$form['wpcl_sponsor_name_first'] = "";
				$form['wpcl_sponsor_name_last'] = "";
				$form['wpcl_sponsor_email'] = "";
				$form['wpcl_sponsor_phone'] = "";
			}
		}
		else
		{
			$customerID = Customer::customer_create($form['name_first'], $form['name_last'], $form['email'], $form['phone'], $form['organization']);
		}
		// Set return = false if something went wrong
		if (!$customerID)
		{
			$return = FALSE;
		}

		/**
		 *  Request
		 *
		 *  We have all we need to create the new request
		 */
		// First, get the fees
		$fees = Request::request_get_fees($form['sessions'], $config['products']['cleaning'], $config['products']['security']);
		$fee_cleaning = new Price($fees['cleaning']);
		$fee_security = new Price($fees['security']);

		// Create the request, grab the Request ID
		$requestID = Request::request_create($form['event_title'], $customerID, Utility::process_yn_boolean($form['wpcl_member']), $form['wpcl_sponsor_name_first'], $form['wpcl_sponsor_name_last'], $form['wpcl_sponsor_email'], $form['wpcl_sponsor_phone'], $form['is_wp_event'], $fee_security->price_get('priceID'), $fee_cleaning->price_get('priceID'));

		// Set return = false if something went wrong
		if (!$requestID)
		{
			$return = FALSE;
		}

		/**
		 *  Event sessions
		 *
		 *  Add all the event sessions
		 */
		$events = Array();
		foreach ($form['sessions'] as $session)
		{
			// Set start and end datetimes
			$datetime_start = $session['session_start_date'] . " " . $session['session_start_time'];
			$datetime_end = $session['session_end_date'] . " " . $session['session_end_time'];

			// Alcohol
			if ($session['session_alcohol'] == 'y')
			{
				$alcohol = new Product(Product::product_get_config('alcohol'));
				$price_alcohol = $alcohol->product_get_prices();
				$price_alcohol = $price_alcohol[0];
			}
			else
			{
				$price_alcohol = NULL;
			}

			// Insert the new session
			$eventID = Event::event_create_session($session['session_name'], $requestID, $datetime_start, $datetime_end, $session['session_attendance'], $price_alcohol);

			// If the overall request is a Woodruff Place event, then automatically waive the fees
			if ($form['is_wp_event'] == "1")
			{
				Event::event_update_session('fee_waiver_rental', "1", $eventID);
				Event::event_update_session('fee_waiver_alcohol', "1", $eventID);
			}
			array_push($events, $eventID);
		}
		if (empty($events))
		{
			$return = FALSE;
		}

		// Email messages
		if ($return)
		{
			// Confirmation email to customer -- either internal or external
			$message = (Utility::process_yn_boolean($form['is_wp_event']) == "1") ? Messages::customer_internal_new_request_confirm($form) : 	Messages::customer_external_new_request_confirm($form);
			Utility::mailer_helper($mail, $form['email'], "Confirming your Town Hall request", $message);

			// Notification to Town Hall committee
			$message = Messages::committee_notification_new_request($form);
			Utility::mailer_helper($mail, $config['notification_internal_requests'], "New Town Hall request - " . $form['event_title'], $message, 'Woodruff Place Town Hall');
		}
		// Return
		return $return;
	}

	/**
	 *  Small function to disable a form field
	 */
	public static function disable_if_review($value)
	{
		if (isset($value) && $value == TRUE)
		{
			echo "disabled";
		}
	}

	/**
	 *  Capture request fee waivers - for the request
	 */
	public static function process_fee_waivers($requestID)
	{
		// Create a new request object to ensure the most up-to-date info
		$request = new Request($requestID);
		// Capture fee waivers
		$request_fee_waive = Array();
		// Waive fees for Woodruff Place events
		if ($request->request_get('is_wp_event') == "1")
		{
			$request_fee_waive['fee_waiver_cleaning'] = "1";
			$request_fee_waive['fee_waiver_security'] = "1";
		}
		else
		{
			$request_fee_waive['fee_waiver_cleaning'] = (isset($_POST['form_fee_cleaning_waiver']) && Utility::form_field_parse($_POST['form_fee_cleaning_waiver']) == "y") ? "1" : "0";
			$request_fee_waive['fee_waiver_security'] = (isset($_POST['form_fee_security_waiver']) && Utility::form_field_parse($_POST['form_fee_security_waiver']) == "y") ? "1" : "0";
		}
		if (!empty($request_fee_waive))
		{
			foreach ($request_fee_waive as $key => $value)
			{
				$request->request_update($key, $value);
			}
		}
	}

	/**
	 *  Capture request fee waivers - for the individual sessions
	 */
	public static function process_session_fee_waivers($requestID)
	{
		global $db;
		$request = new Request($requestID);
		$waiver_setting = ($request->request_get('is_wp_event') == "1") ? "1" : "0";

		// Update all rows at once
		$query = "UPDATE event_sessions SET fee_waiver_rental = '" . $waiver_setting . "', fee_waiver_alcohol = " . $waiver_setting . " WHERE requestID = '".$request->request_get('requestID') . "'";
		$result = $db->query($query);
		return $result;
	}

} // end class
