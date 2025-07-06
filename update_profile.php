<?php
session_start();
require 'db_connection.php';

$student_id = $_SESSION['student_id'];
$email = $_POST['email'];
$phone = $_POST['phone'];

// Handle photo upload
$photo = $_FILES['photo'];
$photo_name = '';
if ($photo['name']) {
    $ext = pathinfo($photo['name'], PATHINFO_EXTENSION);
    $photo_name = "student_" . $student_id . "." . $ext;
    move_uploaded_file($photo['tmp_name'], "uploads/$photo_name");
}

// Update database
$query = "UPDATE students SET email=?, phone=?";
$params = [$email, $phone];

if ($photo_name) {
    $query .= ", photo=?";
    $params[] = $photo_name;
}
$query .= " WHERE id=?";
$params[] = $student_id;

$stmt = $conn->prepare($query);
$stmt->execute($params);

header("Location: student_dashboard.php?update=success");
exit;
?>
