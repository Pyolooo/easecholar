<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer-master/src/Exception.php';
require_once 'PHPMailer-master/src/PHPMailer.php';
require_once 'PHPMailer-master/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
// Get sender's name and email from the form
$senderName = $_POST['sender-name'];
$senderEmail = $_POST['sender-email'];
$message = $_POST['message'];

// Create a new PHPMailer instance
$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'easecholar@gmail.com';
    $mail->Password = 'benz pupq lkxj amje';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
    $mail->Port = 587;

    // Set the recipient's name and email
    $mail->addAddress('easecholar@gmail.com', 'Admin');

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'New Message from Chatbox';

    // Concatenate sender name, sender email, and message into the email body
    $emailBody = "From: $senderName<br>Email: &lt;$senderEmail&gt;<br><br>Message:<br>$message";
    $mail->Body = $emailBody;

    // Send email
    $mail->send();
} catch (Exception $e) {
    echo 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
}
}
?>