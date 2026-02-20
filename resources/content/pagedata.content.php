<?php

/**
 * SECTIONS
 */

/**
 *  IF YOU ARE LOADING PER-PAGE CSS/JS files --
 *
 *  They will be loaded IN THE ORDER they are included.
 *  Order wisely.
 */

$pages = array(
	array
	(
		"url"					=> Array(""),
		"title"					=> "Town Hall Requests | Woodruff Place",
		"data_page"				=> "main",
		"meta_description"		=> "Welcome to the Town Hall Request Platform",
		"heading"				=> $GLOBALS["env"]["sitename"],
		"file"					=> "content_home.php",
		"omit_gtm"				=> FALSE,
		"site_map_include"  	=> TRUE,
		"omit_header" 			=> FALSE,
		"omit_footer" 			=> FALSE,
		"omit_content_header" 	=> TRUE,
	),
	array
	(
		"url"					=> Array("sandbox"),
		"title"					=> "Sandbox | " . $GLOBALS["env"]["sitename"],
		"data_page"				=> "main",
		"meta_description"		=> "Sandbox Page",
		"file"					=> "content_sandbox.php",
		"omit_gtm"				=> FALSE,
		"site_map_include" 	 	=> FALSE,
		"omit_header" 			=> TRUE,
		"omit_footer" 			=> TRUE,
	),
	array
	(
		"url"					=> Array("requests"),
		"title"					=> "Requests | " . $GLOBALS["env"]["sitename"],
		"data_page"				=> "main",
		"meta_description"		=> "Rental Requests",
		"file"					=> "content_requests.php",
		"omit_gtm"				=> FALSE,
		"site_map_include" 	 	=> TRUE
	),
	array
	(
		"url"					=> Array("request"),
		"title"					=> "Process Request | " . $GLOBALS["env"]["sitename"],
		"data_page"				=> "main",
		"meta_description"		=> "New Town Hall Request",
		"file"					=> "content_request.php",
	),
	array
	(
		"url"					=> Array("new"),
		"title"					=> "New Request | " . $GLOBALS["env"]["sitename"],
		"data_page"				=> "main",
		"meta_description"		=> "New Town Hall Request",
		"file"					=> "content_new.php",
		"omit_gtm"				=> FALSE,
		"site_map_include"		=> TRUE
	),
	array
	(
		"url"					=> Array("new/process"),
		"title"					=> "Process | " . $GLOBALS["env"]["sitename"],
		"data_page"				=> "main",
		"meta_description"		=> "Processing Woodruff Place Town Hall rental request",
		"file"					=> "content_new_process.php",
		"omit_gtm"				=> FALSE,
		"site_map_include"  	=> FALSE,
		"omit_header" 			=> TRUE,
		"omit_footer" 			=> TRUE
	),
	array
	(
		"url"					=> Array("new/success"),
		"title"					=> "Success | " . $GLOBALS["env"]["sitename"],
		"data_page"				=> "main",
		"meta_description"		=> "Welcome to Woodruff Place",
		"file"					=> "content_new_confirm.php",
		"omit_gtm"				=> FALSE,
		"site_map_include"  	=> TRUE
	),
	array
	(
		"url"					=> Array("pay"),
		"title"					=> "Pay invoice | " . $GLOBALS["env"]["sitename"],
		"data_page"				=> "main",
		"meta_description"		=> "Town Hall Rental Request",
		"file"					=> "content_pay.php",
		"omit_gtm"				=> FALSE,
		"site_map_include"  	=> TRUE,
		"omit_header" 			=> FALSE,
		"omit_footer" 			=> FALSE,
		"omit_content_header" 	=> TRUE,
	),
	array
	(
		"url"					=> Array("checklist"),
		"title"					=> "Closing Checklist | " . $GLOBALS["env"]["sitename"],
		"data_page"				=> "main",
		"meta_description"		=> "Town Hall Rental Request",
		"file"					=> "content_checklist.php",
		"omit_gtm"				=> FALSE,
		"site_map_include"  	=> TRUE,
		"omit_header" 			=> FALSE,
		"omit_footer" 			=> FALSE,
		"omit_content_header" 	=> TRUE,
	),
	array
	(
		"url"					=> Array("403", "login-error", "login/auth/invalid"),
		"title"					=> "Access Denied | " . $GLOBALS["env"]["sitename"],
		"data_page"				=> "error",
		"meta_description"		=> "Access to this page is restricted.",
		"file"					=> "content_403.php",
		"omit_gtm"				=> FALSE,
		"site_map_include"  	=> TRUE
	),
	array
	(
		"url"					=> Array("404"),
		"title"					=> "Page Not found | " . $GLOBALS["env"]["sitename"],
		"data_page"				=> "error",
		"meta_description"		=> "We're sorry, this page cannnot be found.",
		"file"					=> "content_404.php",
		"omit_gtm"				=> FALSE,
		"site_map_include"  	=> TRUE
	),
	array
	(
		"url"					=> Array("sandbox"),
		"title"					=> "Sandbox | " . $GLOBALS["env"]["sitename"],
		"data_page"				=> "error",
		"meta_description"		=> "We're sorry, this page cannnot be found.",
		"file"					=> "content_sandbox.php",
		"omit_gtm"				=> FALSE,
		"site_map_include"  	=> TRUE
	),
);
