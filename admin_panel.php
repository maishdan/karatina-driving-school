<?php
// admin_panel.php — Admin dashboard to manage students and track attendance
session_start();
include 'db.php';

// Basic security: Check if admin is logged in (implement real auth later)
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch all students
$sql = "SELECT * FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Karatina Driving School</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Admin Panel</h1>
            <a href="logout.php">Logout</a>
        </div>
    </header>

    <section class="container">
        <h2>Registered Students</h2>
        <table border="1" cellpadding="10" cellspacing="0">
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Photo</th>
                <th>Attendance</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td>
                        <?php if (!empty($row['photo'])): ?>
                            <img src="uploads/<?php echo $row['photo']; ?>" width="60" height="60" />
                        <?php else: ?>
                            No photo
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" action="update_attendance.php">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <input type="number" name="attendance" value="<?php echo $row['attendance']; ?>" min="0" required>
                            <button type="submit">Update</button>
                        </form>
                    </td>
                    <td>
                        <a href="update_student.php?id=<?php echo $row['id']; ?>">Edit</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </section>

    <section class="container">
        <h2>Update Student Photo & Fingerprint (Biometric Placeholder)</h2>
        <form action="update_student.php" method="POST" enctype="multipart/form-data">
            <label for="student_id">Student ID:</label>
            <input type="number" name="student_id" id="student_id" required><br><br>

            <label for="photo">Upload Photo:</label>
            <input type="file" name="photo" id="photo" accept="image/*" required><br><br>

            <label for="fingerprint">Enter Fingerprint Data (placeholder):</label>
            <input type="text" name="fingerprint" id="fingerprint" required><br><br>

            <button type="submit">Update Student</button>
        </form>

        <p><strong>Note:</strong> This interface is future-ready for fingerprint scanner integration via USB/Bluetooth biometric API.</p>
        <p>Python script to handle fingerprint matching will run in the backend (to be connected via Python-PHP bridge or API).</p>
    </section>
</body>
</html>
