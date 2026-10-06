<?php
session_start();
include '../db.php';

$message = "";
$msg_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $phone    = trim($_POST['phone']);

    // Check if email already registered
    $check = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($check->num_rows > 0) {
        $message = "Email is already registered! Please log in.";
        $msg_type = "danger";
    } else {
        $sql = "INSERT INTO users (name, email, password, phone) 
                VALUES ('$name', '$email', '$password', '$phone')";
        if ($conn->query($sql) === TRUE) {
            $message = "Registration successful! You can now log in.";
            $msg_type = "success";
        } else {
            $message = "Error: " . $conn->error;
            $msg_type = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registration | Auditorium Booking</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header>
        <div class="nav-container">
            <div class="logo"><a href="../index.php">🎭 Auditorium Booking</a></div>
            <nav>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="search_auditorium.php">Browse Auditoriums</a></li>
                    <li><a href="login.php">Customer Login</a></li>
                    <li><a href="register.php" class="active">Register</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="form-container">
            <h2 style="text-align:center; margin-bottom: 20px; color:#1e3a8a;">Customer Registration</h2>
            
            <?php if ($message != ""): ?>
                <div class="alert alert-<?php echo $msg_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Full Name:</label>
                    <input type="text" name="name" required placeholder="Enter your full name">
                </div>

                <div class="form-group">
                    <label>Email Address:</label>
                    <input type="email" name="email" required placeholder="example@domain.com">
                </div>

                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required placeholder="Create a password">
                </div>

                <div class="form-group">
                    <label>Phone Number:</label>
                    <input type="text" name="phone" required placeholder="Enter 10-digit mobile number">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Register Account</button>
            </form>

            <p style="text-align: center; margin-top: 20px; font-size: 14px;">
                Already have an account? <a href="login.php" style="color: #2563eb; font-weight:600;">Login here</a>
            </p>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Auditorium Booking System</p>
    </footer>

</body>
</html>