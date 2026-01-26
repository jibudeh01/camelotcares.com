<?php
// CURRENT FILE TEST - Jan 25 2026
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
require_once '../vendor/autoload.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Set content type to JSON
header('Content-Type: application/json');

// SES SMTP Configuration for camelotcares.com
$ses_host = 'email-smtp.us-east-1.amazonaws.com';
$ses_port = 587;
$ses_username = 'AKIA6OBHS6YK74HFSYHN';  // Replace with your SES SMTP username
$ses_password = 'BCRpJclodsCqz9d5yhuUieByOcChILBtoEV243YsEztT';  // Replace with your SES SMTP password
$from_email = 'contact@camelotcares.com';  // Your verified SES email
$to_email = 'contact@camelotcares.com';    // Where you want to receive emails

try {
    // Validate required fields
    if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['subject']) || empty($_POST['message'])) {
        throw new Exception('Please fill in all required fields.');
    }
    
    // Sanitize input
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $phone = htmlspecialchars(isset($_POST["phone"]) ? trim($_POST["phone"]) : "");
    $subject = htmlspecialchars(trim($_POST['subject']));
    $message = htmlspecialchars(trim($_POST['message']));
    
    if (!$email) {
        throw new Exception('Please enter a valid email address.');
    }
    
    // Create PHPMailer instance
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = $ses_host;
    $mail->SMTPAuth = true;
    $mail->Username = $ses_username;
    $mail->Password = $ses_password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $ses_port;
    
    // Recipients
    $mail->setFrom($from_email, 'Camelot Cares Contact Form');
    $mail->addAddress($to_email, 'Camelot Cares Team');
    $mail->addReplyTo($email, $name);
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Camelot Cares Contact: ' . $subject;
    
    $html_body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; }
            .header { 
                background: linear-gradient(135deg, #2c5aa0 0%, #1e3c72 100%); 
                color: white; 
                padding: 20px; 
                text-align: center; 
                border-radius: 8px 8px 0 0;
            }
            .content { padding: 20px; background: #f9f9f9; }
            .field { 
                margin-bottom: 15px; 
                padding: 10px;
                background: white;
                border-left: 4px solid #2c5aa0;
            }
            .label { font-weight: bold; color: #2c5aa0; }
            .urgent { background: #fff3cd; border-left-color: #ffc107; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>🏥 New Contact Form Submission</h2>
                <p>Camelot Cares Healthcare Services</p>
            </div>
            <div class='content'>
                <div class='field'>
                    <span class='label'>👤 Patient/Contact Name:</span><br>
                    {$name}
                </div>
                <div class='field'>
                    <span class='label'>📧 Email Address:</span><br>
                    <a href='mailto:{$email}'>{$email}</a>
                </div>
                <div class='field'>
                    <span class='label'>📞 Phone Number:</span><br>
                    " . ($phone ?: 'Not provided') . "
                </div>
                <div class='field " . ($subject === 'Emergency/Urgent Care' ? 'urgent' : '') . "'>
                    <span class='label'>📋 Subject/Category:</span><br>
                    <strong>{$subject}</strong>
                </div>
                <div class='field'>
                    <span class='label'>💬 Message:</span><br>
                    " . nl2br($message) . "
                </div>
                <div class='field'>
                    <span class='label'>🕐 Submitted:</span><br>
                    " . date('l, F j, Y \a\t g:i A T') . "
                </div>
                <div class='field'>
                    <span class='label'>🌐 Source:</span><br>
                    camelotcares.com contact form
                </div>
            </div>
        </div>
    </body>
    </html>";
    
    $mail->Body = $html_body;
    $mail->AltBody = "New Contact Form Submission - Camelot Cares\n\n" .
                     "Name: {$name}\n" .
                     "Email: {$email}\n" .
                     "Phone: " . ($phone ?: 'Not provided') . "\n" .
                     "Subject: {$subject}\n" .
                     "Message: {$message}\n" .
                     "Submitted: " . date('Y-m-d H:i:s T') . "\n" .
                     "Source: camelotcares.com";
    
    $mail->send();
    
    // Log successful submission
    $log_entry = date('Y-m-d H:i:s') . " - Contact form submitted successfully from: {$email} ({$name}) - Subject: {$subject}\n";
error_log("some message", 3, "/var/www/html/contact/camelot-contact.log");
    
    echo json_encode([
        'success' => true, 
        'message' => 'Your message has been sent successfully to Camelot Cares. We will respond within 24 hours.'
    ]);
    
} catch (Exception $e) {
    // Log error
    $error_log = date('Y-m-d H:i:s') . " - Contact form error: " . $e->getMessage() . "\n";
    error_log($error_log, 3, '/var/www/html/contact/camelot-contact.log');
    
    echo json_encode([
        'success' => false, 
        'message' => 'We apologize, but there was an issue sending your message. Please try again or contact us directly.',
        'debug_error' => $e->getMessage()  
    ]);
}
?>
