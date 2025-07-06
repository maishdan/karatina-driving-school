<?php
// Connect to database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "karatina_driving_school";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// Get the JSON callback
$data = file_get_contents("php://input");
file_put_contents("mpesa_callback_log.json", $data, FILE_APPEND); // Optional logging

$response = json_decode($data, true);

if (isset($response['Body']['stkCallback'])) {
    $callback = $response['Body']['stkCallback'];

    if ($callback['ResultCode'] == 0) {
        $metadata = $callback['CallbackMetadata']['Item'];

        $amount = 0;
        $mpesaCode = '';
        $phone = '';

        foreach ($metadata as $item) {
            if ($item['Name'] == 'Amount') {
                $amount = $item['Value'];
            }
            if ($item['Name'] == 'MpesaReceiptNumber') {
                $mpesaCode = $item['Value'];
            }
            if ($item['Name'] == 'PhoneNumber') {
                $phone = $item['Value'];
            }
        }

        // Save to DB
        $stmt = $conn->prepare("INSERT INTO mpesa_payments (phone, amount, mpesa_code) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $phone, $amount, $mpesaCode);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();
