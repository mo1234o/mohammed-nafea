<?php
// Simple contact form that saves to file as backup
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $service = trim($_POST['service'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if ($name && $whatsapp) {
        // Save to file as backup
        $contactData = [
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
        $submissions[] = $contactData;
        file_put_contents($file, json_encode($submissions, JSON_PRETTY_PRINT));
        
        echo "✅ Contact form submitted successfully!\n";
        echo "📝 Saved to: contact_submissions.json\n";
        echo "📊 Total submissions: " . count($submissions) . "\n";
        echo "📤 Notification would be sent to: rouallmostafa1234@gmail.com\n";
        
        // Try to send simple email
        $to = 'rouallmostafa1234@gmail.com';
        $subject = "New Contact: $name";
        $body = "New contact form submission:\n\n";
        $body .= "Name: $name\n";
        $body .= "WhatsApp: $whatsapp\n";
        $body .= "Service: $service\n";
        $body .= "Message: $message\n";
        $body .= "Time: " . date('Y-m-d H:i:s') . "\n";
        
        $headers = "From: portfolio@mohammednafea.com\r\n";
        
        if (mail($to, $subject, $body, $headers)) {
            echo "📧 Email notification sent successfully!\n";
        } else {
            echo "⚠️ Email failed, but data was saved to file.\n";
        }
        
    } else {
        echo "❌ Please fill in name and WhatsApp number.\n";
    }
} else {
    echo "Please use POST method to submit contact form.\n";
}
?>
