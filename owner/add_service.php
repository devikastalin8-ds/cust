<?php
session_start();
include '../db.php';

// Check if owner is logged in
if (!isset($_SESSION['owner_id'])) {
    header("Location: login.php");
    exit();
}

$owner_id = $_SESSION['owner_id'];
$message = "";
$msg_type = "";

// Fetch owner's auditoriums
$halls_res = $conn->query("SELECT * FROM auditoriums WHERE owner_id = '$owner_id'");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $auditorium_id    = intval($_POST['auditorium_id']);
    $service_category = trim($_POST['service_category']);
    $service_name     = trim($_POST['service_name']);
    $price            = floatval($_POST['price']);
    $description      = trim($_POST['description']);

    $sql = "INSERT INTO services (auditorium_id, service_name, service_category, price, description) 
            VALUES ('$auditorium_id', '$service_name', '$service_category', '$price', '$description')";

    if ($conn->query($sql) === TRUE) {
        $message = "Service package added successfully!";
        $msg_type = "success";
    } else {
        $message = "Error: " . $conn->error;
        $msg_type = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Service Package | Owner Portal</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header>
        <div class="nav-container">
            <div class="logo"><a href="../index.php">🎭 Auditorium Booking</a></div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="my_auditoriums.php">My Auditoriums</a></li>
                    <li><a href="manage_services.php" class="active">Manage Services</a></li>
                    <li><a href="view_bookings.php">Customer Bookings</a></li>
                    <li><a href="logout.php" style="color: #fca5a5;">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        
        <div class="form-container" style="max-width: 620px;">
            <h2 style="text-align:center; color:#1e3a8a; margin-bottom: 6px;">➕ Add Additional Service</h2>
            <p style="text-align:center; color:#64748b; font-size:14px; margin-bottom: 20px;">
                Offer Catering, Photography, Stage Decoration, or DJ/Sound to your auditorium
            </p>

            <?php if ($message != ""): ?>
                <div class="alert alert-<?php echo $msg_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST">
                
                <div class="form-group">
                    <label>Select Auditorium:</label>
                    <select name="auditorium_id" required>
                        <option value="">-- Choose Auditorium --</option>
                        <?php if ($halls_res && $halls_res->num_rows > 0): ?>
                            <?php while ($h = $halls_res->fetch_assoc()): ?>
                                <option value="<?php echo $h['auditorium_id']; ?>">
                                    <?php echo htmlspecialchars($h['auditorium_name']); ?> (<?php echo htmlspecialchars($h['location']); ?>)
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Service Category:</label>
                    <select name="service_category" required>
                        <option value="Catering">🍽️ Catering & Buffet</option>
                        <option value="Photography">📸 Photography & Videography</option>
                        <option value="Decoration">🌸 Stage & Floral Decoration</option>
                        <option value="DJ/Sound">🎵 DJ & Sound System</option>
                        <option value="Security">👮 Valet Parking & Security</option>
                        <option value="Other">✨ Other Special Service</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Service Package Name:</label>
                    <input type="text" name="service_name" required placeholder="e.g. Deluxe Royal Buffet Catering">
                </div>

                <div class="form-group">
                    <label>Package Price (₹):</label>
                    <input type="number" name="price" required min="100" step="0.01" placeholder="e.g. 15000">
                </div>

                <div class="form-group">
                    <label>Service Package Description:</label>
                    <textarea name="description" rows="3" required placeholder="Describe what is included (e.g. 5-course meal, live counters, 2 candid photographers, album, drone shoot, etc.)"></textarea>
                </div>

                <button type="submit" class="btn btn-success btn-block" style="padding: 12px; font-size: 15px;">
                    Publish Service Package
                </button>
            </form>
        </div>

    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Auditorium Booking System</p>
    </footer>

</body>
</html>