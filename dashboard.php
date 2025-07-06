<?php
session_start();

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$student_id = $_SESSION["student_id"];

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 900)) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

$stmt = $conn->prepare("SELECT fullname, email, phone, photo_path, fingerprint_data FROM users WHERE id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$profile_update_msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $new_phone = trim($_POST['phone']);
    $photo_path = $user['photo_path'];

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $filename = basename($_FILES["photo"]["name"]);
        $target_file = $target_dir . uniqid() . "-" . $filename;
        $filetype = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($filetype, $allowed_types)) {
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
                $photo_path = $target_file;
            } else {
                $profile_update_msg = "Failed to upload photo.";
            }
        } else {
            $profile_update_msg = "Invalid photo file type.";
        }
    }

    if ($new_email && !empty($new_phone) && empty($profile_update_msg)) {
        $update_stmt = $conn->prepare("UPDATE users SET email = ?, phone = ?, photo_path = ? WHERE id = ?");
        if ($update_stmt) {
            $update_stmt->bind_param("sssi", $new_email, $new_phone, $photo_path, $student_id);
            if ($update_stmt->execute()) {
                $profile_update_msg = "Profile updated successfully. Verification email sent.";
                $user['email'] = $new_email;
                $user['phone'] = $new_phone;
                $user['photo_path'] = $photo_path;
            } else {
                $profile_update_msg = "Failed to update profile.";
            }
            $update_stmt->close();
        } else {
            $profile_update_msg = "Database error: " . $conn->error;
        }
    } else {
        if (empty($profile_update_msg)) {
            $profile_update_msg = "Please enter a valid email and phone number.";
        }
    }
}

$courses = [];
$course_stmt = $conn->prepare("
    SELECT c.name, e.progress, e.attendance, e.exam_eligible, e.fee_paid 
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE e.user_id = ?
");
if ($course_stmt) {
    $course_stmt->bind_param("i", $student_id);
    $course_stmt->execute();
    $course_result = $course_stmt->get_result();
    while ($row = $course_result->fetch_assoc()) {
        $courses[] = $row;
    }
    $course_stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Student Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f3f3;
            color: #333;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #004aad;
            color: white;
            padding: 20px 0;
        }
        header .container {
            width: 90%;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header a {
            color: white;
            text-decoration: underline;
        }
        .container {
            width: 90%;
            margin: 20px auto;
        }
        nav ul {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 0;
            list-style: none;
           
            border-radius: 8px;
        }
        nav ul li {
            padding: 10px 15px;
            background: #0066cc;
            border-radius: 5px;
        }
        nav ul li a {
            color: white;
            font-weight: bold;
            text-decoration: none;
        }
        section {
            background: white;
            padding: 20px;
            border-radius: 10px;
        }
        h2, h3 {
            border-bottom: 2px solid #004aad;
            padding-bottom: 5px;
        }
        .profile img {
            border-radius: 10px;
            margin-bottom: 10px;
        }
        form label {
            display: block;
            margin-top: 10px;
        }
        form input[type="text"],
        form input[type="email"],
        form input[type="file"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }
        form button {
            margin-top: 15px;
            padding: 10px 20px;
            background: #004aad;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .message {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
        }
        ul li {
            margin-bottom: 10px;
        }
        a {
            color: #004aad;
        }
        .sidebar {
    position: fixed;
    top: 0;
    left: -260px;
    width: 250px;
    height: 100%;
    background-color: #004aad;
    overflow-x: hidden;
    transition: 0.3s;
    padding-top: 60px;
    z-index: 1000;
}
.sidebar ul {
    list-style-type: none;
    padding: 0;
}
.sidebar ul li {
    padding: 12px 20px;
}
.sidebar ul li a {
    color: white;
    text-decoration: none;
    display: block;
}
.sidebar ul li:hover {
    background-color: #00357f;
}
.sidebar.open {
    left: 0;
}

.hamburger {
    position: fixed;
    top: 20px;
    left: 20px;
    font-size: 30px;
    cursor: pointer;
    z-index: 1001;
    color: white;
    background-color: #004aad;
    padding: 5px 12px;
    border-radius: 4px;
}

    </style>
</head>
<body>
<header>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($user['fullname']); ?>!</h1>
        <a href="logout.php">Logout</a>
    </div>
</header>
<div id="sidebar" class="sidebar">
    <ul>
        <li><a href="#profile">Dashboard</a></li>
        <li><a href="#profile">Profile</a></li>
        <li><a href="#courses">Courses</a></li>
        <li><a href="#attendance">Attendance</a></li>
        <li><a href="#payments">Payments</a></li>
        <li><a href="#security">Security</a></li>
        <li><a href="#forms">Forms</a></li>
        <li><a href="#exams">Exams</a></li>
        <li><a href="#help">Help</a></li>
        <li><a href="student_portal_addons/change_password.php">Change Password</a></li>
        <li><a href="student_portal_addons/mpesa_payment.php">Make Payment (M-Pesa)</a></li>
        <li><a href="student_portal_addons/notification.php">Notifications</a></li>
    </ul>
</div>

<div class="hamburger" onclick="toggleSidebar()">&#9776;</div>


<section class="dashboard">
    <div class="container">
        <h2>Dashboard</h2>

       
        <div id="profile" class="profile">
            <img src="<?php echo htmlspecialchars($user['photo_path']); ?>" alt="Profile Photo" width="150" />
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
        </div>

        <h3>Update Your Profile</h3>
        <?php if ($profile_update_msg): ?>
            <p class="<?php echo strpos($profile_update_msg, 'success') !== false ? 'message' : 'error'; ?>">
                <?php echo htmlspecialchars($profile_update_msg); ?>
            </p>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($user['email']); ?>" />
            <label for="phone">Phone:</label>
            <input type="text" name="phone" id="phone" required value="<?php echo htmlspecialchars($user['phone']); ?>" />
            <label for="photo">Profile Photo (optional):</label>
            <input type="file" name="photo" id="photo" accept="image/*" />
            <button type="submit" name="update_profile">Update Profile</button>
        </form>

        <div id="courses">
            <h3>Enrolled Courses</h3>
            <?php if (count($courses) > 0): ?>
                <ul>
                    <?php foreach ($courses as $course): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($course['name']); ?></strong><br />
                            Progress: <?php echo htmlspecialchars($course['progress']); ?> |
                            Attendance: <?php echo htmlspecialchars($course['attendance']); ?><br />
                            Exam Eligible: <?php echo $course['exam_eligible'] ? 'Yes' : 'No'; ?><br />
                            Fees Paid: <?php echo $course['fee_paid'] ? 'Yes' : 'No'; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No enrolled courses yet.</p>
            <?php endif; ?>
        </div>

        <div id="attendance">
            <h3>Attendance Details</h3>
            <?php
            if (count($courses) > 0) {
                echo "<ul>";
                foreach ($courses as $course) {
                    echo "<li>" . htmlspecialchars($course['name']) . ": " . htmlspecialchars($course['attendance']) . "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>No attendance records found.</p>";
            }
            ?>
        </div>

        <div id="payments">
            <h3>Payments</h3>
            <p>Make your course payments here or view your payment history.</p>
            <p><a href="student_portal_addons/mpesa_payment.php">Click here to make a payment via M-Pesa</a></p>
        </div>

        <div id="security">
            <h3>Security Settings</h3>
            <p>Manage your account security here.</p>
            <p><a href="student_portal_addons/change_password.php">Change your password</a></p>
        </div>

        <div id="forms">
            <h3>Forms</h3>
            <ul>
                <li><a href="#">Application Form</a></li>
                <li><a href="#">Course Registration Form</a></li>
                <li><a href="#">Examination Request Form</a></li>
            </ul>
        </div>

        <div id="exams">
            <h3>Examination</h3>
            <?php if (count($courses) > 0): ?>
                <ul>
                    <?php foreach ($courses as $course): ?>
                        <li><strong><?php echo htmlspecialchars($course['name']); ?></strong> - Exam Eligible: <?php echo $course['exam_eligible'] ? 'Yes' : 'No'; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No examination information available.</p>
            <?php endif; ?>
        </div>

        <div id="help">
            <h3>Help & Support</h3>
            <ul>
                <li><a href="mailto:support@example.com">Email Support</a></li>
                <li><a href="#">FAQs</a></li>
                <li><a href="#">Live Chat</a></li>
            </ul>
        </div>

    </div>
</section>
</body>
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById("sidebar");
        sidebar.classList.toggle("open");
    }
</script>

</html>
