<?php http_response_code(403); ?>
<main class="main header-spacer">
	<div class="container-lg">
		<div class="row justify-content-center justify-content-lg-between">
			<div class="col-12 col-md-10 col-lg-8 pe-lg-5">
				<div class="row pt-3 pt-md-4">
					<div class="col-12">
						<h1 class="text-heading fw-semibold mb-2 pb-3">Access Denied<span class="d-block pt-2 fs-5 text-primary-emphasis">Error 403</span></h1>
						<p>The page or resource you have requested is access-restricted. If you attempted to login and reached this page, your login link may be invalid or expired.</p>
						<p>Please try starting again at the <a href="/" class="text-primary">home page</a> or <a href="mailto:<?php echo $GLOBALS['config']['contact_email']; ?>" class="text-primary">contact us</a> for help.</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>
