// Swap the home page for the login page
$('#button-home-login').on('click', function()
{
	$('.content-home').addClass("d-none");
	$('.content-login').removeClass("d-none");
});

// Cancel and return to home page from login
$('#button_login_cancel').on('click', function()
{
	$('.content-login').addClass("d-none");
	$('.content-home').removeClass("d-none");
});

// Return to login page from login-submitted
$('#button-login-return').on('click', function()
{
	$('.content-home').addClass("d-none");
	$('.content-login-submit').addClass("d-none");
	$('.content-login').removeClass("d-none");
});

// Listener/function for login button click
$('button[name="login_submit"]').on('click', function()
{
	var email = $('#email').val();
	if (email !== null && email.trim() !== '')
	{
		//$(this).prop('disabled', true);
		$(this ).find('.btn_form_submit_add_spinner').removeClass("d-none");
	}
});
