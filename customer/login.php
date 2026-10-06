<?php
session_start();
include '../db.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];

        // If redirect URL exists (like back to book)
        if (isset($_GET['redirect'])) {
            header("Location: " . $_GET['redirect']);
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $message = "Invalid email or password. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login | Auditorium Booking</title>
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
                    <li><a href="login.php" class="active">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="form-container">
            <h2 style="text-align:center; margin-bottom: 20px; color:#1e3a8a;">Customer Login</h2>
            
            <?php if ($message != ""): ?>
                <div class="alert alert-danger"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Email Address:</label>
                    <input type="email" name="email" required placeholder="Enter registered email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required placeholder="Enter your password">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Log In</button>
            </form>

            <div style="text-align: center; margin-top: 20px; font-size: 14px;">
                <p>Don't have an account? <a href="register.php" style="color: #2563eb; font-weight:600;">Register here</a></p>
                <p style="margin-top: 8px;"><a href="../owner/login.php" style="color: #64748b;">Are you an Auditorium Owner? Click here</a></p>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Auditorium Booking System</p>
    </footer>

</body>
</html>