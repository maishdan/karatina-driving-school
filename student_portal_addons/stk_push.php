<?php
// Daraja API Sandbox Credentials (updated as per your request)
$consumerKey = 'MyRnwikhX2SA8eLM2npN7eDGmWtVx4AANSLt7RSkRW3jH5e5';
$consumerSecret = 'lv3aTpjjdlfLAA5AACJwBbTDMpCpJA7zy1FOrDuKW9KBneQViz0F9Vi4rSpa4A2D';

// Sandbox shortcode and passkey
$BusinessShortCode = '174379';
$Passkey = 'bfb279f9aa9bdbcf15e97dd71a467cd2c2c49c292d9c24bfc466afcd18c5ab6b';

// Get phone and amount from POST form data
$phone = $_POST['phone'] ?? '';
$amount = $_POST['amount'] ?? '';

if (empty($phone) || empty($amount)) {
    exit("❌ Phone number and amount are required.");
}

// Format phone number to international format: 2547XXXXXXXX
$phone = preg_replace('/[^0-9]/', '', $phone);
if (strlen($phone) === 10 && substr($phone, 0, 1) === "0") {
    $phone = "254" . substr($phone, 1);
} elseif (strlen($phone) === 12 && substr($phone, 0, 3) === "254") {
    // already formatted correctly
} else {
    exit("❌ Invalid phone number format. Use 07XXXXXXXX or 2547XXXXXXXX.");
}

// Generate timestamp and password for STK Push
$Timestamp = date('YmdHis');
$Password = base64_encode($BusinessShortCode . $Passkey . $Timestamp);

// Function to get OAuth access token from Daraja API
function getAccessToken($key, $secret)
{
    $credentials = base64_encode("$key:$secret");
    $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo "❌ cURL error while getting access token: " . curl_error($ch);
        curl_close($ch);
        return null;
    }
    curl_close($ch);

    $result = json_decode($response, true);
    if (!$result) {
        echo "❌ Failed to decode access token response: $response";
        return null;
    }
    if (isset($result['errorCode'])) {
        echo "❌ API Error: " . $result['errorMessage'];
        return null;
    }

    return $result['access_token'] ?? null;
}

// Get access token
$access_token = getAccessToken($consumerKey, $consumerSecret);
if (!$access_token) {
    exit("❌ Failed to get access token from Safaricom API.");
}

// Set your live callback URL (replace with your actual ngrok or HTTPS URL)
$callbackUrl = 'https://d0cc-41-76-168-169.ngrok-free.app/mpesa_callback.php';

// Prepare STK Push payload
$stkPushPayload = [
    'BusinessShortCode' => $BusinessShortCode,
    'Password' => $Password,
    'Timestamp' => $Timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => (int)$amount,
    'PartyA' => $phone,
    'PartyB' => $BusinessShortCode,
    'PhoneNumber' => $phone,
    'CallBackURL' => $callbackUrl,
    'AccountReference' => 'KaratinaDrivingSchool',
    'TransactionDesc' => 'Course Payment'
];

// Initialize curl for STK Push request
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($stkPushPayload),
]);

$response = curl_exec($curl);
$curlError = curl_error($curl);
curl_close($curl);

// Output response or error
if ($curlError) {
    echo "❌ cURL Error: " . $curlError;
} else {
    $responseData = json_decode($response, true);
    echo "<h3>✅ STK Push Initiated</h3>";
    echo "<pre>" . json_encode($responseData, JSON_PRETTY_PRINT) . "</pre>";
}
?>
