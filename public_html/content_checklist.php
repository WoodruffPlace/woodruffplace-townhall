<?php require_once('_session.php'); ?>
<?php if (!isset($page_header) && !isset($_SESSION['userID'])){ Page::redirect_home(); } ?>
<?php
/**
 *  Get request and related info from the query string
 */
$id = (isset($_GET['id']) ? htmlspecialchars($_GET['id']) : null);
//$id = 5;

// Validate Request ID
if (Request::request_is_valid($id))
{
	// Create request
	$request = new Request($id);

	// Create customer
	$customer = new Customer($request->request_get('customer'));

	// Disallow the page view except for the few statuses around the ending of an event
	switch ($request->request_get('status'))
	{
		case 'in_progress':
		break;
		case 'ended_pending':
		case 'complete_deposit_refunded':
		case 'complete_deposit_retained':
			$_SESSION['alert'] = 'checklist-already-submitted';
		break;
		case 'ended_abandoned':
			$_SESSION['alert'] = 'event-ended-abandoned';
		break;
		default:
			//Boot the user to the public website
			header("Location: " . $GLOBALS['config']['site_public']);
			exit;
	}
}

// Process form
if (isset($_POST['action']) && $_POST['action'] == 'payment.submit')
{
	// Update the request with both the checklist complete and the notes
	$request->request_update('customer_checklist_complete', '1');
	$request->status_update('ended_inspect');
	$feedback = Utility::form_field_parse($_POST['feedback']);
	$request->request_update('customer_checklist_comments', $feedback);

	// Notification to Town Hall committee
	$message = Messages::committee_notification_checklist_submit($request);
	Utility::mailer_helper($mail, $config['notification_internal_requests'], "Checklist submitted, inspection required - " . $request->request_get('title'), $message, 'Woodruff Place Town Hall');

	$_SESSION['alert'] = 'checklist-complete';
	// Re-initialize request object
	$request = new Request($id);
}
?>
<?php
// Set alerts
if (isset($_SESSION['alert']))
{
	switch ($_SESSION['alert'])
	{
		case 'checklist-complete':
			$alert['status'] = TRUE;
			$alert['type'] = 'success';
			$alert['content'] = "Thank you for completing the final event checklist. Upon inspection and verification, your security deposit will be refunded.";
		break;
		case 'checklist-already-submitted':
			$alert['status'] = TRUE;
			$alert['type'] = 'danger';
			$alert['content'] = "It appears the closing checklist has already been submitted on behalf of this event. If you feel this is in error, please contact us.";
		break;
		case 'event-ended-abandoned':
			$alert['status'] = TRUE;
			$alert['type'] = 'danger';
			$alert['content'] = "Per the rental contract, the timeframe in which to complete the checklist for this event has closed. If you feel this is in error, please contact us.";
		break;
	}
}
?>
<main class="bg-body-tertiary">
	<div class="container-lg container_pay mx-auto rounded-5 bg-white my-5 p-3 pt-4 shadow-sm">
		<div class="row justify-content-center">
			<div class="col-12 text-center">
				<?php
				if (isset($_SESSION['alert']) && (isset($alert['status']) && $alert['status'] == TRUE)):
				?>
				<div class="alert alert-<?php echo $alert['type']; ?> fade show mt-4" role="alert">
					<?php echo $alert['content']; ?>
				</div>
				<?php endif; ?>
				<?php unset($_SESSION['alert']); ?>
				<div class="logo mx-auto"><img src="/img/logo_wp.svg" alt="Woodruff Place logo"></div>
				<h1 class="text-heading mt-3 fs-5">Woodruff Place Town Hall rental</h1>
			</div>
		</div>
		<div class="row mt-2 mb-4 pb-2 border-bottom">
			<div class="col-12 text-center">
				<h1 class="fw-bold ps-2">
					<?php echo $request->request_get('title'); ?>
				</h1>
			</div>
		</div>
		<?php if ($request->request_get('status') == 'in_progress'): ?>
		<div class="row mt-3 border-bottom">
			<div class="col-12 text-center d-flex flex-column align-items-center">
				<p class="text-dark fw-medium">Please affirm that the following closing requests are complete. <span class="d-block text-secondary fw-light fs-7">Note: return of your security deposit is contingent upon completing these tasks.</span></p>
				<div class="row" id="form-request-checklist">
					<div class="col-10 text-start mx-auto">
						<ul class="list-group list-group-flush">
							<li class="list-group-item px-0 bg-transparent">
								<div class="form-check form-check-inline d-flex align-items-center py-2">
									<input class="form-check-input wp-form-check me-3" type="checkbox" name="checklist_item_1" id="checklist_item_1" required value="y">
									<label class="form-check-label text-secondary" for="checklist_item_1">Remove all decorations, food, drink and trash from inside and out (including cigarette butts)</label>
								</div>
							</li>
							<li class="list-group-item px-0 bg-transparent">
								<div class="form-check form-check-inline d-flex align-items-center py-2">
									<input class="form-check-input wp-form-check me-3" type="checkbox" name="checklist_item_2" id="checklist_item_2" required value="y">
									<label class="form-check-label text-secondary" for="checklist_item_2">Spills and spots are cleaned off chairs, tables, floors, walls and/or windows</label>
								</div>
							</li>
							<li class="list-group-item px-0 bg-transparent">
								<div class="form-check form-check-inline d-flex align-items-center py-2">
									<input class="form-check-input wp-form-check me-3" type="checkbox" name="checklist_item_3" id="checklist_item_3" required value="y">
									<label class="form-check-label text-secondary" for="checklist_item_3">Clean the bathrooms</label>
								</div>
							</li>
							<li class="list-group-item px-0 bg-transparent">
								<div class="form-check form-check-inline d-flex align-items-center py-2">
									<input class="form-check-input wp-form-check me-3" type="checkbox" name="checklist_item_4" id="checklist_item_4" required value="y">
									<label class="form-check-label text-secondary" for="checklist_item_4">Clean the kitchen (if used)</label>
								</div>
							</li>
							<li class="list-group-item px-0 bg-transparent">
								<div class="form-check form-check-inline d-flex align-items-center py-2">
									<input class="form-check-input wp-form-check me-3" type="checkbox" name="checklist_item_5" id="checklist_item_5" required value="y">
									<label class="form-check-label text-secondary" for="checklist_item_5">Sweep floors</label>
								</div>
							</li>

							<li class="list-group-item px-0 bg-transparent">
								<div class="form-check form-check-inline d-flex align-items-center py-2">
									<input class="form-check-input wp-form-check me-3" type="checkbox" name="checklist_item_6" id="checklist_item_6" required value="y">
									<label class="form-check-label text-secondary" for="checklist_item_6">Empty trash and remove all trash from the premises</label>
								</div>
							</li>
							<li class="list-group-item px-0 bg-transparent">
								<div class="form-check form-check-inline d-flex align-items-center py-2">
									<input class="form-check-input wp-form-check me-3" type="checkbox" name="checklist_item_7" id="checklist_item_7" required value="y">
									<label class="form-check-label text-secondary" for="checklist_item_7">Return tables to the table cart</label>
								</div>
							</li>
							<li class="list-group-item px-0 bg-transparent">
								<div class="form-check form-check-inline d-flex align-items-center py-2">
									<input class="form-check-input wp-form-check me-3" type="checkbox" name="checklist_item_8" id="checklist_item_8" required value="y">
									<label class="form-check-label text-secondary" for="checklist_item_8">Return chairs to the storage rack</label>
								</div>
							</li>
							<li class="list-group-item px-0 bg-transparent">
								<div class="form-check form-check-inline d-flex align-items-center py-2">
									<input class="form-check-input wp-form-check me-3" type="checkbox" name="checklist_item_9" id="checklist_item_9" required value="y">
									<label class="form-check-label text-secondary" for="checklist_item_9">Lights are turned off, including the restrooms</label>
								</div>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<form method="post">
			<div class="row">
				<div class="col-12">
					<div class="row justify-content-center mt-4 px-2 pb-4">
						<div class="col-12 col-md-10">
							<p class="my-0 mb-3 pb-2 text-secondary fs-7">Please feel free to share any additional thoughts or feedback for us. What worked well? Anywhere where we could have improved?</p>
							<div class="form-floating mb-5">
								<textarea class="form-control" placeholder="Add notes..." name="feedback" id="feedback"></textarea>
								<label for="notes">Additional thoughts or feedback</label>
							</div>
							<button type="submit" name="action" value="payment.submit" id="checklist_submit" class="btn btn-primary btn-lg w-100 mb-1" disabled>Submit</button>
						</div>
					</div>
				</div>
			</div>
		</form>
		<?php endif; ?>
	</div>
</main>
