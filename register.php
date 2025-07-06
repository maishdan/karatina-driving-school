<?php 
include 'db.php'; // database connection

// Initialize variables
$name = $email = $phone = $password = "";
$successMessage = "";
$errorMessage = "";

// Form submission handling
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $name = htmlspecialchars(trim($_POST["name"]));
    $email = htmlspecialchars(trim($_POST["email"]));
    $phone = htmlspecialchars(trim($_POST["phone"]));
    $password = $_POST['password']; // Don't htmlspecialchars password for hashing

    // Basic validation
    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        $errorMessage = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Please enter a valid email address.";
    } else {
        // Hash the password securely
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Prepare insert query
        $sql = "INSERT INTO users (fullname, email, password, phone) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        // Check if prepare() succeeded
        if ($stmt === false) {
            $errorMessage = "Database error: " . htmlspecialchars($conn->error);
        } else {
            $stmt->bind_param("ssss", $name, $email, $hashedPassword, $phone);

            if ($stmt->execute()) {
                $successMessage = "Thank you for registering, $name! We will contact you soon.";
                $name = $email = $phone = $password = ""; // clear form
            } else {
                $errorMessage = "Error: " . htmlspecialchars($stmt->error);
            }

            $stmt->close();
        }

        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Register - Karatina Driving School</title>
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>
  <header>
    <div class="container">
      <h1>Karatina Driving School</h1>
      <nav>
        <ul>
          <li><a href="index.html">Home</a></li>
          <li><a href="courses.html">Courses</a></li>
          <li><a href="contact.html">Contact</a></li>
          <li><a href="register.php" class="active">Register</a></li>
          <li><a href="login.php">Log In</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <section class="about">
    <div class="container">
      <h3>Student Registration</h3>

      <?php if ($errorMessage): ?>
        <p style="color: red;"><?php echo $errorMessage; ?></p>
      <?php endif; ?>

      <?php if ($successMessage): ?>
        <p style="color: green;"><?php echo $successMessage; ?></p>
      <?php endif; ?>

      <!-- Registration Form -->
      <form method="post" action="register.php" style="max-width: 400px; margin: auto;">
        <label for="name">Full Name:</label><br />
        <input type="text" id="name" name="name" value="<?php echo $name; ?>" required /><br /><br />

        <label for="email">Email:</label><br />
        <input type="email" id="email" name="email" value="<?php echo $email; ?>" required /><br /><br />

        <label for="phone">Phone Number:</label><br />
        <input type="tel" id="phone" name="phone" value="<?php echo $phone; ?>" required /><br /><br />

        <label for="password">Password:</label><br />
        <input type="password" id="password" name="password" required /><br /><br />

        <button type="submit" class="btn">Register</button>
         <div class="login-link">
        Already have an account? <a href="login.php">Login here</a>
      </div>
    </div>
  </section>

  <footer>
    <div class="container">
      <p>&copy; 2025 Karatina Driving School. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>
