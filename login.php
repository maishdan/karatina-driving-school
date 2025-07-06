<?php
// File: login.php
session_start();
include 'db.php';

$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lowercase email for case-insensitive match
    $email = strtolower(trim($_POST['email']));
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $errorMessage = "Please fill in both email and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, fullname, password, photo_path FROM users WHERE LOWER(email) = LOWER(?)");
        if (!$stmt) {
            die("Database error: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['student_id'] = $user['id'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['photo'] = $user['photo_path'];
                header("Location: dashboard.php");
                exit();
            } else {
                $errorMessage = "Invalid password.";
            }
        } else {
            $errorMessage = "Invalid Username/Password.";
        }

        $stmt->close();
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Karatina Driving School</title>
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    
    <div class="container">
        <h2>Login</h2>
        <?php if ($errorMessage): ?>
            <p style="color:red;"><?= htmlspecialchars($errorMessage) ?></p>
        <?php endif; ?>
        <form method="POST">
            <label>Email:</label><br>
            <input type="email" name="email" required><br><br>

            <label>Password:</label><br>
            <input type="password" name="password" required><br><br>

            <button type="submit">Login</button>
        </form>
        <p><a href="forgot-password.php">Forgot Password?</a></p>
    </div>
</body>
</html>
