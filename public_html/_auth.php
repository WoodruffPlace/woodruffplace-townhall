<?php
require_once('_session.php');

// Get URL
$key = htmlspecialchars($_GET['key']);

// Process login
$user = Login::login_process($key);
if (isset($user) && $user != FALSE)
{
	Login::login_user_login($user->user_get_attribute('userID'));
	header("Location: " . $config['post_login_redirect']);
}
else
{
	header("Location: " . '/login-error');
}
