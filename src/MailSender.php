<?php

namespace Fazoacademy\Learn;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailSender
{
    private $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
    }

    public function sendMail($to, $subject, $body)
    {
        try {
            // Server settings
            $this->mail->isSMTP();
            $this->mail->Host = $_ENV['SMTP_HOST'];  // Set the SMTP server from env variable
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $_ENV['SMTP_USERNAME'];  // SMTP username from env variable
            $this->mail->Password = $_ENV['SMTP_PASSWORD'];  // SMTP password from env variable
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $this->mail->Port = $_ENV['SMTP_PORT'];  // SMTP Port for SSL from env variable

            // Recipients
            $this->mail->setFrom($_ENV['SMTP_FROM_EMAIL'], $_ENV['SMTP_FROM_NAME']);  // From email and name from env variables
            $this->mail->addAddress($to);  // Add recipient email

            // Content
            $this->mail->isHTML(true);  
            $this->mail->Subject = $subject;  
            $this->mail->Body = $body;  // Body content of the email

            // Send the email
            $this->mail->send();
            return ['status' => 'success', 'message' => 'Message has been sent'];
        } catch (Exception $e) {
            // Handle any errors
            return ['status' => 'error', 'message' => "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}"];
        }
    }
}
