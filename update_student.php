<?php
// update_student.php - Upload student photo and fingerprint (future-ready)
session_start();
include 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Determine student ID from GET or POST
$studentId = isset($_POST['student_id']) ? intval($_POST['student_id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);

if (!$studentId) {
    echo "Invalid student ID.";
    exit();
}

// Fetch current student data
$stmt = $conn->prepare("SELECT fullname, photo, fingerprint FROM users WHERE id = ?");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    echo "Student not found.";
    exit();
}

$successMessage = $errorMessage = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $photoFilename = $student['photo'];
    $fingerprintHash = $student['fingerprint'];

    // Handle fingerprint hash
    if (!empty($_POST['fingerprint'])) {
        $fingerprintHash = password_hash(trim($_POST['fingerprint']), PASSWORD_DEFAULT);
    }

    // Handle photo upload
    if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "uploads/photos/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        $photoFilename = time() . "_" . basename($_FILES['photo']['name']);
        $targetFilePath = $targetDir . $photoFilename;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFilePath)) {
            $successMessage .= "Photo uploaded successfully. ";
        } else {
            $errorMessage .= "Error uploading photo. ";
        }
    }

    // Update student record
    $updateStmt = $conn->prepare("UPDATE users SET photo = ?, fingerprint = ? WHERE id = ?");
    $updateStmt->bind_param("ssi", $photoFilename, $fingerprintHash, $studentId);

    if ($updateStmt->execute()) {
        $successMessage .= "Student data updated.";
    } else {
        $errorMessage .= "Failed to update student.";
    }

    $updateStmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Student - Karatina Driving School</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <div class="container">
        <h1>Update Student - <?php echo htmlspecialchars($student['fullname']); ?></h1>
        <a href="admin_panel.php">Back to Admin Panel</a>
    </div>
</header>
<section class="container">
    <?php if ($successMessage): ?><p style="color:green;"><?php echo $successMessage; ?></p><?php endif; ?>
    <?php if ($errorMessage): ?><p style="color:red;"><?php echo $errorMessage; ?></p><?php endif; ?>

    <form action="update_student.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="student_id" value="<?php echo $studentId; ?>">

        <label>Upload Student Photo:</label><br>
        <input type="file" name="photo" accept="image/*"><br><br>

        <label>Fingerprint Hash (Placeholder):</label><br>
        <input type="text" name="fingerprint" placeholder="Enter fingerprint hash"><br><br>

        <button type="submit">Update Student</button>
        <button type="button" onclick="sendFingerprint()">Check Fingerprint Match</button>
    </form>

    <p style="margin-top:20px;">
        <strong>Future Biometric Integration:</strong><br>
        - This page will connect to a Python service via REST API to capture and match fingerprint data.<br>
        - Secure hashes will be used to compare templates.<br>
        - Image and fingerprint files stored in secure, encrypted format.
    </p>
</section>
<script>
async function sendFingerprint() {
    const fingerprintInput = document.querySelector('input[name="fingerprint"]');
    const fingerprint = fingerprintInput.value.trim();

    if (!fingerprint) {
        alert("Please enter fingerprint data.");
        return;
    }

    try {
        const response = await fetch('http://127.0.0.1:5000/match_fingerprint', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ fingerprint: fingerprint })
        });

        const data = await response.json();

        if (data.error) {
            alert("API Error: " + data.error);
        } else {
            alert("Fingerprint match: " + data.matched + "\nConfidence: " + data.confidence + "%");
        }
    } catch (error) {
        alert("Failed to contact fingerprint API. Make sure the Python server is running.");
    }
}
</script>
</body>
</html>
