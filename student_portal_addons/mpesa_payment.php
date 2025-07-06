<?php
// Your actual Daraja Sandbox Consumer Key and Consumer Secret
$consumer_key = 'MyRnwikhX2SA8eLM2npN7eDGmWtVx4AANSLt7RSkRW3jH5e5';
$consumer_secret = 'lv3aTpjjdlfLAA5AACJwBbTDMpCpJA7zy1FOrDuKW9KBneQViz0F9Vi4rSpa4A2D';

// Function to get OAuth access token from Safaricom Daraja Sandbox API
function getAccessToken($consumer_key, $consumer_secret) {
    $credentials = base64_encode($consumer_key . ':' . $consumer_secret);
    $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        "Authorization: Basic $credentials",
        "Content-Type: application/json"
    ]);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);  // Enable SSL verification for security
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($curl);

    // Check for curl errors
    if ($response === false) {
        $error = curl_error($curl);
        curl_close($curl);
        die("Curl error while getting access token: $error");
    }

    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($http_code != 200) {
        die("Failed to get access token, HTTP status code: $http_code, Response: $response");
    }

    $json = json_decode($response, true);
    if (isset($json['access_token'])) {
        return $json['access_token'];
    } else {
        die("Access token not found in response: $response");
    }
}

// Usage example
$access_token = getAccessToken($consumer_key, $consumer_secret);

echo "Access token received: " . htmlspecialchars($access_token);
?>
