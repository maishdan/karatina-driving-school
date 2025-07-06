<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $attendance = $_POST['attendance'];

    $stmt = $conn->prepare("UPDATE users SET attendance=? WHERE id=?");
    $stmt->bind_param("ii", $attendance, $id);
    $stmt->execute();

    header("Location: admin_panel.php");
}
