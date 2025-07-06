<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $token = bin2hex(random_bytes(50)); // Generate token
    $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

    // Check if email exists
    $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        // Save token and expiry
        $stmt = $conn->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE email=?");
        $stmt->bind_param("sss", $token, $expires, $email);
        $stmt->execute();

        // Email reset link
        $resetLink = "http://localhost/karatina-driving-school/reset_password.php?token=$token";
        $subject = "Password Reset - Karatina Driving School";
        $message = "Click the link to reset your password:\n$resetLink\n\nThis link expires in 1 hour.";
        $headers = "From: no-reply@karatina-driving-school.com";

        mail($email, $subject, $message, $headers);

        echo "Reset link has been sent to your email.";
    } else {
        echo "Email not found!";
    }
}
?>
