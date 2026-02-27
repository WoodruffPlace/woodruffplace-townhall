<?php require_once('_session.php'); ?>
<?php
// unset($_SESSION['form']);
// session_destroy();
?>
<?php if (!isset($page_header)){ Page::redirect_home(); } ?>
<div class="container-lg">
	<div class="row justify-content-center">
		<div class="col-12 col-md-8">
			<div class="row pt-3 pt-md-4">
				<div class="col-12">
					<div class="d-flex align-items-center">
						<span class="bi bi-check-circle fs-1 text-success pe-3"></span>
						<h1 class="text-heading fw-semibold text-success mb-0">Success</h1>
					</div>
				</div>
			</div>
			<div class="row py-4">
				<div class="col-12">
					<h2 class="text-heading fs-4">Thank you for submitting your request to use Town Hall.<br><br>Here's what to expect next:</h2>
				</div>
			</div>
			<div class="row">
				<div class="col-12">
					<ol class="pb-2">
						<li class="mb-4">You'll receive an email confirmation of this request. If anything looks off or you need something changed prior to our review, please <a href="mailto:<?php echo $GLOBALS['config']['contact_email']; ?>" class="link-primary">contact us</a>.</li>
						<li class="mb-4">This request could take up to a week to process. Once we confirm the details, membership or sponsors' membership, and availability, we'll notify you.</li>
						<li class="mb-4">If all checks out, you will receive a confirmation email along with a link to pay the fees. Your reservation will be locked in once your fee is paid.</li>
						<li class="mb-4">You'll receive additional information closer to your event, including a closing checklist. You'll be responsible for confirming the checklist activities are complete. We'll refund your security deposit upon inspection of the facility after your event.</li>
					</ol>
					<p>Once again, thank you for hosting your event here in Woodruff Place. If you have questions at any point, we're here to help. Reach out to us via email at: <a href="mailto:<?php echo $GLOBALS['config']['contact_email']; ?>" class="link-primary"><?php echo $GLOBALS['config']['contact_email']; ?></a></p>
					<div class="row mt-5 mb-5 justify-content-center justify-content-lg-start">
						<div class="col-11 col-sm-8 col-md-6 col-lg-4">
							<a href="https://WoodruffPlace.org" class="btn btn-outline-primary d-block fs-5 py-lg-3">Finish</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
