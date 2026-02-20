<?php

// Create a new global object
$mail = new PHPMailer\PHPMailer\PHPMailer(true);

// Set variables based on config

// Enable verbose debug output
$mail->SMTPDebug = 0;

// Set mailer to use SMTP
$mail->isSMTP();

// Specify SMTP servers
$mail->Host = $config['mail']['host'];

// Enable SMTP authentication
$mail->SMTPAuth = true;

// SMTP username
$mail->Username = $config['mail']['username'];

// SMTP password
$mail->Password = $config['mail']['password'];

// Enable SSL encryption, TLS also accepted with port 465
$mail->SMTPSecure = 'ssl';

 // TCP port to connect to
$mail->Port = 465;

//This is the email your form sends From
$mail->setFrom($mail->Username, $config['mail']['from_name']);

// Add a reply-to
$mail->addReplyTo($config['mail']['reply_to']);

// Set "HTML" setting to false (for now)
$mail->isHTML(false);

?>
