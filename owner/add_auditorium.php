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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name        = trim($_POST['auditorium_name']);
    $location    = trim($_POST['location']);
    $capacity    = intval($_POST['capacity']);
    $price       = floatval($_POST['price']);
    $facilities  = trim($_POST['facilities']);
    $description = trim($_POST['description']);
    $status      = "Available";
    $image       = "auditorium1.jpg"; // Default fallback

    // Image Upload Handling
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $file_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_dir = "../uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = $file_name;
        }
    }

    $sql = "INSERT INTO auditoriums (owner_id, auditorium_name, location, capacity, price, facilities, description, image, status) 
            VALUES ('$owner_id', '$name', '$location', '$capacity', '$price', '$facilities', '$description', '$image', '$status')";

    if ($conn->query($sql) === TRUE) {
        $message = "Auditorium added successfully!";
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
    <title>Add Auditorium | Owner Portal</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header>
        <div class="nav-container">
            <div class="logo"><a href="../index.php">🎭 Auditorium Booking</a></div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="add_auditorium.php" class="active">+ Add Auditorium</a></li>
                    <li><a href="my_auditoriums.php">My Auditoriums</a></li>
                    <li><a href="view_bookings.php">Customer Bookings</a></li>
                    <li><a href="logout.php" style="color: #fca5a5;">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        
        <div class="form-container" style="max-width: 650px;">
            <h2 style="text-align:center; color:#1e3a8a; margin-bottom: 20px;">➕ Add New Auditorium</h2>

            <?php if ($message != ""): ?>
                <div class="alert alert-<?php echo $msg_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label>Auditorium Name:</label>
                    <input type="text" name="auditorium_name" required placeholder="e.g. Royal Palace Auditorium">
                </div>

                <div class="form-group">
                    <label>Location / City Address:</label>
                    <input type="text" name="location" required placeholder="e.g. MG Road, Bangalore">
                </div>

                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Seating Capacity (Persons):</label>
                        <input type="number" name="capacity" required placeholder="e.g. 500" min="10">
                    </div>

                    <div class="form-group" style="flex: 1;">
                        <label>Price Per Day (₹):</label>
                        <input type="number" name="price" required placeholder="e.g. 25000" min="100" step="0.01">
                    </div>
                </div>

                <div class="form-group">
                    <label>Available Facilities:</label>
                    <input type="text" name="facilities" required placeholder="e.g. AC, Sound System, LED Stage, Dining Hall, Parking" value="Central AC, Sound System, Stage, Parking">
                </div>

                <div class="form-group">
                    <label>Auditorium Description:</label>
                    <textarea name="description" rows="4" required placeholder="Describe the hall, seating layout, audio-visual capabilities, and suitability for weddings, corporate meets, etc."></textarea>
                </div>

                <div class="form-group">
                    <label>Auditorium Photo (Optional):</label>
                    <input type="file" name="image" accept="image/*">
                </div>

                <button type="submit" class="btn btn-success btn-block" style="padding: 12px; font-size: 15px;">
                    Publish Auditorium
                </button>
            </form>
        </div>

    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Auditorium Booking System</p>
    </footer>

</body>
</html>