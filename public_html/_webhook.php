<?php
require_once('_session.php');

$payload = @file_get_contents('php://input');
$event = null;

// Only verify the event if there is an endpoint secret defined
// Otherwise use the basic decoded event
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
try
{
	$event = \Stripe\Webhook::constructEvent($payload, $sig_header, $config['stripe_webhook_secret']);

	// Process the event
	switch ($event->type)
	{
		// contains a \Stripe\Invoice
		case 'invoice.paid':
			$invoice = $event->data->object;
			/**
			 *  Process a paid invoice
			 *  Function returns the requestID
			 */
			$inv = "in_1SzVGMFpJK7bNF3rXc5rrNYo";
			$return = Invoice::invoice_update_status($invoice->status, $inv);
			if ($return)
			{
				// Update the status of the request
				$request = new Request($return);
				$request->status_update('scheduled');

				// Send notification email
				$message = "Town Hall Committee,\n\nThis email is to notify you that the invoice for the following Town Hall rental has been paid:\n\n". $request->request_get('title') . "\n\n***\nView and manage requests:\n". $config['env']['protocol'] . "://" . $config['env']['host'] . "\n***\n\n--\nWoodruff Place Civic League\nhttps://WoodruffPlace.org";
				Utility::mailer_helper($mail, $config['notification_internal_requests'], 'Town Hall rental paid: ' . $request->request_get('title'), $message);
			}
		break;
		default:
			error_log('Received unknown event type');
	}
}
catch(\Stripe\Exception\SignatureVerificationException $e)
{
	// Invalid signature
	echo '⚠️  Webhook error while validating signature.';
	http_response_code(400);
	exit();
}

?>
