<?php require_once('_session.php'); ?>
<?php if (!isset($page_header)){ Page::redirect_home(); } ?>
<?php
$_SESSION['readypayment'] = false;
/**
 *  Accept form
 */
if (isset($_POST['action']))
{
	switch ($_POST['action'])
	{
		// Add a new session
		case 'session_add':
			$_SESSION['alert'] = 'added';
			FormProcessor::form_collect(FALSE, TRUE);
			header("Location: " . $_SERVER['REQUEST_URI']);
			// Important: terminate script execution after redirect
			exit();
		break;
		// Edit a session
		case 'session_edit':
			$edited_session = Utility::form_field_parse($_POST['session_to_edit']);
			FormProcessor::form_collect($edited_session, TRUE);
			// Set an alert
			$_SESSION['alert'] = 'edited';
		break;
		// Remove session
		case 'session_remove':
			if (is_numeric($_POST['session_to_remove']))
			{
				unset($_SESSION['form']['sessions'][$_POST['session_to_remove']]);
			}
			// Set an alert
			$_SESSION['alert'] = 'removed';
		break;
		case 'form_reset':
			session_destroy();
			header("Location: " . $_SERVER['REQUEST_URI']);
			// Important: terminate script execution after redirect
			exit();
		break;
		case 'route_goback':
			$_SESSION['readypayment'] = FALSE;
		break;
		// Enable the review stage of the request
		case 'route_review':
			$_SESSION['readypayment'] = FALSE;
			FormProcessor::form_collect();

			// Validation
			$valid = Utility::form_response_validate($_SESSION['form']);
			if ($valid !== TRUE)
			{
				$_SESSION['alert'] = ($valid[0] == "sessions") ? 'sessions-null' : 'required-incomplete';
			}
			else
			{
				$_SESSION['readypayment'] = TRUE;
				$_SESSION['alert'] = "review";
			}
		break;
		case 'route_request':
			// Submit request here
			if (FormProcessor::new_request_initiate($_SESSION['form']))
			{
				header("Location: " . '/new/success');
			}
			else
			{
				$_SESSION['alert'] = 'required-incomplete';
			}
		break;
	}
}

// Set alerts
if (isset($_SESSION['alert']))
{
	switch ($_SESSION['alert'])
	{
		case 'info-required':
			$alert['status'] = TRUE;
			$alert['type'] = 'danger';
			$alert['content'] = "All fields for a session are required.";
		break;
		case 'edited':
			$alert['status'] = TRUE;
			$alert['type'] = 'primary';
			$alert['content'] = "Session updated.";
		break;
		case 'removed':
			$alert['status'] = TRUE;
			$alert['type'] = 'info';
			$alert['content'] = "Session removed.";
		break;
		case 'edited':
			$alert['status'] = TRUE;
			$alert['type'] = 'primary';
			$alert['content'] = "Session added.";
		break;
		case 'added':
			$alert['status'] = TRUE;
			$alert['type'] = 'primary';
			$alert['content'] = "Session added.";
		break;
		case 'businesses-null':
			$alert['status'] = TRUE;
			$alert['type'] = 'danger';
			$alert['content'] = "You must add at least one session.";
		break;
		case 'required-incomplete':
			$alert['status'] = TRUE;
			$alert['type'] = 'danger';
			$alert['content'] = "Please double-check required fields.";
		break;
		case 'review':
			$alert['status'] = TRUE;
			$alert['type'] = 'primary';
			$alert['content'] = "Please ensure all information looks accurate.";
		break;
	}
}
?>
<main class="bg-body-tertiary">
	<form name="form_main" method="post" novalidate>
		<div class="container-lg">
			<div class="row justify-content-center justify-content-lg-between">
				<div class="col-12 col-md-10 col-lg-7 pe-lg-2">
					<?php
					if (isset($_SESSION['alert']) && (isset($alert['status']) && $alert['status'] == TRUE)):
					?>
					<div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show mt-4" role="alert">
					<?php echo $alert['content']; ?>
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
					<?php endif; ?>
					<?php unset($_SESSION['alert']); ?>
					<div class="row pt-3 pt-md-4">
						<div class="col-12">
							<h1 class="text-heading text-primary fw-semibold mb-2 pb-3">New Request</h1>
							<p><strong>Before requesting to book your event, </strong>please be sure you’ve read and agree to the<strong> <a href="https://woodruffplace.org/events/town-hall-rental/">Rental Agreement</a></strong>. You <em><strong>must</strong> </em>be a resident of Woodruff Place <strong><em>AND </em></strong>a member of the Woodruff Place Civic League, <em><strong>OR</strong> </em>sponsored by a resident and member. We will contact you once you’ve completed the event form below.</p>
						</div>
					</div>
					<div class="row py-4">
						<div class="col-12">
							<h2 class="text-heading text-primary fs-3 border-bottom border-secondary-subtle pb-2">Contact information</h2>
						</div>
					</div>
					<div class="row mb-2">
						<div class="col-12 col-md-6">
							<label for="form_name_first" class="form-label">First name <span class="text-danger">*</span></label>
							<input type="text" class="form-control form_request_contact" name="name_first" id="form_name_first" value="<?php if (isset($_SESSION['form']['name_first'])){ echo $_SESSION['form']['name_first']; } ?>" required <?php FormProcessor::disable_if_review($_SESSION['readypayment']); ?>>
							<div class="invalid-feedback">Your first name is required.</div>
						</div>
						<div class="col-12 col-md-6">
							<label for="form_name_last" class="form-label">Last name <span class="text-danger">*</span></label>
							<input type="text" class="form-control form_request_contact" name="name_last" id="form_name_last" value="<?php if (isset($_SESSION['form']['name_last'])){ echo $_SESSION['form']['name_last']; } ?>" required <?php FormProcessor::disable_if_review($_SESSION['readypayment']); ?>>
							<div class="invalid-feedback">Your last name is required.</div>
						</div>
					</div>
					<div class="row mb-2">
						<div class="col-12">
							<label for="form_email" class="form-label">Email <span class="text-danger">*</span></label>
							<input type="email" class="form-control form_request_contact" name="email" id="form_email" value="<?php if (isset($_SESSION['form']['email'])){ echo $_SESSION['form']['email']; } ?>" required <?php FormProcessor::disable_if_review($_SESSION['readypayment']); ?>>
							<div class="invalid-feedback">Your email address is required.</div>
						</div>
					</div>
					<div class="row mb-3">
						<div class="col-12">
							<label for="form_email" class="form-label">Phone <span class="text-danger">*</span></label>
							<input type="tel" class="form-control form_request_contact" name="phone" id="form_phone" value="<?php if (isset($_SESSION['form']['phone'])){ echo $_SESSION['form']['phone']; } ?>" required <?php FormProcessor::disable_if_review($_SESSION['readypayment']); ?>>
							<div class="invalid-feedback">Your phone number is required.</div>
						</div>
					</div>
					<div class="row mb-4">
						<div class="col-12">
							<label for="form_organization" class="form-label">Organization</label>
							<input type="text" class="form-control form_request_organization" name="organization" id="form_organization" value="<?php if (isset($_SESSION['form']['organization'])){ echo $_SESSION['form']['organization']; } ?>" <?php FormProcessor::disable_if_review($_SESSION['readypayment']); ?>>
							<div class="form-text">If applicable</div>
						</div>
					</div>
					<div class="row mt-3">
						<div class="col-12">
							<p>Are you a Woodruff Place Civic League member? <span class="text-danger">*</span></p>
							<div class="form-check">
								<input class="form-check-input form_request_contact" type="radio" name="wpcl_member" id="form_wpcl_member_yes" value="y" required <?php if (isset($_SESSION['form']['wpcl_member']) && $_SESSION['form']['wpcl_member'] == 'y'){ echo 'checked'; } ?> <?php FormProcessor::disable_if_review($_SESSION['readypayment']); ?>>
								<label class="form-check-label" for="form_wpcl_member_yes">Yes</label>
							</div>
							<div class="form-check">
								<input class="form-check-input form_request_contact" type="radio" name="wpcl_member" id="form_wpcl_member_no" value="n" required <?php if (isset($_SESSION['form']['wpcl_member']) && $_SESSION['form']['wpcl_member'] == 'n'){ echo 'checked'; } ?> <?php FormProcessor::disable_if_review($_SESSION['readypayment']); ?>>
								<label class="form-check-label" for="form_wpcl_member_no">No</label>
							</div>
						</div>
					</div>
					<div class="form_block_sponsor <?php if (!isset($_SESSION['form']['wpcl_member']) || (isset($_SESSION['form']['wpcl_member']) && $_SESSION['form']['wpcl_member'] == 'y')){ echo "d-none"; } ?>">
						<div class="row">
							<div class="col-12">
								<p class="py-4 text-secondary">Events must either be requested or sponsored by a member of the Woodruff Place Civic League (in good standing). This person MUST be present at all times during the event. Please enter the sponsor’s name so we may reach out to them.</p>
							</div>
						</div>
						<div class="row">
							<div class="col-12 col-md-6 mb-3">
								<label for="form_sponsor_first" class="form-label">Sponsor first name <span class="text-danger">*</span></label>
								<input type="text" class="form-control form_request_contact" name="wpcl_sponsor_name_first" id="form_sponsor_first" value="<?php if (isset($_SESSION['form']['wpcl_sponsor_name_first'])){ echo $_SESSION['form']['wpcl_sponsor_name_first']; } ?>"  <?php FormProcessor::disable_if_review($_SESSION['readypayment']); ?>>
							</div>
							<div class="col-12 col-md-6">
								<label for="form_sponsor_last" class="form-label">Sponsor last name <span class="text-danger">*</span></label>
								<input type="text" class="form-control form_request_contact" name="wpcl_sponsor_name_last" id="form_sponsor_last" value="<?php if (isset($_SESSION['form']['wpcl_sponsor_name_last'])){ echo $_SESSION['form']['wpcl_sponsor_name_last']; } ?>"  <?php FormProcessor::disable_if_review($_SESSION['readypayment']); ?>>
							</div>
						</div>
						<div class="row">
							<div class="col-12 col-md-6 mb-3">
								<label for="form_sponsor_email" class="form-label">Sponsor email <span class="text-danger">*</span></label>
								<input type="text" class="form-control form_request_contact" name="wpcl_sponsor_email" id="form_sponsor_email" value="<?php if (isset($_SESSION['form']['wpcl_sponsor_email'])){ echo $_SESSION['form']['wpcl_sponsor_email']; } ?>"  <?php FormProcessor::disable_if_review($_SESSION['readypayment']); ?>>
							</div>
							<div class="col-12 col-md-6">
								<label for="form_sponsor_phone" class="form-label">Sponsor phone <span class="text-danger">*</span></label>
								<input type="text" class="form-control form_request_contact" name="wpcl_sponsor_phone" id="form_sponsor_phone" value="<?php if (isset($_SESSION['form']['wpcl_sponsor_phone'])){ echo $_SESSION['form']['wpcl_sponsor_phone']; } ?>"  <?php FormProcessor::disable_if_review($_SESSION['readypayment']); ?>>
							</div>
						</div>
					</div>
					<div class="row mt-3 pt-4 pb-3">
						<div class="col-12">
							<h2 class="text-heading text-primary fs-3 border-bottom border-secondary-subtle pb-2">Event information</h2>
						</div>
					</div>
					<div class="row mb-2">
						<div class="col-12">
							<label for="form_email" class="form-label">Event title <span class="text-danger">*</span></label>
							<input type="text" class="form-control form_request_contact" name="event_title" id="event_title" value="<?php if (isset($_SESSION['form']['event_title'])){ echo $_SESSION['form']['event_title']; } ?>" required <?php FormProcessor::disable_if_review($_SESSION['readypayment']); ?>>
							<div class="form-text">Will appear publicly on the Woodruff Place event calendar</div>
							<div class="invalid-feedback">An event title is required.</div>
						</div>
					</div>
					<div class="row mt-3 pt-4">
						<div class="col-12">
							<p>Is this for a Woodruff Place neighborhood function? <span class="text-danger">*</span><span class="d-block text-secondary fs-7">(e.g. Civic League, Foundation, EID, committee)</span></p>
							<div class="form-check">
								<input class="form-check-input form_request_contact" type="radio" name="is_wp_event" id="is_wp_event_yes" value="y" required <?php if (isset($_SESSION['form']['is_wp_event']) && $_SESSION['form']['is_wp_event'] == 'y'){ echo 'checked'; } ?> <?php FormProcessor::disable_if_review($_SESSION['readypayment']); ?>>
								<label class="form-check-label" for="is_wp_event_yes">Yes</label>
							</div>
							<div class="form-check">
								<input class="form-check-input form_request_contact" type="radio" name="is_wp_event" id="is_wp_event_no" value="n" required <?php if (isset($_SESSION['form']['is_wp_event']) && $_SESSION['form']['is_wp_event'] == 'n'){ echo 'checked'; } ?> <?php FormProcessor::disable_if_review($_SESSION['readypayment']); ?>>
								<label class="form-check-label" for="is_wp_event_no">No</label>
							</div>
						</div>
					</div>
					<div class="row mt-5 mb-4">
						<div class="col-12">
							<h3 class="text-heading text-secondary fs-4 border-bottom border-secondary-subtle pb-2">Event sessions</h3>
						</div>
					</div>
					<!-- No sessions in list -->
					<?php
					// Form initial state or no sessions in list
					if (!isset($_SESSION['form']['sessions']) || (isset($_SESSION['form']['sessions']) && empty($_SESSION['form']['sessions']))):
					?>
					<div class="row businesses-list-empty sessions-list-empty">
						<div class="col-12">
							<p class="pt-3">Rental requests must include at least one date/time session. You may add multiple, if applicable.</p>
						</div>
					</div>
					<?php else: ?>
					<!-- Sessions list -->
					<div class="row my-4">
						<div class="col-12">
							<?php if (Event::events_are_overlapping($_SESSION['form']['sessions'])): ?>
							<div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
								<p class="my-0 fs-7 fw-bold">You have overlapping events. Is this intentional?
								<?php if ($GLOBALS['settings']->get('request.one_charge_daily') == "1"):?>
								<span class="d-block fst-italic fw-normal">Note: we charge a maximum of one rental fee per day (as determined by the largest event), regardless of whether you book multiple sessions.</span>
								<?php endif; ?>
								</p>
								<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
							</div>
							<?php endif; ?>
							<?php foreach ($_SESSION['form']['sessions'] as $key => $session): ?>
							<?php $price = new Price($session['session_attendance']); ?>
							<?php $booze = ($session['session_alcohol'] == "y") ? "Yes" : "No"; ?>
							<div class="business-row sessions-row border-bottom py-2">
								<div class="d-flex justify-content-between align-items-center">
									<div class="col-start">
										<h3 class="my-0 fs-6 fw-medium"><?php echo $session['session_name']; ?></h3>
										<div class="ps-2 ps-lg-3">
											<span class="text-secondary fs-7">Start: <span class="text-primary-emphasis"><?php echo date('M j, Y', strtotime($session['session_start_date'])); ?> <?php echo date('g:i a', strtotime($session['session_start_time'])); ?></span></span>
											<span class="d-block text-secondary fs-7">End: <span class="text-primary-emphasis"><?php echo date('M j, Y', strtotime($session['session_end_date'])); ?> <?php echo date('g:i a', strtotime($session['session_end_time'])); ?></span></span>
											<span class="d-block text-secondary fs-7">Event size: <span class="text-primary-emphasis"><?php echo $price->price_get('field_request_label'); ?> people</span></span>
											<span class="d-block text-secondary fs-7">Alcohol served: <span class="text-primary-emphasis"><?php echo $booze; ?></span></span>
										</div>
									</div>
									<div class="col-end">
										<?php if (!isset($_SESSION['readypayment']) || (isset($_SESSION['readypayment']) && $_SESSION['readypayment'] != TRUE)): ?>
										<div class="dropdown">
											<button type="button" class="btn btn-outline-secondary btn-sm px-3" data-name="form_block_session_remove_trigger" data-bs-toggle="dropdown" aria-expanded="false"><span class="bi bi-three-dots"></span></button>
											<ul class="dropdown-menu">
												<li>
													<button type="button" class="dropdown-item form_block_session_edit" data-bs-toggle="modal" data-bs-target="#modal_session_add_edit" data-bs-action="edit" data-bs-itemtoedit="<?php echo $key; ?>"><span class="bi bi-pencil me-2 text-primary"></span> Edit</button>
												</li>
												<li>
													<input type="hidden" name="session" value="<?php echo $key; ?>">
													<button type="button" name="session_remove" class="dropdown-item form_block_session_remove" data-bs-toggle="modal" data-bs-target="#modal_session_delete" data-session-to-remove="<?php echo $key; ?>"><span class="bi bi-trash me-2 text-danger"></span> Remove</button>
												</li>
											</ul>
										</div>
										<?php endif; ?>
									</div>
								</div>
							</div>
							<?php endforeach; ?>
						</div>
					</div>
					<?php endif; ?>
					<!-- Add sessions -->
					<div class="row mb-5 mb-lg-6 pt-3">
						<div class="col-12">
							<div class="d-flex align-items-center">
								<?php if (!isset($_SESSION['readypayment']) || (isset($_SESSION['readypayment']) && $_SESSION['readypayment'] != TRUE)): ?>
								<button type="button" class="btn btn-outline-primary px-4" id="form_block_session_add" data-bs-toggle="modal" data-bs-target="#modal_session_add_edit">Add session</button>
								<?php endif; ?>
							</div>
							<?php if (!isset($_SESSION['readypayment']) || (isset($_SESSION['readypayment']) && $_SESSION['readypayment'] != TRUE)): ?>
							<h3 class="text-heading text-secondary fs-4 mt-5 pt-3 mb-3">Need to start over?</h3>
							<button type="button" class="btn btn-outline-danger btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modal_form_reset">Reset form</button>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<div class="col-12 col-lg-4 bg-body-secondary bg-opacity-50">
					<div class="sticky-top ps-2">
						<h3 class="text-heading text-primary-emphasis fw-light h4 mt-1 pt-4 pb-2 mb-3 border-bottom border-secondary-subtle">Rental summary</h3>
						<?php
						// Form initial state or no sessions in list
						$total = "-";
						if (isset($_SESSION['form']['sessions']) && !empty($_SESSION['form']['sessions'])):
						$total = 0;
						// Determine the cleaning fee and security deposit (if any)
						// Note: request_get_fees returns the appropriate price IDs of the respective fees, not the amounts
						$fees = Request::request_get_fees($_SESSION['form']['sessions']);
						if (!empty($fees)):
						// Check for sessions that should be discounted because of > 1 sessions per day
						$sessions_waived = Event::event_return_sessions_discounted_new($_SESSION['form']['sessions']);
						// Per setting, check if we should max rental charges per day
						if ($GLOBALS['settings']->get('request.one_charge_daily') == "1" && (!empty($sessions_waived) && count($sessions_waived) >= 1)):
						?>
						<div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
							<p class="my-0 fs-7 fst-italic fw-normal">Note: we charge a maximum of one rental fee per day (as determined by the largest event), regardless of whether you book multiple sessions.</p>
						</div>
						<?php endif; ?>
						<?php
						foreach ($_SESSION['form']['sessions'] as $key => &$session):
						?>
						<!-- Row per session -->
						<?php
						// Check for a discounted session
						if (in_array($key, $sessions_waived))
						{
							$session['rental_waived'] = '1';
						}
						?>
						<?php $price = new Price($session['session_attendance']); ?>
						<div class="mb-2 pb-2 border-bottom border-secondary-subtle">
							<div class="d-flex justify-content-between align-items-center">
								<div class="left fw-medium fs-6">
									<span class="fw-medium"><?php echo $session['session_name']; ?></span>
								</div>
								<div class="right fw-light"></div>
							</div>
							<div class="d-flex justify-content-between align-items-center">
								<div class="left fw-medium fs-7">
									<span class="text-secondary fs-7">Rental fee</span> <span class="fw-light">(<?php echo $price->price_get('field_request_label'); ?> people)</span>
								</div>
								<div class="right fw-light"><div id="form_display_cost_total2">
								<?php
								$display_price = '$' . round($price->price_get('amount'));
								if (isset($session['rental_waived']) && $session['rental_waived'] == '1')
								{
									$display_price = '<s>' . $display_price . '</s>';
								}
								echo $display_price;
								?>
								</div></div>
							</div>
							<?php if ($session['session_alcohol'] == "y"): ?>
							<div class="d-flex justify-content-between align-items-center">
								<div class="left fw-medium fs-6">
									<div class="text-secondary fs-7">Alcohol served</div>
								</div>
								<div class="right fw-light"><div id="form_display_cost_total2"><?php echo "$" . round(Price::price_get_amount($config['products']['cleaning'])); ?></div></div>
							</div>
							<?php $total = $total + intval(Price::price_get_amount($config['products']['cleaning'])); ?>
							<?php endif; ?>
						</div>
						<?php
						// Set total based on whether this session fee is waived
						$total = (isset($session['rental_waived']) && $session['rental_waived'] == '1') ? $total : $total + intval(round(Price::price_get_amount($session['session_attendance'])));
						?>
						<?php endforeach; ?>
						<?php endif; ?>
						<!-- End row per session -->
						<!-- Cleaning fee, if applicable -->
						<?php
						$fee_cleaning = new Price($fees['cleaning']);
						if (intval($fee_cleaning->price_get('amount')) > 0):
						?>
						<div class="d-flex justify-content-between mb-2 pb-2 border-bottom border-secondary-subtle">
							<div class="left fs-6">
								<span class="fw-normal">Cleaning fee</span>
							</div>
							<div class="right fw-light"><div id="form_display_cost_total"><?php echo '$' . round($fee_cleaning->price_get('amount')); ?></div></div>
						</div>
						<?php
						// Add cleaning fee to the total
						$total = $total + intval($fee_cleaning->price_get('amount'));
						?>
						<?php endif; ?>
						<!-- End cleaning fee -->
						<!-- Security deposit, if applicable -->
						<?php
						$fee_security = new Price($fees['security']);
						if (intval($fee_security->price_get('amount')) > 0):
						?>
						<div class="d-flex justify-content-between mb-2 pb-2 border-bottom border-secondary-subtle">
							<div class="left fs-6">
								<span class="fw-normal">Security deposit</span>
							</div>
							<div class="right fw-light"><div id="form_display_cost_total"><?php echo '$' . round($fee_security->price_get('amount')); ?></div></div>
						</div>
						<?php
						// Add security deposit to the total
						$total = $total + intval($fee_security->price_get('amount'));
						?>
						<?php endif; ?>
						<!-- End security deposit -->
						<?php endif; ?>
						<div class="d-flex justify-content-between mb-4">
							<div class="left fs-6">
								<span class="fw-semibold">Total</span>
							</div>
							<div class="right fw-medium"><div id="form_display_cost_total">$ <?php echo $total; ?></div></div>
						</div>
						<!-- Sign up button -->
						<div class="row">
							<div class="col-12">
								<div class="d-flex">
									<?php
									$agreement = (isset($_SESSION['form']['agreement']) && $_SESSION['form']['agreement'] == "y") ? "y" : "n";
									$agreement_is_disabled = (isset($_SESSION['readypayment']) && $_SESSION['readypayment'] == TRUE) ? "disabled" : "";
									?>
									<input class="form-check-input wp-form-check me-3 form_request_contact" type="checkbox" name="agreement" id="agreement" required value="y" <?php echo Utility::form_check_if_checked($agreement, 'y', 'checked'); ?> <?php echo $agreement_is_disabled; ?>>
									<label for="agreement" class="fs-7">I have read and agree to the rental terms as outlined in the <a href="<?php echo $GLOBALS['settings']->get('link.rental_terms'); ?>" target="_blank">Town Hall Rental Agreement.</a></label>
								</div>
							</div>
						</div>
						<div class="row justify-content-center pt-4 mb-5 mb-lg-0">
							<div class="col-12">
								<div class="d-flex flex-column">
									<?php if (isset($_SESSION['readypayment']) && $_SESSION['readypayment'] == TRUE): ?>
									<button type="submit" name="action" value="route_request" id="route_request" class="btn btn-success text-white btn-lg px-4 flex-fill px-md-5 fs-6">Submit request</button>
									<button type="submit" name="action" value="route_goback" id="route_goback" class="btn btn-link text-primary mt-3 me-3"><span class="bi bi-arrow-left pe-2"></span> Go back and make changes</button>
									<?php else: ?>
									<button type="submit" name="action" value="route_review" id="route_review" class="btn btn-primary btn-lg px-4 flex-fill px-md-5 fs-6" disabled>Continue to review</button>
									<?php endif; ?>
								</div>
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
												$rental = new Product($config['products']['rental']);
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
											<div class="form-text">Alcohol fee: <?php echo "$" . Price::price_get_amount($config['products']['cleaning']); ?></div>
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
		<!-- Modal - Reset form -->
		<div class="modal fade" id="modal_form_reset" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="label_form_reset" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header bg-danger">
						<h1 class="modal-title fs-5 text-white" id="label_form_reset">Are you sure?</h1>
						<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<p>Are you sure you want to completely start over?</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
						<button type="submit" name="action" value="form_reset" class="btn btn-outline-danger px-5">Yes, start over</button>
					</div>
				</div>
			</div>
		</div>
	</form>
</main>
