<?php
require("PHPMailerAutoload.php"); // path to the PHPMailerAutoload.php file.

$mail = new PHPMailer();
$mail->IsSMTP();
$mail->Mailer = "smtp";
$mail->Host = "smtp.gmail.com";
$mail->Port = "465"; // 8025, 587 and 25 can also be used. Use Port 465 for SSL.
$mail->SMTPAuth = true;
$mail->SMTPSecure = 'ssl';
$mail->Username = "info@sacet.com";
$mail->Password = "Mailsub@123";

$mail->From = "info@sacet.com";
$mail->FromName = "SACET";
$mail->AddAddress("paulkumar007@gmail.com", "Paulkumar Nadar");
//$mail->AddReplyTo("Your Reply-to Address", "Sender's Name");

$mail->Subject = "Hi!";
$mail->Body = "Hi! How are you?";
$mail->WordWrap = 50; 

if(!$mail->Send()) {
echo 'Message was not sent.';
echo 'Mailer error: ' . $mail->ErrorInfo;
exit;
} else {
echo 'Message has been sent.';
}
?>


