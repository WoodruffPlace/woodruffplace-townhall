<?php
require_once('_session.php');
if (isset($sys_error) && $sys_error == TRUE)
{
	$page = new Page('error');
}
else
{
	$page = new Page(Page::getURL());
}
ob_start();
// Header
if ($page->page_omit_header() != TRUE)
{
	require_once(TEMPLATES . "/header.php");
}
// Main page content
require_once($page->page_get_content());

// Footer
if ($page->page_omit_footer() != TRUE)
{
	require_once(TEMPLATES . "/footer.php");
}
ob_end_flush();
?>
