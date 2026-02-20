<?php
@session_start();
require_once("../resources/_bootstrap.inc");
if (!Login::login_is_authenticated())
{
	header("Location: " . $env['docroot']);
}
