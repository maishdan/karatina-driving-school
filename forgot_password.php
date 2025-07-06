<?php
// forgot_password.php
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
</head>
<body>
    <h2>Forgot Password</h2>
    <form action="send_reset_link.php" method="POST">
        <label>Enter your registered email:</label><br>
        <input type="email" name="email" required><br><br>
        <button type="submit">Send Reset Link</button>
    </form>
</body>
</html>
