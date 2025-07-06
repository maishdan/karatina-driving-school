<?php
$servername = "localhost";
$username = "root";
$password = ""; // default empty password for XAMPP
$dbname = "karatina_driving_school";

// Create connection with charset UTF-8
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for proper encoding support
if (!$conn->set_charset("utf8mb4")) {
    die("Error loading character set utf8mb4: " . $conn->error);
}
?>
