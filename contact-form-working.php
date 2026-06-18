<?php
// Working contact form that saves submissions and tries simple email
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $service = trim($_POST['service'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validate
    if (!$name || !$whatsapp) {
        echo json_encode(['success' => false, 'message' => 'Please fill in name and WhatsApp number.']);
        exit;
    }
    
    // Save submission to file
    $submission = [
        'timestamp' => date('Y-m-d H:i:s'),
        'name' => $name,
        'whatsapp' => $whatsapp,
        'service' => $service,
        'message' => $message,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    $file = __DIR__ . '/contact_submissions.json';
    $submissions = [];
    if (file_exists($file)) {
        $submissions = json_decode(file_get_contents($file), true) ?: [];
    }
    $submissions[] = $submission;
    file_put_contents($file, json_encode($submissions, JSON_PRETTY_PRINT));
    
    // Try to send simple email notification
    $to = 'rouallmostafa1234@gmail.com';
    $subject = "New Contact: $name - Mohammed Nafea Portfolio";
    $emailBody = "New contact form submission:\n\n";
    $emailBody .= "Name: $name\n";
    $emailBody .= "WhatsApp: $whatsapp\n";
    $emailBody .= "Service: $service\n";
    $emailBody .= "Message: $message\n";
    $emailBody .= "Time: " . date('Y-m-d H:i:s') . "\n";
    $emailBody .= "IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
    
    $headers = "From: portfolio@mohammednafea.com\r\n";
    $headers .= "Reply-To: portfolio@mohammednafea.com\r\n";
    
    // Try PHP mail
    $emailSent = mail($to, $subject, $emailBody, $headers);
    
    // Return success response
    echo json_encode([
        'success' => true, 
        'message' => 'Your message has been sent successfully! We will contact you soon.',
        'email_sent' => $emailSent,
        'submission_saved' => true,
        'total_submissions' => count($submissions)
    ]);
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
