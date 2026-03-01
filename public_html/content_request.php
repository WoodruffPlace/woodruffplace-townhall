<?php require_once('_session.php'); ?>
<?php require_once('_session_auth.php'); ?>
<?php if (!isset($page_header)){ Page::redirect_home(); } ?>
<?php

/**
 *  Get request and related info from the query string
 */
$id = (isset($_GET['id']) ? htmlspecialchars($_GET['id']) : null);

// Initialize "edit mode" as false
$mode_edit = false;

// Validate Request ID
if (Request::request_is_valid($id))
{
	// Create request
	$request = new Request($id);

	// Get all request sessions
	$sessions = $request->request_get_sessions();

	// Get any session conflicts
	$conflicts = Request::get_conflict_map();

	// Create customer
	$customer = new Customer($request->request_get('customer'));
}
else
{
	header("Location: /requests");
	exit;
}

/**
 *  Handle editing
 */
$mode_edit = (isset($_GET['edit']) ? htmlspecialchars($_GET['edit']) : null);

/**
 *  Accept form
 */
if (isset($_POST['action']))
{
	switch ($_POST['action'])
	{
		case 'session_add':
			$_SESSION['alert'] = 'session-added';
			FormProcessor::form_process_session('new', $request);
			header("Location: " . '/request?id=' . $request->request_get('requestID'));
			exit;
		break;
		case 'session_edit':
			$_SESSION['alert'] = 'session-saved';
			FormProcessor::form_process_session('edit', $request, Utility::form_field_parse($_POST['session_to_edit']));
			// Update the fees for the overall request
			$request->request_update_fees();
			header("Location: " . '/request?id=' . $request->request_get('requestID'));
			exit;
		break;
		case 'session_remove':
			$_SESSION['alert'] = 'session-deleted';
			Event::event_delete_session(Utility::form_field_parse($_POST['session_to_remove']));
			header("Location: " . '/request?id=' . $request->request_get('requestID'));
			exit;
		break;
		case 'request_save_notes':
			$notes['event']			= Utility::form_field_parse($_POST['notes_event']);
			$notes['fee_waiver']	= Utility::form_field_parse($_POST['notes_feewaiver']);
			$request->request_update('notes_event', $notes['event']);
			$request->request_update('notes_fee_waiver', $notes['fee_waiver']);
			$_SESSION['alert'] = 'notes-saved';
			header("Location: " . '/request?id=' . $request->request_get('requestID'));
			exit;
		break;
		case 'request_save':
			// Set initials
			$is_valid = true;

			// Collect customer data
			$customer_info['name_first'] 	= Utility::form_field_parse($_POST['name_first']);
			$customer_info['name_last']		= Utility::form_field_parse($_POST['name_last']);
			$customer_info['email']			= Utility::form_field_parse($_POST['email']);
			$customer_info['phone'] 		= Utility::form_field_parse($_POST['phone']);

			// Update customer
			foreach ($customer_info as $key => $value)
			{
				if (empty($value))
				{
					$is_valid = FALSE;
				}
			}

			// Non-required fields
			$customer_info['organization'] 	= Utility::form_field_parse($_POST['organization']);
			// Update request
			$request_info['title']		 				= Utility::form_field_parse($_POST['event_title']);
			$request_info['is_wp_event'] 				= isset($_POST['is_wp_event']) ? Utility::process_yn_boolean(Utility::form_field_parse($_POST['is_wp_event'])) : NULL;
			$request_info['customer_is_wpcl_member'] 	= isset($_POST['wpcl_member']) ? Utility::process_yn_boolean(Utility::form_field_parse($_POST['wpcl_member'])) : NULL;
			$request_info['wpcl_sponsor_name_first']	= Utility::form_field_parse($_POST['wpcl_sponsor_name_first']);
			$request_info['wpcl_sponsor_name_last']		= Utility::form_field_parse($_POST['wpcl_sponsor_name_last']);
			$request_info['wpcl_sponsor_email']			= Utility::form_field_parse($_POST['wpcl_sponsor_email']);
			$request_info['wpcl_sponsor_phone'] 		= Utility::form_field_parse($_POST['wpcl_sponsor_phone']);

			if (empty($request_info['title']))
			{
				$is_valid = FALSE;
			}
			if ($request_info['customer_is_wpcl_member'] != '1' &&
				(
					empty($request_info['wpcl_sponsor_name_first']) ||
					empty($request_info['wpcl_sponsor_name_last']) ||
					empty($request_info['wpcl_sponsor_email']) ||
					empty($request_info['wpcl_sponsor_phone'])
				)
			)
			{
				$is_valid = FALSE;
				$_SESSION['alert'] = 'sponsor-info-incomplete';
			}

			// If customer is sponsor, clear out other fields
			if ($request_info['customer_is_wpcl_member'] == '1')
			{
				$request_info['wpcl_sponsor_name_first'] = '';
				$request_info['wpcl_sponsor_name_last'] = '';
				$request_info['wpcl_sponsor_email']  = '';
				$request_info['wpcl_sponsor_phone'] = '';
			}

			// Update request
			if ($is_valid == TRUE)
			{
				// If any customer info is different, update Stripe
				$customer->update_stripe_if_updated($customer_info);

				// Update customer
				foreach ($customer_info as $key => $value)
				{
					if (empty($value))
					{
						$is_valid = FALSE;
					}
					$customer->customer_update($key, $value);
				}

				// Update request
				foreach ($request_info as $key => $value)
				{
					$request->request_update($key, $value);
				}
				// Capture fee waivers
				FormProcessor::process_fee_waivers($request->request_get('requestID'));

				// Update session fees based on whether event is WP event
				FormProcessor::process_session_fee_waivers($request->request_get('requestID'));

				// Set alert
				$_SESSION['alert'] = 'request-saved';
				header("Location: " . '/request?id=' . $request->request_get('requestID'));
				exit;
			}
			else
			{
				$_SESSION['alert'] = 'info-incomplete';
			}
		break;
		case 'request_route':
			$request_route_status = Utility::form_field_parse($_POST['request_status_switch']);

			// Capture fee waivers
			FormProcessor::process_fee_waivers($request->request_get('requestID'));

			// Process status
			switch ($request_route_status)
			{
				// This is easy: do a quick status update
				case 'initiated':
				case 'review':
					$request->status_update($request_route_status);
					// UI response
					$_SESSION['alert'] = 'request-saved';
					header("Location: " . '/request?id=' . $request->request_get('requestID'));
					exit;
				break;
				case 'denied':
					$request->status_update($request_route_status);
					// UI response
					$_SESSION['alert'] = 'request-denied';
					$request->request_process_internal_denial();
					header("Location: " . '/request?id=' . $request->request_get('requestID'));
					exit;
				break;
				case 'approved':
					// Initiate the Stripe invoice and kick off the payment process
					if ($request->request_get('is_wp_event'))
					{
						$request->request_process_internal_approval('scheduled');
						$_SESSION['alert'] = 'request-approved-internal';
						header("Location: " . '/request?id=' . $request->request_get('requestID'));
						exit;
					}
					else
					{
						if ($request->request_initiate_invoice())
						{
							$request->status_update($request_route_status);
							// UI response
							$_SESSION['alert'] = 'request-approved';
							header("Location: " . '/request?id=' . $request->request_get('requestID'));
							exit;
						}
						else
						{
							$_SESSION['alert'] = 'request-process-error';
							header("Location: " . '/request?id=' . $request->request_get('requestID'));
							exit;
						}
					}
				break;
			}
		break;
		case 'event_cancel':
			$request->status_update('cancelled');
			// Remove from WoodruffPlace.org calendar

			// Send notification to treasurer if non-WP event
			if ($request->request_get('is_wp_event') != "1")
			{
				$message = Messages::treasurer_internal_request_cancelled($request);
				Utility::mailer_helper($mail, $config['notification_internal_treasurer'], "Town Hall event cancelled - " . $request->request_get('title'), $message);
			}
			// UI response
			$_SESSION['alert'] = 'request-cancelled';
			header("Location: " . '/request?id=' . $request->request_get('requestID'));
			exit;
		break;
		case 'inspection_record':
			$inspection['result']	= Utility::form_field_parse($_POST['inspection']);
			$inspection['comments'] = Utility::form_field_parse($_POST['inspection_comments']);

			$is_valid = true;

			if (empty($inspection['result']) || ($inspection['result'] == "deny") && empty($inspection['comments']))
			{
				$is_valid = false;
				$_SESSION['alert'] = 'inspection-submission-fail';
				header("Location: " . '/request?id=' . $request->request_get('requestID'));
				exit;
			}
			// Record the inspection results and close out event
			else
			{
				if (!empty($inspection['comments']))
				{
					$request->request_update('customer_checklist_comments', $inspection['comments']);
				}
				if ($inspection['result'] == "approve")
				{
					$request->request_update('inspection_pass', "1");
					$request->status_update('complete_deposit_refunded');
					// Notification to WP Treasurer for refund
					$message = Messages::treasurer_internal_refund_deposit($request);
					Utility::mailer_helper($mail, $GLOBALS['config']['notification_internal_treasurer'], "Rental security deposit refund request", $message, 'Woodruff Place Town Hall', $GLOBALS['config']['notification_internal_requests']);
				}
				else
				{
					$request->request_update('inspection_pass', "0");
					$request->status_update('complete_deposit_retained');
				}
				$_SESSION['alert'] = 'inspection-complete';
				header("Location: " . '/request?id=' . $request->request_get('requestID'));
				exit;
			}

		break;
	}
}
?>
<?php
// Set alerts
if (isset($_SESSION['alert']))
{
	switch ($_SESSION['alert'])
	{
		case 'request-saved':
		case 'notes-saved':
			$alert['status'] = TRUE;
			$alert['type'] = 'success';
			$alert['content'] = "Request saved.";
		break;
		case 'session-added':
			$alert['status'] = TRUE;
			$alert['type'] = 'success';
			$alert['content'] = "Session added.";
		break;
		case 'session-saved':
			$alert['status'] = TRUE;
			$alert['type'] = 'success';
			$alert['content'] = "Session saved.";
		break;
		case 'session-deleted':
			$alert['status'] = TRUE;
			$alert['type'] = 'warning';
			$alert['content'] = "Session removed.";
		break;
		case 'info-incomplete':
			$alert['status'] = TRUE;
			$alert['type'] = 'danger';
			$alert['content'] = "Please ensure all required fields are completed.";
		break;
		case 'sponsor-info-incomplete':
			$alert['status'] = TRUE;
			$alert['type'] = 'danger';
			$alert['content'] = "Sponsor info is required if requestor is not a Civic League member.";
		break;
		case 'request-denied':
			$alert['status'] = TRUE;
			$alert['type'] = 'danger';
			$alert['content'] = "This request has been denied. A generic notification has been sent, but you may wish to follow up with the requestor with additional context.";
		break;
		case 'request-approved':
			$alert['status'] = TRUE;
			$alert['type'] = 'success';
			$alert['content'] = "This request has been approved. An invoice will be emailed to the customer. You will be notified upon successful payment.";
		break;
		case 'request-approved-internal':
			$alert['status'] = TRUE;
			$alert['type'] = 'success';
			$alert['content'] = "This request has been approved and the requestor has been notified. No further action is needed.";
		break;
		case 'request-process-error':
			$alert['status'] = TRUE;
			$alert['type'] = 'danger';
			$alert['content'] = "An error has occurred. Please try again.";
		break;
		case 'request-cancelled':
			$alert['status'] = TRUE;
			$alert['type'] = 'success';
			$alert['content'] = "This request has been cancelled. No notification has been sent to the customer. Please coordinate with the treasurer any necessary refunds.";
		break;
		case 'inspection-submission-fail':
			$alert['status'] = TRUE;
			$alert['type'] = 'danger';
			$alert['content'] = "Please make a selection. Remember that comments are required if Town Hall fails inspection.";
		break;
		case 'inspection-complete':
			$alert['status'] = TRUE;
			$alert['type'] = 'success';
			$alert['content'] = "Inspection recorded. This rental is now complete.";
		break;
	}
}
?>
<main class="bg-body-tertiary">
	<form name="form_main" method="post" novalidate>
		<div class="container-lg">
			<div class="row justify-content-center justify-content-lg-between">
				<div class="col-12 col-md-10 col-lg-8 pe-lg-4">
					<?php
					if (isset($_SESSION['alert']) && (isset($alert['status']) && $alert['status'] == TRUE)):
					?>
					<div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show mt-4" role="alert">
						<?php echo $alert['content']; ?>
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
					<?php endif; ?>
					<?php unset($_SESSION['alert']); ?>
					<div class="row border-bottom">
						<div class="col-12">
							<div class="d-flex align-items-center justify-content-between">
								<div><p class="my-2 text-secondary fs-7"><a href="/requests">All requests</a> <span class="mx-2">: :</span> <?php echo $request->request_get('title'); ?></p></div>
							</div>
						</div>
					</div>
					<?php
					// Error for session conflicts
					$message = '';
					if (!empty(array_intersect_key($conflicts, array_flip($sessions)))):
						$message = 'This request contains event conflicts.';
						foreach (array_intersect_key($conflicts, array_flip($sessions)) as $key => $value)
						{
							foreach ($value as $e)
							{
								$newEvent = new Event($e);
								if ($newEvent->event_get('requestID') != $request->request_get('requestID'))
								{
									$newRequest = new Request($newEvent->event_get('requestID'));
									$message .= '<br><a href="/request?id=' . $newRequest->request_get('requestID') . '">' . $newRequest->request_get('title') . '</a>';
								}
							}
						}
					?>
					<div class="alert alert-danger mt-3" role="alert">
						<p class="my-0"><?php echo $message; ?></p>
					</div>
					<?php endif; ?>
					<div class="row">
						<div class="col-12">
							<div class="mt-2 d-lg-none">
								<?php require(TEMPLATES . '/includes/_request_status_indicator_label.php'); ?>
							</div>
							<div class="d-flex justify-content-between align-items-center my-2 pt-3 pb-1">
								<?php if ($mode_edit == "true"): ?>
								<h1 class="text-heading text-primary fw-semibold my-0">Editing request</h1>
								<?php else: ?>
								<h1 class="text-heading text-primary fw-semibold my-0"><?php echo $request->request_get('title'); ?></h1>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<?php
					// Shortcut button for Town Hall inspections
					if ($request->request_get('status') == 'ended_inspect'):
					?>
					<a href="#" class="btn btn-success d-block d-sm-inline-flex d-lg-none px-4 py-2 my-3" data-bs-toggle="modal" data-bs-target="#modal_inspect" aria-expanded="false" aria-controls="modal_inspect">Town Hall inspection</a>
					<?php endif; ?>


					<?php if ($mode_edit != "true" && $request->is_editable()): ?>
					<div class="row mb-2">
						<div class="col-12 justify-content-md-end d-flex">
							<a href="/request?id=<?php echo $request->request_get('requestID'); ?>&edit=true" class="btn btn-outline-secondary btn-sm"><span class="bi bi-pencil me-2"></span> Edit <span class="d-none d-md-inline">information</span></a>
						</div>
					</div>
					<?php endif; ?>
					<?php if ($mode_edit == "true" && !$request->is_prepayment()): ?>
					<div class="alert alert-warning" role="alert">
						Note: editing a request once payment is pending may not change the information on the invoice.
					</div>
					<?php endif; ?>
					<div class="row pt-3 pb-2">
						<div class="col-12">
							<h2 class="text-heading text-primary fs-3 border-bottom border-secondary-subtle pb-2 fs-4">Contact information</h2>
						</div>
					</div>
					<?php if ($mode_edit == "true"): ?>
					<!-- First name -->
					<div class="row mb-2 align-items-center">
						<div class="col-3 text-end">
							<label for="name_first" class="form-label my-0">First name <span class="text-danger">*</span></label>
						</div>
						<div class="col-8">
							<input type="text" class="form-control form_request_contact" name="name_first" id="name_first" value="<?php echo $customer->customer_get('name_first'); ?>" required>
							<div class="invalid-feedback">First name is required.</div>
						</div>
					</div>
					<!-- Last name -->
					<div class="row mb-2 align-items-center">
						<div class="col-3 text-end">
							<label for="name_last" class="form-label">Last name <span class="text-danger">*</span></label>
						</div>
						<div class="col-8">
							<input type="text" class="form-control form_request_contact" name="name_last" id="name_last" value="<?php echo $customer->customer_get('name_last'); ?>" required>
							<div class="invalid-feedback">Your last name is required.</div>
						</div>
					</div>
					<!-- Email -->
					<div class="row mb-2 align-items-center">
						<div class="col-3 text-end">
							<label for="email" class="form-label">Email <span class="text-danger">*</span></label>
						</div>
						<div class="col-8">
							<input type="email" class="form-control form_request_contact" name="email" id="email" value="<?php echo $customer->customer_get('email'); ?>" required>
							<div class="invalid-feedback">Your email address is required.</div>
						</div>
					</div>
					<!-- Phone -->
					<div class="row mb-2 align-items-center">
						<div class="col-3 text-end">
							<label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
						</div>
						<div class="col-8">
							<input type="tel" class="form-control form_request_contact" name="phone" id="phone" value="<?php echo $customer->customer_get('phone'); ?>" required>
							<div class="invalid-feedback">Your phone number is required.</div>
						</div>
					</div>
					<!-- Organization -->
					<div class="row mb-2 align-items-center">
						<div class="col-3 text-end">
							<label for="organization" class="form-label">Organization</label>
						</div>
						<div class="col-8">
							<input type="text" class="form-control form_request_organization" name="organization" id="organization" value="<?php echo $customer->customer_get('organization'); ?>">
							<div class="form-text">If applicable</div>
						</div>
					</div>
					<!-- Is WPCL -->
					<?php
					// Set HTML checked indicators
					$print['form_wpcl_member']	= (isset($request_info['customer_is_wpcl_member'])) ? $request_info['customer_is_wpcl_member'] : $request->request_get('wpcl_member');
					?>
					<div class="row mt-4 mb-2 align-items-center">
						<div class="col-3 text-end"></div>
						<div class="col-9">
							<p class="my-0">Is requestor a Woodruff Place Civic League member? <span class="text-danger">*</span></p>
							<div class="form-check">
								<input class="form-check-input form_request_contact" type="radio" name="wpcl_member" id="form_wpcl_member_yes" value="y" required <?php echo Utility::form_check_if_checked($print['form_wpcl_member'], '1', 'checked'); ?>>
								<label class="form-check-label" for="form_wpcl_member_yes">Yes</label>
							</div>
							<div class="form-check">
								<input class="form-check-input form_request_contact" type="radio" name="wpcl_member" id="form_wpcl_member_no" value="n" required <?php echo Utility::form_check_if_checked($print['form_wpcl_member'], '0', 'checked'); ?>>
								<label class="form-check-label" for="form_wpcl_member_no">No</label>
							</div>
						</div>
					</div>
					<?php
					// Set sponsor array
					$sponsor = $request->request_get('wpcl_sponsor');
					// Set HTML visibility class
					$html_display = ($print['form_wpcl_member'] == '0') ? '' : 'd-none';
					// Set array to use for printing the form field values
					$print['sponsor_name_first']	= (isset($request_info['wpcl_sponsor_name_first']))	? $request_info['wpcl_sponsor_name_first']	: $sponsor['name_first'];
					$print['sponsor_name_last']		= (isset($request_info['wpcl_sponsor_name_last']))	? $request_info['wpcl_sponsor_name_last']	: $sponsor['name_last'];
					$print['sponsor_email']			= (isset($request_info['wpcl_sponsor_email']))		? $request_info['wpcl_sponsor_email']		: $sponsor['email'];
					$print['sponsor_phone']			= (isset($request_info['wpcl_sponsor_phone']))		? $request_info['wpcl_sponsor_phone']		: $sponsor['phone'];
					?>
					<div class="form_block_sponsor <?php echo $html_display; ?>">
						<div class="row pt-2 pb-3">
							<div class="col-12">
								<h3 class="text-heading text-secondary my-0 pb-2 fs-5">WPCL sponsor information</h3>
							</div>
						</div>
						<!-- WPCL Sponsor First name -->
						<div class="row mb-2 align-items-center">
							<div class="col-3 text-end">
								<label for="form_name_first" class="form-label my-0">Sponsor first name <span class="text-danger">*</span></label>
							</div>
							<div class="col-8">
								<input type="text" class="form-control form_request_contact" name="wpcl_sponsor_name_first" id="form_name_first" value="<?php echo $print['sponsor_name_first']; ?>" required>
								<div class="invalid-feedback">First name is required.</div>
							</div>
						</div>
						<!-- WPCL Sponsor Last name -->
						<div class="row mb-2 align-items-center">
							<div class="col-3 text-end">
								<label for="form_name_last" class="form-label">Sponsor last name <span class="text-danger">*</span></label>
							</div>
							<div class="col-8">
								<input type="text" class="form-control form_request_contact" name="wpcl_sponsor_name_last" id="form_name_last" value="<?php echo $print['sponsor_name_last']; ?>" required>
								<div class="invalid-feedback">Your last name is required.</div>
							</div>
						</div>
						<!-- WPCL Sponsor Email -->
						<div class="row mb-2 align-items-center">
							<div class="col-3 text-end">
								<label for="form_email" class="form-label">Sponsor email <span class="text-danger">*</span></label>
							</div>
							<div class="col-8">
								<input type="email" class="form-control form_request_contact" name="wpcl_sponsor_email" id="form_email" value="<?php echo $print['sponsor_email']; ?>" required>
								<div class="invalid-feedback">Your email address is required.</div>
							</div>
						</div>
						<!-- WPCL Sponsor Phone -->
						<div class="row mb-2 align-items-center">
							<div class="col-3 text-end">
								<label for="form_phone" class="form-label">Sponsor phone <span class="text-danger">*</span></label>
							</div>
							<div class="col-8">
								<input type="tel" class="form-control form_request_contact" name="wpcl_sponsor_phone" id="form_phone" value="<?php echo $print['sponsor_phone'] ?>" required>
								<div class="invalid-feedback">Your phone number is required.</div>
							</div>
						</div>
					</div>
					<?php else: ?>
					<div class="row mb-2">
						<div class="col-6 col-md-3">
							<p class="my-0 text-black-50">First name</p>
							<p class="my-0 text-body-emphasis fs-6 fw-light"><?php echo $customer->customer_get('name_first'); ?></p>
						</div>
						<div class="col-6 col-md-3">
							<p class="my-0 text-black-50">Last name</p>
							<p class="my-0 text-body-emphasis fs-6 fw-light"><?php echo $customer->customer_get('name_last'); ?></p>
						</div>
						<div class="col-6 col-md-3">
							<p class="my-0 text-black-50">Email</p>
							<p class="my-0 text-body-emphasis fs-6 fw-light">
							<?php echo (Utility::is_valid_email($customer->customer_get('email'))) ? '<a href="mailto:' . $customer->customer_get('email') .'" class="text-primary">' . $customer->customer_get('email') . '</a>' : $customer->customer_get('email'); ?>
							</p>
						</div>
						<div class="col-6 col-md-3">
							<p class="my-0 text-black-50">Phone</p>
							<p class="my-0 text-body-emphasis fs-6 fw-light"><?php echo $customer->customer_get('phone'); ?></p>
						</div>
						<?php if (!empty($customer->customer_get('organization'))): ?>
						<div class="col-6 col-md-3 mt-3">
							<p class="my-0 text-black-50">Organization</p>
							<p class="my-0 text-body-emphasis fs-6 fw-light"><?php echo $customer->customer_get('organization'); ?></p>
						</div>
						<?php endif; ?>
					</div>
					<div class="row pt-1">
						<div class="col-12">
							<div class="d-flex justify-content-between align-items-center border-bottom border-top py-3">
								<p class="my-0">Is requestor a Woodruff Place Civic League member? <span class="text-danger">*</span></p>
								<p class="my-0"><?php echo Utility::print_yes_no($request->request_get('wpcl_member')); ?></p>
							</div>
						</div>
					</div>
					<!-- WPCL sponsor info -->
					<?php if ($request->request_get('wpcl_member') != '1'): ?>
					<?php
					$sponsor = $request->request_get('wpcl_sponsor');
					?>
					<div class="row pt-4 pb-1">
						<div class="col-12">
							<h3 class="text-heading text-secondary pb-2 fs-5">Woodruff Place Civic League member sponsor</h3>
						</div>
					</div>
					<div class="row mb-2">
						<div class="col-6 col-md-3">
							<p class="my-0 text-black-50">First name</p>
							<p class="my-0 text-body-emphasis fs-6 fw-light"><?php echo $sponsor['name_first']; ?></p>
						</div>
						<div class="col-6 col-md-3">
							<p class="my-0 text-black-50">Last name</p>
							<p class="my-0 text-body-emphasis fs-6 fw-light"><?php echo $sponsor['name_last']; ?></p>
						</div>
						<div class="col-6 col-md-3">
							<p class="my-0 text-black-50">Email</p>
							<p class="my-0 text-body-emphasis fs-6 fw-light"><?php echo $sponsor['email']; ?></p>
						</div>
						<div class="col-6 col-md-3">
							<p class="my-0 text-black-50">Phone</p>
							<p class="my-0 text-body-emphasis fs-6 fw-light"><?php echo $sponsor['phone']; ?></p>
						</div>
					</div>
					<?php elseif ($request->request_get('wpcl_member') != '1'): ?>
					<div class="row pt-5 pb-2">
						<div class="col-12">
							<p><strong>Something went wrong</strong><br>The requestor is not a Woodruff Place Civic League member and a sponsor has not been indicated.</p>
						</div>
					</div>
					<?php endif; ?>
					<?php endif; ?>
					<div class="row pt-5 pb-2">
						<div class="col-12">
							<h2 class="text-heading text-primary fs-3 border-bottom border-secondary-subtle pb-2 fs-4">Event information</h2>
						</div>
					</div>
					<?php if ($mode_edit == "true"): ?>
					<!-- Event title -->
					<div class="row mb-2 align-items-center">
						<div class="col-3 text-end">
							<label for="form_event_title" class="form-label my-0">Event title <span class="text-danger">*</span></label>
						</div>
						<div class="col-8">
							<input type="text" class="form-control" name="event_title" id="form_event_title" value="<?php echo $request->request_get('title'); ?>" required>
							<div class="invalid-feedback">Event title is required.</div>
						</div>
					</div>
					<!-- Is WP neighborhood event -->
					<?php
					// This field is no longer editable once the request is approved
					$field_wp_event_disabled = ($request->is_sessions_editable()) ? "" : "disabled";
					?>
					<div class="row mt-4 mb-2 align-items-center">
						<div class="col-3 text-end"></div>
						<div class="col-8">
							<label for="form_event_title" class="form-label my-0 mb-2">Is this for a Woodruff Place neighborhood function? <span class="text-danger">*</span><span class="d-block text-secondary fs-7">(e.g. Civic League, Foundation, EID, committee)</span></label>
							<div class="form-check">
								<input class="form-check-input form_request_contact" type="radio" name="is_wp_event" id="is_wp_event_yes" value="y" required <?php echo Utility::form_check_if_checked($request->request_get('is_wp_event'), '1', 'checked'); ?> <?php echo $field_wp_event_disabled; ?>>
								<label class="form-check-label" for="is_wp_event_yes">Yes</label>
							</div>
							<div class="form-check">
								<input class="form-check-input form_request_contact" type="radio" name="is_wp_event" id="is_wp_event_no" value="n" required <?php echo Utility::form_check_if_checked($request->request_get('is_wp_event'), '0', 'checked'); ?> <?php echo $field_wp_event_disabled; ?>>
								<label class="form-check-label" for="is_wp_event_no">No</label>
							</div>
							<?php if (!$request->is_sessions_editable()): ?>
							<div class="alert alert-info p-2 border-1 mt-2">
								<p class="my-0 fs-7 fst-italic">Note: this setting is no longer editable.</p>
							</div>
							<?php endif; ?>
						</div>
					</div>
					<?php else: ?>
					<div class="row">
						<div class="col-12">
							<p class="my-0 text-black-50">Event title</p>
							<p class="my-0 text-body-emphasis fs-5 fw-normal"><?php echo $request->request_get('title'); ?></p>
						</div>
					</div>
					<div class="row mt-3 mb-4">
						<div class="col-12">
							<div class="d-flex justify-content-between align-items-center border-bottom border-top py-2">
							<p class="my-0">Is this for a Woodruff Place neighborhood function? <span class="text-danger">*</span><span class="d-block text-secondary fs-7">(e.g. Civic League, Foundation, EID, committee)</span></p>
							<p class="my-0"><?php echo Utility::print_yes_no($request->request_get('is_wp_event')); ?></p>
							</div>
						</div>
					</div>
					<?php endif; ?>
					<div class="row pt-3 pb-1 mb-2">
						<div class="col-12">
							<div class="d-flex justify-content-between align-items-center">
								<h3 class="text-heading text-secondary my-0 py-0 fs-5">Event sessions</h3>
								<?php if ($mode_edit != "true" && $request->is_sessions_editable()): ?>
								<button type="button" class="btn btn-outline-secondary btn-sm px-4 fs-7" id="form_block_session_add" data-bs-toggle="modal" data-bs-target="#modal_session_add_edit">Add session</button>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<?php
					// Check for any waived sessions
					$sessions_waived = $request->request_return_sessions_discounted($sessions);

					// Per setting, check if we should max rental charges per day
					if ($GLOBALS['settings']->get('request.one_charge_daily') == "1" && (!empty($sessions_waived) && count($sessions_waived) >= 1)):
					?>
					<div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
						<p class="my-0 fs-7 fst-italic fw-normal">We charge a maximum of one rental fee per day (as determined by the largest event), regardless of whether the customer requests multiple sessions.<br><br>Note: the lower-priced sessions should automatically be fee-waived on customer-requested sessions, but NOT on sessions you add manually. All settings can be overridden. Please double-check prior to approval.</p>
					</div>
					<?php endif; ?>
					<!-- Sessions list -->
					<div class="row">
						<div class="col-12">
							<div class="row p-2 border-bottom border-secondary bg-white">
								<div class="col-4"><span class="text-body-emphasis fw-medium fs-7">Session title</span></div>
								<div class="col-2"><span class="text-body-emphasis fw-medium fs-7">Size</span></div>
								<div class="col-2"><span class="text-body-emphasis fw-medium fs-7">Alcohol</span></div>
								<div class="col-3 ps-4"><span class="text-body-emphasis fw-medium fs-7 ps-3">Session fee</span></div>
								<div class="col-1 d-none"><span class="text-body-emphasis fw-medium fs-7"></span></div>
							</div>
							<?php
							foreach ($sessions as $session):
							$event = new Event($session);
							$fee_rental = (!empty($event->event_get('fee_rental'))) ? new Price($event->event_get('fee_rental')) : null;
							$fee_alcohol = (!empty($event->event_get('fee_alcohol'))) ? new Price($event->event_get('fee_alcohol')) : null;
							?>
							<div class="row p-2 bg-light-subtle border-bottom border-light-subtle align-items-center">
								<div class="col-4">
									<span class="text-body-emphasis fw-medium fs-7"><?php echo $event->event_get('title'); ?></span>
									<span class="d-block">
										<?php if ($event->event_shares_start_end_date()): ?>
											<span class="d-block text-secondary fs-7"><?php echo date('M j, Y', strtotime($event->event_get('event_start'))) . " <span class=\"d-block\">" . date('g:i a', strtotime($event->event_get('event_start'))) . " &ndash; " . date('g:i a', strtotime($event->event_get('event_end'))) . "</span>"; ?></span>
										<?php else: ?>
										<span class="text-secondary-emphasis fs-7 d-block">Start: <span class="text-secondary fs-7"><?php echo date('M j, Y g:i a', strtotime($event->event_get('event_start'))); ?> </span></span>
										<span class="text-secondary-emphasis fs-7 d-block">End: <span class="text-secondary fs-7"><?php echo date('M j, Y g:i a', strtotime($event->event_get('event_end'))); ?></span></span>
										<?php endif; ?>
									</span>
									<?php if (!empty($conflicts) && in_array($event->event_get('eventID'), array_keys($conflicts))): ?>
									<span class="badge rounded-pill text-bg-danger fs-8">Conflict</span>
									<?php endif; ?>
								</div>
								<?php $css_fee_rental = ($event->event_get('fee_waiver_rental') == "1") ? "text-decoration-line-through" : ""; ?>
								<div class="col-2"><span class="text-body fs-7"><?php echo $fee_rental->price_get('field_request_label'); ?></span><span class="d-block text-secondary fs-7 <?php echo $css_fee_rental; ?>"><?php echo "$" . round($fee_rental->price_get('amount'), 2); ?></span></div>
								<div class="col-2"><span class="text-body fs-7"><?php echo (!empty($event->event_get('fee_alcohol'))) ? "Yes" : "No"; ?></span>
								<?php if (!empty($event->event_get('fee_alcohol'))) : ?>
								<?php $css_fee_alcohol = ($event->event_get('fee_waiver_alcohol') == "1") ? "text-decoration-line-through" : ""; ?>
								<span class="text-secondary fs-7 d-block <?php echo $css_fee_alcohol; ?>"><?php echo "$" . round($fee_alcohol->price_get('amount'), 2); ?></span>
								<?php endif; ?>
								</div>
								<div class="col-2 ps-4"><span class="text-body fs-7 ps-3"><?php echo "$" . $event->event_get_total_cost(); ?></span></div>
								<div class="col-2 text-end">
									<?php if ($mode_edit != "true" && $request->is_sessions_editable()): ?>
									<div class="dropdown">
										<button type="button" class="btn btn-outline-secondary btn-sm px-3" data-name="form_block_session_remove_trigger" data-bs-toggle="dropdown" aria-expanded="false"><span class="bi bi-three-dots"></span></button>
										<ul class="dropdown-menu">
											<li>
												<button type="button" class="dropdown-item form_block_event_session_edit" data-bs-toggle="modal" data-bs-target="#modal_session_add_edit" data-bs-action="edit" data-bs-itemtoedit="<?php echo $event->event_get('eventID'); ?>"><span class="bi bi-pencil me-2 text-primary"></span> Edit</button>
											</li>
											<li>
												<input type="hidden" name="session" value="">
												<button type="button" name="session_remove" class="dropdown-item form_block_session_remove" data-bs-toggle="modal" data-bs-target="#modal_session_delete" data-session-to-remove="<?php echo $event->event_get('eventID'); ?>"><span class="bi bi-trash me-2 text-danger"></span> Remove</button>
											</li>
										</ul>
									</div>
									<?php endif; ?>
								</div>
							</div>
							<?php endforeach; ?>
						</div>
					</div>
					<!-- End Sessions list -->
					<div class="row pt-5 pb-2">
						<div class="col-12">
							<div class="d-flex justify-content-between align-items-center ">
								<h2 class="text-heading text-primary fs-3  fs-4">Administrative notes</h2>
								<?php if ($mode_edit != "true" && $request->is_notes_editable()): ?>
								<button type="button" class="btn btn-outline-secondary btn-sm btn-request-notes-edit"><span class="bi bi-pencil me-2"></span> Edit <span class="d-none d-md-inline">notes</span></button>
								<?php endif; ?>
							</div>
							<p class="text-secondary">Internal only. These are never sent or shown to the requesting party.</p>
						</div>
					</div>
					<!-- View mode -->
					<div class="row mb-2 admin_notes_view">
						<div class="col-12">
							<p class="my-0 mb-2 text-black-50">Internal notes</p>
							<div class="card w-100 p-4">
								<?php if (!empty($request->request_get('notes_event'))): ?>
								<?php echo nl2br($request->request_get('notes_event')); ?>
								<?php else: ?>
								<span class="text-body-tertiary fst-italic">No notes</span>
								<?php endif; ?>
							</div>
							<p class="mt-3 mb-2 text-black-50">Fee waivers</p>
							<div class="card w-100 p-4">
								<?php if (!empty($request->request_get('notes_fee_waiver'))): ?>
								<?php echo $request->request_get('notes_fee_waiver'); ?>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<!-- Edit mode -->
					<div class="admin_notes_edit d-none">
						<div class="row">
							<div class="col-12">
								<p class="my-0 mb-2 text-primary">Add an internal note</p>
								<div class="form-floating">
									<?php $text_fill_events = (!empty($request->request_get('notes_event'))) ? $request->request_get('notes_event') : ""; ?>
									<textarea class="form-control" placeholder="Add notes..." name="notes_event" id="notes"><?php echo trim($text_fill_events); ?></textarea>
									<label for="notes">Internal notes</label>
								</div>
								<p class="my-3 text-primary">Describe any fee waivers</p>
								<div class="form-floating">
									<?php $text_fill_feewaiver = (!empty($request->request_get('notes_fee_waiver'))) ? $request->request_get('notes_fee_waiver') : ""; ?>
									<textarea class="form-control" placeholder="Add notes..." name="notes_feewaiver" id="fee_waiver_notes"><?php echo trim($text_fill_feewaiver); ?></textarea>
									<label for="fee_waiver_notes">Fees waived</label>
								</div>
								<div class="form-text">Context for any fees waived</div>
							</div>
						</div>
						<div class="row mt-2 justify-content-center justify-content-md-end">
							<div class="col-10 col-md-7 text-end">
								<div class="d-flex flex-column flex-md-row-reverse">
									<button type="submit" name="action" value="request_save_notes" id="request_save_notes" class="btn btn-primary text-white btn-sm px-4 px-md-5 flex-fill fs-6 mb-4 mb-md-0">Save notes</button>
									<button type="button" class="btn btn-outline-primary btn-sm btn-request-notes-view px-4 flex-fill fs-6 me-md-3 me-xl-4">Cancel</button>
								</div>
							</div>
						</div>
					</div>
					<!-- <div class="row mb-2 align-items-center">
						<div class="col-3 text-end">
							<label for="form_event_title" class="form-label my-0">Event title <span class="text-danger">*</span></label>
						</div>
						<div class="col-8">
							<input type="text" class="form-control" name="event_title" id="form_event_title" value="<?php echo $request->request_get('title'); ?>" required>
							<div class="invalid-feedback">Event title is required.</div>
						</div>
					</div> -->
					<div class="my-5"></div>
					<?php if ($mode_edit == "true"): ?>
					<div class="row my-5 pt-4 border-top justify-content-center justify-content-md-end">
						<div class="col-12 col-sm-9 col-md-7">
							<div class="d-flex flex-column flex-md-row-reverse">
								<button type="submit" name="action" value="request_save" id="request_save" class="btn btn-primary text-white btn-lg px-4 px-md-5 flex-fill fs-6 mb-4 mb-md-0">Save</button>
								<a href="/request?id=<?php echo $request->request_get('requestID'); ?>" class="btn btn-outline-primary btn-lg px-4 flex-fill fs-6 me-md-3 me-xl-4">Cancel</a>
							</div>
						</div>
					</div>
					<?php endif; ?>
				</div>
				<div class="col-12 col-lg-4 bg-body-secondary bg-opacity-50">
					<div class="sticky-top ps-2">
						<div class="mt-2 pt-1 d-none d-lg-block">
							<?php require(TEMPLATES . '/includes/_request_status_indicator_label.php'); ?>
						</div>
						<div class="row">
							<div class="col-12 order-2 order-md-0">
								<?php if ($mode_edit != "true" && $request->is_actionable()): ?>
								<h3 class="text-heading text-primary-emphasis fw-light h4 mt-0 pt-3 pb-0 mb-2 border-bottom border-secondary-subtle">Actions</h3>
									<!-- Status edit switch -->
									<?php if ($request->is_status_editable()): ?>
									<div class="row justify-content-center mt-4 mb-4">
										<div class="col-12">
											<div class="d-flex flex-column">
												<?php //echo $request->request_get('status'); ?>
												<label for="request_status_switch" class="form-text mb-1">Change request status</label>
												<select class="form-select mb-3" aria-label="Change request status" name="request_status_switch" id="request_status_switch">
													<option value="initiated" <?php echo Utility::form_check_if_checked('initiated', $request->request_get('status'), 'selected'); ?>>Initiated</option>
													<option value="review" <?php echo Utility::form_check_if_checked('review', $request->request_get('status'), 'selected'); ?>>In review</option>
													<option value="approved" <?php echo Utility::form_check_if_checked('approved', $request->request_get('status'), 'selected'); ?>>Approved</option>
													<option value="denied">Denied</option>
												</select>
												<!-- Alert messages -->
												<!-- Approve -->
												<p class="my-0 mb-3 fw-medium fs-7 fst-italic text-primary d-none alert_transition" id="alert_transition_approved">Marking a request as "approved" will send an invoice to the customer. Line items and fees are no longer editable.</p>
												<!-- Denied -->
												<p class="my-0 mb-3 fw-medium fs-7 fst-italic text-danger d-none alert_transition" id="alert_transition_denied">Marking a request as "denied" at any point is a terminal state; customer will need to re-submit request.</p>
												<button type="submit" name="action" value="request_route" id="request_status_transition" class="btn <?php echo Request::status_display($request->request_get('status'))['color-btn']; ?> btn-lg px-4 flex-fill px-md-5 fs-6">Save request</button>
											</div>
										</div>
									</div>
									<?php endif; ?>
									<!-- Cancellation button -->
									<?php if ($request->is_cancelable()): ?>
									<ul class="mt-3">
										<li><a href="#" class="text-danger" data-bs-toggle="collapse" data-bs-target="#event_actions" aria-expanded="false" aria-controls="event_actions">Cancel event</a></li>
									</ul>
									<div id="event_actions" class="accordion-collapse collapse" data-bs-parent="#event_actions_section">
										<div class="ms-3">
											<button type="button" data-bs-toggle="modal" data-bs-target="#modal_event_cancel" class="btn btn-outline-danger btn-sm px-4 flex-fill mx-auto px-md-5 fs-7">Cancel event</button>
										</div>
									</div>
									<?php endif; ?>
									<!-- Inspection -->
									<?php if ($request->request_get('status') == 'ended_inspect'): ?>
									<ul class="mt-3">
										<li><a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#modal_inspect" aria-expanded="false" aria-controls="modal_inspect">Town Hall inspection</a></li>
									</ul>
									<?php endif; ?>
								<?php endif; ?>
							</div>
							<div class="col-12">
								<h3 class="text-heading text-primary-emphasis fw-light h4 mt-1 pt-4 pb-2 mb-3 border-bottom border-secondary-subtle">Rental summary</h3>
								<?php
								// Form initial state or no sessions in list
								$total = "-";
								$total = 0;
								// Loop the sessions
								foreach ($sessions as $session):
								$event = new Event($session);
								$fee_rental = (!empty($event->event_get('fee_rental'))) ? new Price($event->event_get('fee_rental')) : null;
								$fee_alcohol = (!empty($event->event_get('fee_alcohol'))) ? new Price($event->event_get('fee_alcohol')) : null;
								?>
								<!-- Row per session -->
								<div class="mb-2 pb-2 border-bottom border-secondary-subtle">
									<div class="d-flex justify-content-between align-items-center">
										<div class="left fw-medium fs-6">
											<span class="fw-medium"><?php echo $event->event_get('title'); ?></span>
										</div>
										<div class="right fw-light"></div>
									</div>
									<div class="d-flex justify-content-between align-items-center">
										<div class="left fw-light fs-7">
											<span class="fs-7">Rental fee</span> <span class="text-secondary">(<?php echo $fee_rental->price_get('field_request_label'); ?> people)</span>
											<?php if ($event->event_get('fee_waiver_rental') == "1"): ?>
											<span class="badge rounded-pill text-bg-primary fs-8">Waived</span>
											<?php endif; ?>
										</div>
										<div class="right fw-light">
											<div class="request_item_cost">
												<?php echo "$" . round($event->event_get_fee_cost('rental'), 2); ?>
											</div>
										</div>
									</div>
									<?php if (!empty($conflicts) && in_array($event->event_get('eventID'), array_keys($conflicts))): ?>
									<span class="badge rounded-pill text-bg-danger fs-9">Conflict</span>
									<?php endif; ?>
									<?php if (!empty($event->event_get('fee_alcohol'))) : ?>
									<div class="d-flex justify-content-between align-items-center">
										<div class="left fw-light fs-6">
											<span class="fs-7">Alcohol served</span>
											<?php if ($event->event_get('fee_waiver_alcohol') == "1"): ?>
											<span class="badge rounded-pill text-bg-primary fs-8">Waived</span>
											<?php endif; ?>
										</div>
										<div class="right fw-light"><div class="request_item_cost"><?php echo "$" . round($event->event_get_fee_cost('alcohol'), 2); ?></div></div>
									</div>
									<?php //$total = $total + intval($fee_alcohol->price_get('amount')); ?>
									<?php endif; ?>
								</div>
								<?php $total = $total + intval(round($event->event_get_total_cost())); ?>
								<?php endforeach; ?>
								<!-- End row per session -->
								<!-- Cleaning fee, if applicable -->
								<?php
								// Initialize total_unwaived
								$total_unwaived = $total;
								// Determine the cleaning fee and security deposit (if any)
								$fee_cleaning = new Price($request->request_get('cleaning_fee'));
								if (intval($fee_cleaning->price_get('amount')) > 0):
								$form_waivers_disable_cleaning = (!$request->is_prepayment() || $request->request_get('is_wp_event') == "1") ? "disabled" : "";
								?>
								<div class="mb-2 pb-2 border-bottom border-secondary-subtle">
									<div class="d-flex justify-content-between">
										<div class="left fs-6">
											<span class="fw-normal">Cleaning fee</span>
										</div>
										<div class="right fw-light">
											<div id="form_display_cost_cleaning" class="<?php if ($request->fee_waived('cleaning') == true){ echo "d-none"; } ?> fee_waived_cleaning_hide request_item_cost"><?php echo '$' . round($fee_cleaning->price_get('amount')); ?></div>
											<div class="<?php if ($request->fee_waived('cleaning') != true){ echo "d-none"; } ?> fee_waived_cleaning_show"><s class="text-secondary fee_waive_cleaning_show"><?php echo '$' . round($fee_cleaning->price_get('amount')); ?></s> $0</div>
										</div>
									</div>
									<div class="mt-2 form-check form-switch">
										<input	class="form-check-input"
												type="checkbox"
												role="switch"
												value="y"
												name="form_fee_cleaning_waiver"
												<?php echo $form_waivers_disable_cleaning; ?>
												<?php echo Utility::form_check_if_checked($request->fee_waived('cleaning'), true, 'checked'); ?>
												id="form_fee_cleaning_waiver">
										<label class="form-check-label text-secondary fs-7" for="form_fee_cleaning_waiver">Waive cleaning fee</label>
									</div>
								</div>
								<?php
								// Add cleaning fee to the total
								$fee_cleaning_total = ($request->fee_waived('cleaning') == true) ? 0 : intval($fee_cleaning->price_get('amount'));
								// Record total fee w/o waivers
								$total_unwaived = $total_unwaived + intval($fee_cleaning->price_get('amount'));
								// Record display total
								$total = $total + $fee_cleaning_total;
								?>
								<?php endif; ?>
								<!-- End cleaning fee -->
								<!-- Security deposit, if applicable -->
								<?php
								$fee_security = new Price($request->request_get('security_deposit'));
								if (intval($fee_security->price_get('amount')) > 0):
								$form_waivers_disable_security = (!$request->is_prepayment() || $request->request_get('is_wp_event') == "1") ? "disabled" : "";
								?>
								<div class="mb-2 pb-2 border-bottom border-secondary-subtle">
									<div class="d-flex justify-content-between">
										<div class="left fs-6">
											<span class="fw-normal">Security deposit</span>
										</div>
										<div class="right fw-light">
											<div id="form_display_cost_security" class="<?php if ($request->fee_waived('security') == true){ echo "d-none"; } ?> fee_waived_security_hide request_item_cost"><?php echo '$' . round($fee_security->price_get('amount')); ?></div>
											<div class="<?php if ($request->fee_waived('security') != true){ echo "d-none"; } ?> fee_waived_security_show"><s class="text-secondary"><?php echo '$' . round($fee_security->price_get('amount')); ?></s> $0</div>
										</div>
									</div>
									<div class="mt-2 form-check form-switch">
										<input	class="form-check-input"
												type="checkbox"
												role="switch"
												value="y"
												name="form_fee_security_waiver"
												<?php echo $form_waivers_disable_security; ?>
												<?php echo Utility::form_check_if_checked($request->fee_waived('security'), true, 'checked'); ?>
												id="form_fee_security_waiver">
										<label class="form-check-label text-secondary fs-7" for="form_fee_security_waiver">Waive security deposit</label>
									</div>
								</div>
								<?php
								// Add security deposit to the total
								$fee_security_total = ($request->fee_waived('security') == true) ? 0 : intval($fee_security->price_get('amount'));
								// Record total fee w/o waivers
								$total_unwaived = $total_unwaived + intval($fee_security->price_get('amount'));
								// Record display total
								$total = $total + $fee_security_total;
								?>
								<?php endif; ?>
								<!-- End security deposit -->
								<div class="d-flex justify-content-between mb-4">
									<div class="left fs-6">
										<span class="fw-semibold">Total</span>
									</div>
									<div class="right fw-medium">$<span id="form_display_cost_total"><?php echo $total; ?></span></div>
									<div id="form_display_cost_total_unwaived" class="d-none"><?php echo $total_unwaived; ?></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- Modal - Add/modify session -->
			<div class="modal fade" id="modal_session_add_edit" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="add_modify_session_label" aria-hidden="true">
				<div class="modal-dialog modal-dialog-scrollable modal-fullscreen-lg-down">
					<div class="modal-content">
						<div class="modal-header bg-primary">
							<h2 class="modal-title fs-5 text-white" id="add_modify_session_label">Add session</h2>
							<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<!-- Begin session block -->
							<div class="form_block_session">
								<div class="row justify-content-center">
									<div class="col-12">
										<div class="row">
											<div class="col-12">
												<label for="form_session_name" class="form-label text-primary">Session name <span class="text-danger">*</span></label>
												<input type="text" class="form-control" name="session_name" id="form_session_name" value="" required>
												<div class="form-text">Helpful if you require more than one rental period as part of your request.</div>
												<div class="invalid-feedback">The name is required.</div>
											</div>
										</div>
										<div class="row mt-4 pt-3">
											<div class="col-12">
												<h4 class="form-label fw-normal fs-6 border-bottom border-secondary-subtle pb-2 mb-3 text-primary">Session start</h4>
											</div>
										</div>
										<div class="row">
											<div class="col-12 col-md-6">
												<label for="form_event_date_start_1" class="form-label text-secondary">Start date</label>
												<input type="date" name="session_start_date" class="form-control form_event_date_select" id="form_event_date_start_1" required>
											</div>
											<div class="col-12 col-md-6">
												<label for="form_event_time_start_1" class="form-label text-secondary">Start time</label>
												<input type="time" name="session_start_time" class="form-control form_event_date_select" id="form_event_time_start_1" required>
											</div>
										</div>
										<div class="row mt-4 pt-3">
											<div class="col-12">
												<h4 class="form-label fw-normal fs-6 border-bottom border-secondary-subtle pb-2 mb-3 text-primary">Session end</h4>
											</div>
										</div>
										<div class="row">
											<div class="col-12 col-md-6">
												<label for="form_event_date_end_1" class="form-label text-secondary">End date</label>
												<input type="date" name="session_end_date" class="form-control" id="form_event_date_end_1" required>
											</div>
											<div class="col-12 col-md-6">
												<label for="form_event_time_end_1" class="form-label text-secondary">End time</label>
												<input type="time" name="session_end_time" class="form-control" id="form_event_time_end_1" required>
											</div>
										</div>
										<div class="row mt-4 pt-3">
											<div class="col-12">
												<label for="form_event_attendance" class="form-label text-primary">Attendance</label>
												<select class="form-select form_event_attendance" name="session_attendance" required>
													<option selected disabled value="">-</option>
													<?php
													// Town Hall rental = Product ID 1
													$rental = new Product(1);
													$rental_prices = $rental->product_get_prices();
													// Render an option for each active price
													foreach ($rental_prices as $priceID):
													$price = new Price($priceID);
													?>
													<option value="<?php echo $price->price_get('priceID'); ?>"><?php echo $price->price_get('field_request_label'); ?> &bull; $<?php echo $price->price_get('amount'); ?></option>
													<?php endforeach; ?>
												</select>
												<div class="form-text" id="basic-addon4">Attendance (event size) determines base rental fee.</div>
											</div>
										</div>
										<div class="row mt-4 pt-3">
											<div class="col-12">
												<label for="form_event_alcohol" class="form-label text-primary">Will alcohol be served during this session?</label>
												<select class="form-select form_event_alcohol" name="session_alcohol" required>
													<option selected disabled>-</option>
													<option value="y">Yes</option>
													<option value="n">No</option>
												</select>
												<div class="form-text">Alcohol fee: <?php echo "$" . Price::price_get_amount(4); ?></div>
											</div>
										</div>
										<div class="row mt-4 pt-3">
											<div class="col-12">
												<p class="text-danger my-0 mb-2">Fee waivers</p>
												<?php
												$form_waivers_disable = ($request->request_get('is_wp_event') == "1") ? "disabled" : "";
												?>
												<div class="form-check form-switch mb-2" id="form-session-switch-rental">
													<input class="form-check-input" type="checkbox" role="switch" name="fee_waiver_rental" id="fee_waiver_rental" value="y" <?php echo $form_waivers_disable; ?>>
													<label class="form-check-label" for="fee_waiver_rental">Waive the <strong>rental fee</strong> for this session</label>
												</div>
												<div class="form-check form-switch" id="form-session-switch-alcohol">
													<input class="form-check-input" type="checkbox" role="switch" name="fee_waiver_alcohol" id="fee_waiver_alcohol" value="y" <?php echo $form_waivers_disable; ?>>
													<label class="form-check-label" for="fee_waiver_alcohol">Waive the <strong>alcohol fee</strong> for this session</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<!-- End session block -->
						</div>
						<div class="modal-footer">
							<input type="hidden" name="session_to_edit" id="session_to_edit" value="">
							<button type="button" class="btn btn-outline-secondary px-4 btn_form_addedit_cancel" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" name="action" value="session_add" class="btn_form_submit_add btn_form_submit_addedit btn btn-primary text-white px-5" disabled>
								Add session
								<span class="btn_form_submit_add_spinner spinner-border spinner-border-sm ms-2 d-none" aria-hidden="true"></span>
							</button>
						</div>
					</div>
				</div>
			</div>
			<!-- Modal - Delete session -->
			<div class="modal fade" id="modal_session_delete" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="label_session_delete" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header bg-danger">
							<h1 class="modal-title fs-5 text-white" id="label_session_delete">Are you sure?</h1>
							<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<p>Are you sure you want to remove this session?</p>
							<input type="hidden" name="session_to_remove" id="session_to_remove" value="">
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" name="action" value="session_remove" class="btn_form_submit_add btn btn-outline-danger px-5">
								Remove session
								<span class="btn_form_submit_add_spinner spinner-border spinner-border-sm ms-2 d-none" aria-hidden="true"></span>
							</button>
						</div>
					</div>
				</div>
			</div>
			<!-- Modal - Cancel event -->
			<div class="modal fade" id="modal_event_cancel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="label_modal_event_cancel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header bg-danger">
							<h1 class="modal-title fs-5 text-white" id="label_modal_event_cancel">Cancel entire event?</h1>
							<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<p>Are you sure you want to cancel this event? <span class="d-block fs-7 fw-bold">This action cannot be undone.</span></p>
							<?php if ($request->request_get('is_wp_event') != "1"): ?>
							<div class="d-flex align-items-start">
								<span class="bi bi-exclamation-triangle-fill text-warning me-2 fs-5"></span>
								<p class="lh-1 text-secondary"><span class="fw-bold text-secondary-emphasis">Note:</span><span class="d-block">As this is a paid event, the Civic League treasurer will receive a notification. Please coordinate with them regarding any refunds.</span></p>
							</div>
							<?php endif; ?>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" name="action" value="event_cancel" class="btn_form_submit_add btn btn-outline-danger px-5">
								Cancel event
								<span class="btn_form_submit_add_spinner spinner-border spinner-border-sm ms-2 d-none" aria-hidden="true"></span>
							</button>
						</div>
					</div>
				</div>
			</div>
			<!-- Modal - Town Hall inspection -->
			<div class="modal fade" id="modal_inspect" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="label_modal_inspect" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header bg-primary">
							<h1 class="modal-title fs-5 text-white" id="label_modal_inspect">Town Hall inspection</h1>
							<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<!-- Inspection approval/denial buttons -->
							<div class="row justify-content-center my-4">
								<div class="col-5 pe-1">
									<input type="radio" class="btn-check" name="inspection" value="approve" id="inspection_approve" autocomplete="off">
									<label class="btn btn-outline-success rounded-2 me-2 text-center w-100" for="inspection_approve">
										<span class="bi bi-hand-thumbs-up fs-0 fw-light"></span>
										<span class="d-block fs-4 fw-light">Pass</span>
									</label>
									<p class="text-secondary-emphasis lh-sm fs-7 mt-4">Town Hall passes inspection. Treasurer will be notified to refund security deposit.</p>
								</div>
								<div class="col-5 ps-1 ms-2">
									<input type="radio" class="btn-check" name="inspection" value="deny" id="inspection_deny" autocomplete="off">
									<label class="btn btn-outline-danger rounded-2 text-center w-100" for="inspection_deny">
										<span class="bi bi-hand-thumbs-down fs-0 fw-light"></span>
										<span class="d-block fs-4 fw-light">Fail</span>
									</label>
									<p class="text-secondary-emphasis lh-sm fs-7 mt-4">Town Hall fails inspection. Security deposit <span class="fw-bold">not</span> refunded.</p>
								</div>
							</div>
							<div class="row justify-content-center">
								<div class="col-10">
									<div class="form-floating">
										<textarea class="form-control" placeholder="Add notes..." name="inspection_comments" id="inspection_comments"></textarea>
										<label for="inspection_comments">Comments</label>
									</div>
									<p class="form-text mt-2 lh-sm">Add any comments. <span class="text-danger">Required</span> if denying the return of security deposit.</p>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" name="action" value="inspection_record" class="btn_form_submit_add btn btn-primary px-5">
								Confirm
								<span class="btn_form_submit_add_spinner spinner-border spinner-border-sm ms-2 d-none" aria-hidden="true"></span>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</main>
