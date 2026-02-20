<?php require_once('_session.php'); ?>
<?php

$inv = "in_1SzVGMFpJK7bNF3rXc5rrNYo";
$status = "paid";
$return = Invoice::invoice_update_status($status, $inv);

print_r($return);


// $rental = new Product(1);
// $rental_prices = $rental->product_get_prices();
// //print_r($rental_prices);
// foreach ($rental_prices as $price){
	// echo $price;
// }

//echo dirname(dirname(__FILE__));

// $client = new Google\Client();
// $client->setAuthConfig(dirname(dirname(__FILE__)) . '/resources/config/keys/mass-ave-business-memberships-0fc0d5b5712c.json'); // Replace with the actual path
// $client->addScope(Google_Service_Drive::DRIVE); // Example: Google Drive API scope
//
// // Option 1: Using Application Default Credentials (recommended if applicable)
// // $client->useApplicationDefaultCredentials();
//
// // Option 2: Explicitly fetching the access token
// $accessToken = $client->fetchAccessTokenWithAssertion();
// $client->setAccessToken($accessToken);
//
// // Now you can create a service and make API calls
// $service = new Google_Service_Drive($client);
// // ... use the service to interact with the Google Drive API ...
?>

<?php
//require_once __DIR__ . '/vendor/autoload.php';

/***********************************

// Service account credentials JSON file path
$serviceAccountFile = dirname(__DIR__) . '/resources/config/keys/mass-ave-business-memberships-0fc0d5b5712c.json';

// Admin email for domain-wide delegation
//$adminEmail = 'admin@massaveindy.org';
$adminEmail = 'admin-sdk@mass-ave-business-memberships.iam.gserviceaccount.com';

// Initialize Google Client
$client = new Google\Client();
$client->setAuthConfig($serviceAccountFile);
$client->setScopes(
[
	'https://www.googleapis.com/auth/admin.directory.group',
	'https://www.googleapis.com/auth/admin.directory.group.member'
]);
$client->setSubject($adminEmail);

$service = new Google_Service_Directory($client);

// Example: List all groups in the domain

$groups = $service->groups->listGroups(['domain' => 'massaveindy.org']);


***********************************/


/**
foreach ($groups->getGroups() as $group)
{
	echo "Group: " . $group->getEmail() . "\n";
}

**?


// Example: Add a member to a group
/*
$member = new Google_Service_Directory_Member([
	'email' => 'user@yourdomain.com',
	'role' => 'MEMBER'
]);
$service->members->insert('group@yourdomain.com', $member);
*/

// Example: Remove a member from a group
/*
$service->members->delete('group@yourdomain.com', 'user@yourdomain.com');
*/

?>

