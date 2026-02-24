<?php
class Messages
{
	private $email;
	private $mailer;

	/**
	 *  Customer notification emails
	 */

	// Send a confirmation to an EXTERNAL customer upon successful request
	public static function customer_upcoming_event_notification($request, $customer)
	{
		$message = $customer->customer_get('name_first') . ",\n\nWe're excited to be hosting your upcoming event (".$request->request_get('title').") at Woodruff Place Town Hall. This email contains pertinent information to ensure a successful and positive rental experience.";
		$message .= "\n\n";
		$message .= "TOWN HALL INFO";
		$message .= "\n\n";
		$message .= "1) The front door key lockbox is 7350. This key will get you in the front door (the boardroom is not included in rentals).";
		$message .= "\n\n";
		$message .= "2) The chair cart is in the main room and the tables are in the closet located in the back corner of the main room.";
		$message .= "\n\n";
		$message .= "3) There is a Nest thermostat for the main room located on the stage. Please note that it is a commercial system and requires more time to heat and cool than a typical house.";
		$message .= "\n\n";
		$message .= "4) The speaker on the stage is available for use and can pair via Bluetooth to your personal device.";
		$message .= "\n\n";
		$message .= "5) The stage lights are controlled by the small mixing board on the floor next to the speaker. Slide channel one to your color preference.";
		$message .= "\n\n";
		$message .= "6) Cleaning supplies and some consumables can be found in the small room to the north (stage right) of the main stage.";
		$message .= "\n\n";
		$message .= "7) Consumables like trash bags, disposable utensils/plates/cups may be found in the kitchen, stage left.";
		$message .= "\n\n\n";
		//$message .= "8)All rental-related tasks are noted in the original contract checklist, 	.";
		$message .= "IMPORTANT\n\n";
		$message .= "As part of your rental contract, you are required to complete the closing checklist, the link to which is below. The checklist must be completed within *". $GLOBALS['settings']->get('request.checklist_due')."* days of the final rental session in order to obtain your security deposit back.";
		$message .= "\n\n";
		$message .= "Complete the checklist here:\n";
		$message .= $GLOBALS['config']['env']['protocol'] . "://" . $GLOBALS['config']['env']['host'] . "/checklist?id=" . $request->request_get('requestID');
		$message .= "\n\n";
		$message .= "Please do not hesitate to reach out to us with any questions in the meantime. Otherwise, we wish you a successful event." . $GLOBALS['config']['mail']['signature'];
		return $message;
	}

	/**
	 *  New request notification emails
	 */

	// Send a confirmation to an EXTERNAL customer upon successful request
	public static function customer_external_new_request_confirm($form)
	{
		$message = $form['name_first'] . ",\n\nThank you for your interest in Woodruff Place Town Hall for your event. Please allow us up to one week to process your request. \n\nWe'll notify you once we confirm the details, membership or sponsors' membership, and availability. If everything checks out, the next step is that you will receive an invoice via email with a link to pay and confirm the reservation.";
		$message .= "\n\nPlease do not hesitate to reach out to us with any questions in the meantime." . $GLOBALS['config']['mail']['signature'];
		return $message;
	}

	// Send a confirmation to an INTERNAL customer upon successful request
	public static function customer_internal_new_request_confirm($form)
	{
		$message = $form['name_first'] . ",\n\nThe Woodruff Place Town Hall committee has received your request. Please allow us up to one week to review and process your request. \n\nWe'll notify you once we confirm the details and availability.";
		$message .= "\n\nPlease do not hesitate to reach out to us with any questions." . $GLOBALS['config']['mail']['signature'];
		return $message;
	}

	// Send a notification to the Town Hall committee
	public static function committee_notification_new_request($form)
	{
		global $config;
		$message = "Town Hall Committee,\n\nA new Town Hall request has been submitted. Please see details below:\n\n";
		$message .= "Event type: ";
		$message .= (Utility::process_yn_boolean($form['is_wp_event']) == "1") ? "Internal" : "External";
		$message .= "\n";
		$message .= "Event title: " . $form['event_title'] . "\n";
		$message .= "Requestor: " . $form['name_first'] . " " . $form['name_last'] . " (" . $form['email'] . ")\n\n";
		$message .= "***\nView and manage requests:\n". $GLOBALS['config']['env']['protocol'] . "://" . $GLOBALS['config']['env']['host'] . "\n***" . $GLOBALS['config']['mail']['signature'] ."\n\nNote: this message was dynamically generated and this mailbox is unmonitored.";
		return $message;
	}

	/**
	 *  New request approval / denial
	 */

	// Send a confirmation to an INTERNAL customer upon approval
	public static function customer_internal_request_approval($request)
	{
		global $config;
		$requestor = new Customer($request->request_get('customer'));
		$message = $requestor->customer_get('name_first') . ",\n\nYour request to use Woodruff Place Town Hall for the following event has been approved:\n\n";
		$message .= $request->request_get('title') . "\n";
		foreach ($request->request_get_sessions() as $session)
		{
			$event = new Event($session);
			$message .= (!empty($event->event_get('title'))) ? "\n" . $event->event_get('title') . "\n" : '';
			if ($event->event_shares_start_end_date())
			{
				$message .= date('M j, Y', strtotime($event->event_get('event_start'))) . ": " . date('g:i a', strtotime($event->event_get('event_start'))) . " - " . date('g:i a', strtotime($event->event_get('event_end')));
			}
			else
			{
				$message .= "Start: " . date('M j, Y g:i a', strtotime($event->event_get('event_start'))) . "\n";
				$message .= "End: " . date('M j, Y g:i a', strtotime($event->event_get('event_end')));
			}
			$message .= "\n";
		}
		$message .= "\n\nPlease do not hesitate to reach out to the Town Hall committee with any questions or for any assistance you may require." . $GLOBALS['config']['mail']['signature'];
		return $message;
	}

	// Send a confirmation to an INTERNAL customer upon approval
	public static function customer_internal_request_denial($request)
	{
		global $config;
		$requestor = new Customer($request->request_get('customer'));
		$message = $requestor->customer_get('name_first') . ",\n\nThe Woodruff Place Town Hall Committee was unable to approve your request to use the space for the following event:\n\n";
		$message .= $request->request_get('title');
		$message .= "\n\nIf you have further questions, please reach out to us directly." . $GLOBALS['config']['mail']['signature'];
		return $message;
	}

	/**
	 *  User emails
	 */

	// Send user an email with a one-time login link
	public static function user_login($link)
	{
		$message = "Welcome to the Woodruff Place Town Hall Request Management Platform!\n\nLogin by clicking the link below (or copy and paste it into your browser):\n\n";
		$message .= $link;
		$message .= "\n\nNote: the link is only valid once and will expire in 10 minutes.". $GLOBALS['config']['mail']['signature'];
		return $message;
	}

	/**
	 *  Internal emails
	 */

	// Send a confirmation to Woodruff Place treasurer upon cancellation
	public static function treasurer_internal_request_cancelled($request)
	{
		$requestor = new Customer($request->request_get('customer'));
		$message = "Woodruff Place Treasurer,\n\nThis is to notify you that the following Town Hall rental request has been cancelled post-payment:\n\n";
		$message .= "Event title:\n";
		$message .= $request->request_get('title') . "\n\n";
		$message .= "Customer information:\n";
		$message .= $requestor->customer_get('name_first') . " " . $requestor->customer_get('name_last') . "\n" . $requestor->customer_get('email');
		$message .= "\n\n";
		$message .= "Please coordinate with the Town Hall Monitors committee to issue any necessary refund." . $GLOBALS['config']['mail']['signature'];
		return $message;
	}

	// Send a notification to the Town Hall committee for checklist completed
	public static function committee_notification_checklist_submit($request)
	{
		global $config;
		$message = "Town Hall Committee,\n\nThe closing event checklist has been submitted on behalf of the following event:\n\n";
		$message .= $request->request_get('title') . "\n\n";
		$message .= "At your earliest convenience, please inspect Town Hall and either approve or deny the closing. Remember: this affects the return of the customer's security deposit.";
		$message .= "\n\n";
		$message .= "***\nView and manage requests:\n". $GLOBALS['config']['env']['protocol'] . "://" . $GLOBALS['config']['env']['host'] . "\n***" . $GLOBALS['config']['mail']['signature'] ."\n\nNote: this message was dynamically generated and this mailbox is unmonitored.";
		return $message;
	}

	// Send a note to the treasurer to refund the security deposit for a request
	public static function treasurer_internal_refund_deposit($request)
	{
		$requestor = new Customer($request->request_get('customer'));
		$message = "Woodruff Place Treasurer,\n\nThis is to notify you that the following Town Hall rental event has completed and the customer is owed their security deposit refunded at your earliest convenience. Event and customer information below:";
		$message .= "\n\n";
		$message .= "Event title:\n";
		$message .= $request->request_get('title') . "\n\n";
		$message .= "Customer information:\n";
		$message .= $requestor->customer_get('name_first') . " " . $requestor->customer_get('name_last') . "\n" . $requestor->customer_get('email');
		$message .= "\n\n";
		$message .= "Please coordinate with the Town Hall Monitors committee with any questions." . $GLOBALS['config']['mail']['signature'];
		return $message;
	}

}  // end class
