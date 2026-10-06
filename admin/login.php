<?php
session_start();
include '../db.php';

// If already logged in as admin
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: dashboard.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check admin credentials in admin table
    $sql = "SELECT * FROM admin WHERE email='$email' AND password='$password'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_email'] = $admin['email'];

        header("Location: dashboard.php");
        exit();
    } else {
        $message = "Invalid Admin credentials!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Auditorium Booking</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header>
        <div class="nav-container">
            <div class="logo"><a href="../index.php">🎭 Auditorium Booking</a></div>
            <nav>
                <ul>
                    <li><a href="../index.php">Main Website</a></li>
                    <li><a href="login.php" class="active">Admin Login</a></li>
                    <li><a href="../customer/login.php">Customer Portal</a></li>
                    <li><a href="../owner/login.php">Owner Portal</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="form-container">
            <div style="text-align:center; font-size: 40px; margin-bottom: 5px;">🛡️</div>
            <h2 style="text-align:center; margin-bottom: 6px; color:#1e3a8a;">Admin Portal Login</h2>
            <p style="text-align:center; color:#64748b; font-size: 13px; margin-bottom: 20px;">System Administrator Access</p>
            
            <?php if ($message != ""): ?>
                <div class="alert alert-danger"><?php echo $message; ?></div>
            <?php endif; ?>

            <!-- Default credentials hint for student viva demonstration -->
            <div style="background:#eff6ff; border:1px solid #bfdbfe; color:#1e40af; padding:10px 14px; border-radius:8px; font-size:13px; margin-bottom:18px;">
                💡 <strong>Demo Credentials:</strong><br>
                Email: <code>admin@gmail.com</code><br>
                Password: <code>admin123</code>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label>Admin Email:</label>
                    <input type="email" name="email" required placeholder="admin@gmail.com" value="admin@gmail.com">
                </div>

                <div class="form-group">
                    <label>Admin Password:</label>
                    <input type="password" name="password" required placeholder="Enter password" value="admin123">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Log In as Administrator</button>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Auditorium Booking System | Admin Portal</p>
    </footer>

</body>
</html>