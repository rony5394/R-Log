<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';

function sendMail($to, $subject, $message){
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = explode(";",file_get_contents("../secrets/mail_access.txt"))[0];
        $mail->Password   = explode(";",file_get_contents("../secrets/mail_access.txt"))[1];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('myapp@test.local', '');
        $mail->addAddress($to, '');

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        // Send the email
        $mail->send();
        echo 'Email has been sent successfully!\n';
    } catch (Exception $e) {
        echo "Error: {$mail->ErrorInfo}";
    }
}

//sendMail("someone@example.com", "This is a test", "<h1>KEBAB</h1><br><button href='example.com'>CLICK</button>");