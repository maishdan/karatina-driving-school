<?php
// For email
function sendEmail($to, $subject, $message) {
    $headers = 'From: no-reply@example.com' . "\r\n" .
               'Reply-To: no-reply@example.com' . "\r\n" .
               'X-Mailer: PHP/' . phpversion();
    return mail($to, $subject, $message, $headers);
}

// For SMS — Example with Twilio (you need to install Twilio SDK and get credentials)
require_once 'vendor/autoload.php'; // Assuming composer install for Twilio

use Twilio\Rest\Client;

function sendSMS($to, $message) {
    $sid = 'YOUR_TWILIO_SID';
    $token = 'YOUR_TWILIO_AUTH_TOKEN';
    $client = new Client($sid, $token);

    try {
        $client->messages->create(
            $to,
            [
                'from' => 'YOUR_TWILIO_NUMBER',
                'body' => $message
            ]
        );
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Test sending
$email_sent = sendEmail('test@example.com', 'Test Email', 'This is a test email.');
$sms_sent = sendSMS('+2547XXXXXXXX', 'This is a test SMS.');

echo $email_sent ? "Email sent.<br>" : "Email failed.<br>";
echo $sms_sent ? "SMS sent.<br>" : "SMS failed.<br>";
?>
