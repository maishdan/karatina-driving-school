<?php
session_start();
include '../db.php';  // Fixed path to db.php

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION["student_id"];
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        $msg = "New passwords do not match.";
    } else {
        // Fetch current hashed password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($current_password, $user['password'])) {
            // Hash new password and update
            $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_hashed, $student_id);
            if ($update_stmt->execute()) {
                $msg = "Password changed successfully.";
            } else {
                $msg = "Failed to update password.";
            }
            $update_stmt->close();
        } else {
            $msg = "Current password incorrect.";
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
</head>
<body>
<h2>Change Password</h2>
<?php if ($msg) echo "<p>$msg</p>"; ?>
<form method="POST">
    <label>Current Password:</label><br />
    <input type="password" name="current_password" required /><br />
    <label>New Password:</label><br />
    <input type="password" name="new_password" required /><br />
    <label>Confirm New Password:</label><br />
    <input type="password" name="confirm_password" required /><br />
    <button type="submit">Change Password</button>
</form>
</body>
</html>
