<?php

/**
 *  Page params
 */
$login_page_visibility = "d-none";

/**
 *  Accept form
 */
if (isset($_POST['login_submit']))
{
	$sent = false;
	$email = trim(Utility::form_field_parse($_POST['email']));
	if (Utility::is_valid_email($email))
	{
		// Process login here
		$login = new Login($email, $mail);
		if ($login->login_begin() == TRUE)
		{
			$sent = TRUE;
		}
	}
	else
	{
		$error_text = "Sorry, an error occurred. Please ensure the email address format is valid.";
	}
}
?>
<main class="landing bg-body-tertiary">
	<div class="mast-hero">
		<div class="mast-hero-image"></div>
		<div class="mast-hero-content">
			<div class="logo"><img src="/img/logo_wp.svg" alt="Woodruff Place logo"></div>
			<div class="heading"><h1><span class="heading-em3">Welcome to the</span> <span class="heading-em1">Woodruff Place Town Hall</span> <span class="heading-em2">Reservation Portal</span></h1></div>
		</div>
		<div class="content-main">
			<div class="container-xl mt-3 mt-md-4 mt-lg-5 pt-lg-3">
				<div class="row justify-content-md-center">
					<div class="col-12 col-md-8 col-lg-8 offset-lg-1 offset-xxl-2">
						<?php if (!isset($email) || $email == null): ?>
						<div class="content-home">
							<p>Use this system to submit a request to rent or use Town Hall.</p>
							<div class="row">
								<div class="col-12">
									<div class="mt-1 mb-5 pb-lg-4"><a href="/new" class="btn btn-primary d-block fs-5 py-lg-3">Submit request</a></div>
								</div>
							</div>
							<p>Need to manage reservatiions?</p>
							<div class="row pb-5">
								<div class="col-12">
									<div class="mt-1">
										<?php if (Login::login_is_authenticated()): ?>
										<a href="/businesses" id="button-home-login-auth" class="btn btn-outline-primary d-block fs-5 py-lg-3">Login</a>
										<?php else: ?>
										<a href="#" id="button-home-login" class="btn btn-outline-primary d-block fs-5 py-lg-3">Login</a>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>
						<?php endif; ?>
						<?php
						if (isset($email) && $email != null && (isset($sent) && $sent == FALSE))
						{
							$login_page_visibility = "";
							$email_error = true;
							$error_text = (isset($error_text)) ? $error_text : "Sorry, an error occurred and we were unable to complete the login process. Please try again.";
						}
						?>
						<div class="content-login <?php echo $login_page_visibility; ?>">
							<?php if (isset($email_error) && $email_error == TRUE): ?>
								<div class="alert alert-danger fade show" role="alert">
									<p class="my-0"><?php echo $error_text; ?></p>
								</div>
							<?php endif; ?>
							<h2 class="font-alt home-h2 mb-4">Login</h2>
							<form method="post" class="needs-validation">
								<label for="email" class="form-label fs-5 mb-3 text-primary">Enter your email address:</label>
								<input type="email" class="form-control fs-4 border-primary text-primary" name="email" id="email" required value="<?php if (isset($email) && $email != null){ echo $email; } ?>"">
								<div class="row">
									<div class="col-12 col-md-8">
										<button type="submit" name="login_submit" class="btn btn-primary text-white d-block w-100 py-2 py-lg-3 mt-4">Login
											<span class="btn_form_submit_add_spinner spinner-border spinner-border-sm ms-2 d-none" aria-hidden="true"></span>
										</button>
										<button class="btn btn-link text-primary ps-0 mt-3 mt-4" id="button_login_cancel">Cancel and return home.</button>
									</div>
								</div>
								<p class="mt-4 pt-2">For login assistance, email us at: <a href="mailto:<?php echo $config['contact_email']; ?>" class="text-primary"><?php echo $config['contact_email']; ?></a></p>
							</form>
						</div>
						<?php if (isset($email) && $email != null & $sent == TRUE): ?>
						<div class="content-login-submit pb-4">
							<h2 class="font-alt home-h2 mb-5">Login</h2>
							<p><strong>Thank you!</strong><br>If that email address has access to this system, you will receive a login link via email.</p>
							<button class="btn btn-link text-primary ps-0 mt-3 mt-3" id="button-login-return">Return to login</button>
						</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>
