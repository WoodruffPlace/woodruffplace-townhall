<?php
require_once('_session.php');

// Get URL
$url = $_SERVER['REQUEST_URI'];
header('Content-Type: application/json; charset=utf-8');

if (str_starts_with($url, '/api/'))
{
	$method = str_replace('/api/', '', $url);
	$method = explode('/', $method);
}

// If URL is properly formatted, create an array object and execute the command
if (isset($method))
{
	$api = new API();
	$result = $api->execute($method[0], $method[1]);
	if (!$result == NULL)
	{
		echo $result;
	}
}
?>
