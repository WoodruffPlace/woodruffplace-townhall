<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';


// function mail_send($from_name, $replyto, $recipients, $subject, $message)
// {
    // $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
    // try
    // {
    //     //Server settings
    //     $mail->SMTPDebug = 0;                           // Enable verbose debug output
    //     $mail->isSMTP();                                // Set mailer to use SMTP
    //     $mail->Host = 'smtp.gmail.com';                 // Specify main and backup SMTP servers
    //     $mail->SMTPAuth = true;                         // Enable SMTP authentication
    //     $mail->Username = 'noreply@massaveindy.org';    // SMTP username
    //     $mail->Password = '7YG<L75r*bL&7rD&';           // SMTP password
    //     $mail->SMTPSecure = 'tls';                      // Enable SSL encryption, TLS also accepted with port 465
    //     $mail->Port = 587;                              // TCP port to connect to
//
    //     $mail->setFrom($mail->Username, $from_name);            //This is the email your form sends From
//
    //     //Recipients
    //     foreach ($recipients as $recipient)
    //     {
    //         // Add a recipient address
    //         $mail->addAddress($recipient["email"], $recipient["name"]);
    //     }
//
    //     $mail->addReplyTo($replyto);
    //     //$mail->addCC('cc@example.com');
    //     //$mail->addBCC('bcc@example.com');
//
    //     //Attachments
    //     //$mail->addAttachment('/var/tmp/file.tar.gz');            // Add attachments
    //     //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');       // Optional name
//
    //     //Content
    //     $mail->isHTML(false);                                       // Set email format to HTML
    //     $mail->Subject = $subject;
    //     $mail->Body    = $message;
    //     //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
//
    //     $mail->send();
    //     //echo 'Message has been sent';
    //     return true;
    // }
    // catch (Exception $e)
    // {
    //     echo 'Message could not be sent.';
    //     echo 'Mailer Error: ' . $mail->ErrorInfo;
    //     return false;
    // }
// }
?>
