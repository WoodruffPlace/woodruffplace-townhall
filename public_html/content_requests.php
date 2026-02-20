<?php require_once('_session.php'); ?>
<?php require_once('_session_auth.php'); ?>
<?php if (!isset($page_header) && !isset($_SESSION['userID'])){ Page::redirect_home(); } ?>
<?php
$user = new User($_SESSION['userID']);

if (User::user_is_admin($user->user_get_attribute('userID')) == TRUE)
{
	$roles['admin'] = TRUE;
	$requests = Request::request_get_requests();
}
?>
<main class="bg-body-tertiary">
	<div class="bg-white border-bottom">
		<div class="container-lg">
			<div class="row">
				<div class="col-12 py-1 py-lg-2">
					<div class="d-flex align-items-center justify-content-between">
						<p class="text-secondary fs-6 my-0">Welcome, <?php echo $user->user_get_attribute('name_first'); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="container-lg">
		<div class="row justify-content-center mb-lg-5">
			<div class="col-12">
				<div class="row pt-3 pt-md-2 mt-2 align-items-center">
					<div class="col-12 col-md ps-lg-0">
						<h1 class="text-heading text-primary fw-semibold mb-2 pb-2 mx-0">Requests</h1>
					</div>
				</div>
				<?php
				if ($requests && count($requests) > 0):
				?>
				<div class="row align-items-center pt-3 pb-3 bg-primary text-white d-none d-lg-flex">
					<div class="col-4">
						<p class="my-0 fw-bold">Request</p>
					</div>
					<div class="col-4">
						<p class="my-0 fw-bold">Contact</p>
					</div>
					<div class="col-4">
						<p class="my-0 fw-bold">Sessions</p>
					</div>
				</div>
				<?php
				foreach ($requests as $requestID):
				?>
				<?php
				$request = new Request($requestID);
				$customer = new Customer($request->request_get('customer'));
				// Visuals
				$status["class"] = "text-success";
				?>
				<div class="row align-items-lg-center py-3 py-lg-1 border-bottom bg-white shadow-sm">
					<div class="col-12 col-lg-4">
						<?php require(TEMPLATES . '/includes/_request_status_indicator.php'); ?>
						<h4 class="h5 fw-light fs-5 my-0 mt-2"><a href="/request?id=<?php echo $request->request_get('requestID'); ?>" class="link-primary"><?php echo $request->request_get('title'); ?></a></h4>
					</div>
					<div class="col-12 col-lg-4 mt-3">
						<p class="my-0 text-body-secondary"><?php echo $customer->customer_get("name_first") . " " . $customer->customer_get("name_last"); ?>
						<?php if (!empty($customer->customer_get('organization'))): ?>
						<span class="d-block fs-7"><?php echo $customer->customer_get('organization'); ?></span>
						<?php endif; ?>
						<span class="d-block fs-7"><a href="mailto:<?php echo $customer->customer_get('email'); ?>"><?php echo $customer->customer_get('email'); ?></a></span>
						</p>
					</div>
					<div class="col-12 col-lg-4">
						<div class="accordion accordion-flush mt-3" id="requests_list">
							<div class="accordion-item">
								<h2 class="accordion-header d-lg-none">
									<button class="accordion-button collapsed border border-secondary-subtle rounded-4" type="button" data-bs-toggle="collapse" data-bs-target="#requests_list_<?php echo $requestID; ?>" aria-expanded="false" aria-controls="requests_list_<?php echo $requestID; ?>">Details</button>
								</h2>
								<div id="requests_list_<?php echo $requestID; ?>" class="accordion-collapse collapse d-lg-block" data-bs-parent="#accordionFlushExample">
									<div class="accordion-body px-lg-0">
										<?php
										$sessions = $request->request_get_sessions();
										foreach ($sessions as $session):
										$event = new Event($session);
										?>
										<p class="my-0 pb-2">
											<span class="text-body-emphasis"><?php echo $event->event_get('title'); ?></span>
											<?php if ($event->event_shares_start_end_date()): ?>
												<span class="d-block text-secondary fs-7"><?php echo date('M j, Y', strtotime($event->event_get('event_start'))) . " " . date('g:i a', strtotime($event->event_get('event_start'))) . " &ndash; " . date('g:i a', strtotime($event->event_get('event_end'))); ?></span>
											<?php else: ?>
											<span class="text-secondary-emphasis fs-7 d-block">Start: <span class="text-secondary fs-7"><?php echo date('M j, Y g:i a', strtotime($event->event_get('event_start'))); ?></span></span>
											<span class="text-secondary-emphasis fs-7 d-block">End: <span class="text-secondary fs-7"><?php echo date('M j, Y g:i a', strtotime($event->event_get('event_end'))); ?></span></span>
											<?php endif; ?>
										</p>
										<?php endforeach; ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php endforeach; ?>
				<?php else: ?>
				<p class="mt-3">No requests found.</p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</main>
