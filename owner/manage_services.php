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

// Handle Delete Service
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    // Verify this service belongs to an auditorium owned by this owner
    $conn->query("DELETE s FROM services s 
                  JOIN auditoriums a ON s.auditorium_id = a.auditorium_id 
                  WHERE s.service_id='$delete_id' AND a.owner_id='$owner_id'");
    $message = "Service package deleted successfully.";
}

// Fetch all services for auditoriums owned by this owner
$sql = "SELECT s.*, a.auditorium_name 
        FROM services s 
        JOIN auditoriums a ON s.auditorium_id = a.auditorium_id 
        WHERE a.owner_id = '$owner_id' 
        ORDER BY s.service_id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Additional Services | Owner Portal</title>
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
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1 class="section-title" style="margin-bottom: 0;">✨ Manage Additional Services</h1>
            <a href="add_service.php" class="btn btn-primary btn-sm">+ Add New Service Package</a>
        </div>

        <p style="color: #64748b; margin-bottom: 20px;">
            Offer extra add-on services (Catering, Photography, Stage Decoration, DJ/Sound) that customers can add to their auditorium booking.
        </p>

        <?php if ($message != ""): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Auditorium</th>
                        <th>Category</th>
                        <th>Service Package Name</th>
                        <th>Package Price</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['service_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['auditorium_name']); ?></strong></td>
                                <td>
                                    <span class="badge badge-primary" style="background:#e0e7ff; color:#3730a3;">
                                        <?php echo htmlspecialchars($row['service_category']); ?>
                                    </span>
                                </td>
                                <td><strong><?php echo htmlspecialchars($row['service_name']); ?></strong></td>
                                <td style="font-weight: 700; color: #16a34a;">₹<?php echo number_format($row['price'], 2); ?></td>
                                <td><small style="color:#64748b;"><?php echo htmlspecialchars($row['description']); ?></small></td>
                                <td>
                                    <a href="manage_services.php?delete_id=<?php echo $row['service_id']; ?>" onclick="return confirm('Are you sure you want to delete this service package?');" class="btn btn-danger btn-sm">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color:#64748b;">
                                No additional services found. <a href="add_service.php" style="color:#2563eb; font-weight:600;">Add your first service package (Catering, Photography, etc.)</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Auditorium Booking System</p>
    </footer>

</body>
</html>