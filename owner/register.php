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

    $check = $conn->query("SELECT * FROM owners WHERE email='$email'");
    if ($check->num_rows > 0) {
        $message = "Email is already registered! Please log in.";
        $msg_type = "danger";
    } else {
        $sql = "INSERT INTO owners (name, email, password, phone) 
                VALUES ('$name', '$email', '$password', '$phone')";
        if ($conn->query($sql) === TRUE) {
            $message = "Owner account registered successfully! You can now log in.";
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
    <title>Owner Registration | Auditorium Booking</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header>
        <div class="nav-container">
            <div class="logo"><a href="../index.php">🎭 Auditorium Booking</a></div>
            <nav>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="login.php">Owner Login</a></li>
                    <li><a href="register.php" class="active">Register as Owner</a></li>
                    <li><a href="../customer/login.php">Customer Portal</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="form-container">
            <h2 style="text-align:center; margin-bottom: 20px; color:#1e3a8a;">Auditorium Owner Registration</h2>
            
            <?php if ($message != ""): ?>
                <div class="alert alert-<?php echo $msg_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Full Name / Business Name:</label>
                    <input type="text" name="name" required placeholder="Enter owner or organization name">
                </div>

                <div class="form-group">
                    <label>Email Address:</label>
                    <input type="email" name="email" required placeholder="owner@domain.com">
                </div>

                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required placeholder="Create password">
                </div>

                <div class="form-group">
                    <label>Contact Phone Number:</label>
                    <input type="text" name="phone" required placeholder="10-digit mobile number">
                </div>

                <button type="submit" class="btn btn-success btn-block">Create Owner Account</button>
            </form>

            <p style="text-align: center; margin-top: 20px; font-size: 14px;">
                Already registered? <a href="login.php" style="color: #16a34a; font-weight:600;">Owner Login</a>
            </p>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Auditorium Booking System</p>
    </footer>

</body>
</html>