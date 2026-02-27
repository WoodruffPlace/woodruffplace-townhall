<?php
class StripeInterface
{
	public static $stripe_api_key;

	// Set the API key
	public static function set_stripe_api_key($key)
	{
		self::$stripe_api_key = $key;
	}

	/**
	 *  Create the Stripe invoice
	 */
	 static function stripe_invoice_create($customer, $request_title)
	{
		// Assign the variables
		$stripe_invoice = Array();
		// Default and configuration-based settings
		$stripe_invoice['collection_method'] 				= 'send_invoice';
		$stripe_invoice['days_until_due']					= $GLOBALS['settings']->get('invoice.days_until_due');
		$stripe_invoice['pending_invoice_items_behavior'] 	= 'include';
		// Request-specific values
		$stripe_invoice['customer'] 			= $customer->customer_stripe_id_lookup_process();
		$stripe_invoice['description']			= $GLOBALS['settings']->get('invoice.description_start') . ' ' . $request_title;

		// Create the Stripe session
		$stripe = new \Stripe\StripeClient(self::$stripe_api_key);
		// Create the invoice
		$invoice = $stripe->invoices->create($stripe_invoice);
		// Return the invoice object
		return $invoice;
	}

	/**
	 *  Create a new invoice item
	 *
	 *  $item = Array of Stripe Price IDs
	 */
	 static function stripe_invoice_item_add($stripe_invoice_id, $stripe_customer_id, $stripe_price_id, $description, $discount = FALSE)
	{
		// Prepare the data
		$stripe_invoice_item = Array();
		$stripe_invoice_item['customer']			= $stripe_customer_id;
		$stripe_invoice_item['invoice'] 			= $stripe_invoice_id;
		if ($description)
		{
			$stripe_invoice_item['description']	= $description;
		}
		if ($discount)
		{
			$stripe_invoice_item['discounts'] = Array(Array('coupon' => $discount));
		}
		$stripe_invoice_item['pricing']['price']	= $stripe_price_id;

		// Create the Stripe session
		$stripe = new \Stripe\StripeClient(self::$stripe_api_key);

		// Add the invoice item
		$invoice_item = $stripe->invoiceItems->create($stripe_invoice_item);

		// Return the ID
		return $invoice_item;
	}


	// Validate a checkout session
	static function stripe_checkout_session_validate($sessionID)
	{
		$stripe = new \Stripe\StripeClient(self::$stripe_api_key);
		$session = $stripe->checkout->sessions->retrieve($sessionID,[]);
		return $session;
	}

	// Process a Stripe checkout session
	static function stripe_process_session($session)
	{
		$stripe = new \Stripe\StripeClient(self::$stripe_api_key);
		if ($session->payment_status == "paid")
		{
			// Change database entries from "pending" to "active"
			// Update business to "active"

			// Review each of the items in the local subscriptions session array and
			// verify the corresponding Stripe Price ID is contained in the
			// "items" value from the Stripe checkout session.
			// If so, flip the "is_active" bit in the local db to "1" for both
			// subscription and business.
			if (isset($_SESSION['subscriptions']))
			{
				// Get this Stripe session's line items
				$lineItems = $stripe->checkout->sessions->allLineItems($session->id,[]);
				$match = FALSE;
				foreach ($_SESSION['subscriptions'] as $subscriptionID)
				{
					$subscription = new Subscription($subscriptionID);
					$membership = new Membership($subscription->subscription_get_attribute('membershipID'));
					$business = new Business($subscription->subscription_get_attribute('businessID'));
					$user = new User($business->business_get_attribute('billing_contact'));

					// Verify that the Stripe Price ID that corresponds to our membership is found
					// in the payload
					foreach ($lineItems->data as $element)
					{
						if ($element->price->id == $membership->membership_get_attribute('stripe_price_id_current'))
						{
							$match = TRUE;
						}
					}

					// If we have a match, update the subscription's status and
					// insert the Stripe Subscription ID
					if ($match == TRUE)
					{
						// Update the Stripe subscription ID
						$subscription->subscription_update('stripe_subscription_id', $session->subscription);
						// Update the business status to "active"
						$business->business_update('status', '1');
						// Update the billing contact user so they can login
						$user->user_update('can_login', '1');

						// Get the subscription object from Stripe and update the expiration date
						$newSubscription = $stripe->subscriptions->retrieve($session->subscription, []);
						foreach ($newSubscription->items->data as $values)
						{
							// Verify the Stripe Price ID matches
							if ($values->price->id == $membership->membership_get_attribute('stripe_price_id_current'))
							{
								$subscription->subscription_update('expiration', gmdate("Y-m-d", $values['current_period_end']));
							}
						}
					}
				}
			}
			// return TRUE
			return TRUE;
		}
	}

	/**
	 *  Update a customer
	 *
	 *  $stripe_customer_id = a valid Stripe customer_id number
	 *  $customer_metadata = an array of metadata formatted ready for Stripe
	 */
	static function stripe_customer_update($stripe_customer_id, $customer_data)
	{
		$stripe = new \Stripe\StripeClient(self::$stripe_api_key);
		return ($customer = $stripe->customers->update($stripe_customer_id, $customer_data)) ? $customer : FALSE;
	}
}
