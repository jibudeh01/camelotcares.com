<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = htmlspecialchars($_POST['name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $phone = htmlspecialchars($_POST['phone'] ?? '');
    $appointment_type = htmlspecialchars($_POST['appointment_type'] ?? '');
    $date = htmlspecialchars($_POST['date'] ?? '');
    $time = htmlspecialchars($_POST['time'] ?? '');
    $message = htmlspecialchars($_POST['message'] ?? '');
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($appointment_type)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
        exit;
    }
    
    // Email content
    $to = "contact@camelothealthcare.com"; // Change this to your email
    $subject = "New Appointment Request - " . $appointment_type;
    
    $email_body = "
    New Appointment Request:
    
    Name: $name
    Email: $email
    Phone: $phone
    Service: $appointment_type
    Preferred Date: $date
    Preferred Time: $time
    Message: $message
    
    Please contact the client to confirm the appointment.
    ";
    
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    
    // Send email
    if (mail($to, $subject, $email_body, $headers)) {
        echo json_encode(['status' => 'success', 'message' => 'Appointment request sent successfully! We will contact you soon.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to send appointment request. Please try again.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
