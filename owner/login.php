<?php
session_start();
include '../db.php';

// If already logged in as owner
if (isset($_SESSION['owner_id'])) {
    header("Location: dashboard.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM owners WHERE email='$email' AND password='$password'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $owner = $result->fetch_assoc();
        $_SESSION['owner_id'] = $owner['owner_id'];
        $_SESSION['owner_name'] = $owner['name'];
        $_SESSION['owner_email'] = $owner['email'];

        header("Location: dashboard.php");
        exit();
    } else {
        $message = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Login | Auditorium Booking</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header>
        <div class="nav-container">
            <div class="logo"><a href="../index.php">🎭 Auditorium Booking</a></div>
            <nav>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="login.php" class="active">Owner Login</a></li>
                    <li><a href="register.php">Register as Owner</a></li>
                    <li><a href="../customer/login.php">Customer Portal</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="form-container">
            <h2 style="text-align:center; margin-bottom: 20px; color:#1e3a8a;">Auditorium Owner Login</h2>
            
            <?php if ($message != ""): ?>
                <div class="alert alert-danger"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Owner Email:</label>
                    <input type="email" name="email" required placeholder="Enter registered owner email">
                </div>

                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required placeholder="Enter password">
                </div>

                <button type="submit" class="btn btn-success btn-block">Log In as Owner</button>
            </form>

            <div style="text-align: center; margin-top: 20px; font-size: 14px;">
                <p>New Auditorium Owner? <a href="register.php" style="color: #16a34a; font-weight:600;">Register here</a></p>
                <p style="margin-top: 8px;"><a href="../customer/login.php" style="color: #64748b;">Looking to book a hall? Customer Login</a></p>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Auditorium Booking System</p>
    </footer>

</body>
</html>