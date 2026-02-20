<?php require_once('_session.php'); ?>
<?php if (!isset($page_header) && !isset($_SESSION['userID'])){ Page::redirect_home(); } ?>
<?php
/**
 *  Get request and related info from the query string
 */
//$id = (isset($_GET['id']) ? htmlspecialchars($_GET['id']) : null);
$id = 5;

// Validate Request ID
if (Request::request_is_valid($id))
{
	// Create request
	$request = new Request($id);

	// Create customer
	$customer = new Customer($request->request_get('customer'));
}

// Process form
if (isset($_POST['action']) && $_POST['action'] == 'payment.submit')
{
	echo "go";
	// Initiate the Stripe checkout session
	// Create a Stripe checkout session, which will create the subscription
	$stripe_session = StripeInterface::stripe_checkout_session_create($request);

	//echo $stripe_session->url;
	echo $stripe_session;

	// Uncomment this once we're ready to kick the user over to Stripe
	//header("Location: " . $stripe_session->url);

}
?>
<main class="bg-body-tertiary">
	<div class="container-lg container_pay mx-auto rounded-5 bg-white my-5 p-3 pt-4 shadow-sm">
		<div class="row justify-content-center">
			<div class="col-12 text-center">
				<div class="logo mx-auto"><img src="/img/logo_wp.svg" alt="Woodruff Place logo"></div>
				<h1 class="text-heading mt-3 fs-4">Woodruff Place Town Hall rental</h1>
			</div>
		</div>

		<div class="row mt-2 mb-4 pb-2 border-bottom">
			<div class="col-12">
				<div class="d-flex justify-content-center">
					<div class="mx-auto d-inline-block">
						<span class="text-secondary fs-7 ps-2">Total due</span>
						<h1 class="fw-bold ps-2">
							<?php echo "$" . $request->request_get_total_cost(); ?>
						</h1>
					</div>
				</div>
			</div>
		</div>
		<div class="row mt-3">
			<div class="col-12">
				<div class="d-flex flex-column flex-md-row mb-4">
					<div class="pb-2 pb-md-0 flex-fill">
						<span class="text-secondary fs-7">Event title</span>
						<span class="d-block text-body-emphasis">Scotty's Big Bash</span>
					</div>
					<div class="pt-2 pt-md-0 flex-fill">
						<span class="text-secondary fs-7">Requestor name</span>
						<span class="d-block text-body-emphasis">Scotty Z.</span>
					</div>
				</div>
				<div class="accordion mt-3" id="rental_event_details">
					<div class="accordion-item">
						<h2 class="accordion-header">
							<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rental-details" aria-expanded="false" aria-controls="rental-details">
								<span class="text-heading fs-5">Rental details</span>
							</button>
						</h2>
						<div id="rental-details" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
							<!-- Sessions list -->
							<div class="row mx-1">
								<div class="col-12">
									<div class="row px-1 py-2 border-bottom border-secondary bg-white">
										<div class="col-4"><span class="text-body-emphasis fw-medium fs-7">Session</span></div>
										<div class="col-3"><span class="text-body-emphasis fw-medium fs-7">Size</span></div>
										<div class="col-2"><span class="text-body-emphasis fw-medium fs-7">Alcohol</span></div>
										<div class="col-3"><span class="text-body-emphasis fw-medium fs-7 ps-md-3">Session fee</span></div>
									</div>
									<?php
									$sessions = $request->request_get_sessions();
									foreach ($sessions as $session):
									$event = new Event($session);
									$fee_rental = (!empty($event->event_get('fee_rental'))) ? new Price($event->event_get('fee_rental')) : null;
									$fee_alcohol = (!empty($event->event_get('fee_alcohol'))) ? new Price($event->event_get('fee_alcohol')) : null;
									?>
									<div class="row px-1 py-2 bg-light-subtle border-bottom border-light-subtle align-items-center">
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
										</div>
										<?php $css_fee_rental = ($event->event_get('fee_waiver_rental') == "1") ? "text-decoration-line-through" : ""; ?>
										<div class="col-3"><span class="text-body fs-7"><?php echo $fee_rental->price_get('field_request_label'); ?></span><span class="d-block text-secondary fs-7 <?php echo $css_fee_rental; ?>"><?php echo "$" . round($fee_rental->price_get('amount'), 2); ?></span></div>
										<div class="col-2"><span class="text-body fs-7"><?php echo (!empty($event->event_get('fee_alcohol'))) ? "Yes" : "No"; ?></span>
										<?php if (!empty($event->event_get('fee_alcohol'))) : ?>
										<?php $css_fee_alcohol = ($event->event_get('fee_waiver_alcohol') == "1") ? "text-decoration-line-through" : ""; ?>
										<span class="text-secondary fs-7 d-block <?php echo $css_fee_alcohol; ?>"><?php echo "$" . round($fee_alcohol->price_get('amount'), 2); ?></span>
										<?php endif; ?>
										</div>
										<div class="col-3 ps-4"><span class="text-body fs-7 ps-3"><?php echo "$" . $event->event_get_total_cost(); ?></span></div>
									</div>
									<?php endforeach; ?>
								</div>
							</div>
							<!-- End Sessions list -->
							<div class="my-4 px-3">
								<h3 class="text-heading fs-5 text-primary">Fees</h3>
								<ul class="list-group list-group-flush">
									<li class="list-group-item d-flex justify-content-between border-bottom border-light-subtle px-0">
										<span class="">Cleaning fee</span>
										<span class="">
											<?php $fee_cleaning = new Price($request->request_get('cleaning_fee')); ?>
											<?php
											if ($request->fee_waived('cleaning'))
											{
												echo "<s>";
											}
											echo "$" . intval($fee_cleaning->price_get('amount'));
											if ($request->fee_waived('cleaning'))
											{
												echo "</s>";
												echo "<span class='ms-1'>$0</span>";
											}
											?>
										</span>
									</li>
									<li class="list-group-item d-flex justify-content-between border-bottom border-light-subtle px-0">
										<span class="">Security deposit</span>
										<span class="">
											<?php $fee_security = new Price($request->request_get('security_deposit')); ?>
											<?php
											if ($request->fee_waived('security'))
											{
												echo "<s>";
											}
											echo "$" . intval($fee_security->price_get('amount'));
											if ($request->fee_waived('security'))
											{
												echo "</s>";
												echo "<span class='ms-1'>$0</span>";
											}
											?>
										</span>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="row justify-content-center mt-4 px-2 pb-4">
					<div class="col-12 col-md-10">
						<p class="my-0 mb-4 pb-2 text-secondary fs-7">If anything needs updating or correcting, please <span class="text-body fw-semibold">contact us prior to payment</span>. Otherwise, please pay to confirm your rental. You will be able to download a copy of your invoice afterwards.</p>
						<form method="post">
							<button type="submit" name="action" value="payment.submit" class="btn btn-primary btn-lg w-100 mb-1">Pay</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>
