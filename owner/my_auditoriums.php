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

// Delete Auditorium Handler
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM auditoriums WHERE auditorium_id='$delete_id' AND owner_id='$owner_id'");
    $message = "Auditorium has been successfully deleted.";
}

// Fetch all auditoriums owned by this owner
$sql = "SELECT * FROM auditoriums WHERE owner_id='$owner_id' ORDER BY auditorium_id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Auditoriums | Owner Portal</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header>
        <div class="nav-container">
            <div class="logo"><a href="../index.php">🎭 Auditorium Booking</a></div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="add_auditorium.php">+ Add Auditorium</a></li>
                    <li><a href="my_auditoriums.php" class="active">My Auditoriums</a></li>
                    <li><a href="view_bookings.php">Customer Bookings</a></li>
                    <li><a href="logout.php" style="color: #fca5a5;">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1 class="section-title" style="margin-bottom: 0;">🏢 My Listed Auditoriums</h1>
            <a href="add_auditorium.php" class="btn btn-primary btn-sm">+ Add New Auditorium</a>
        </div>

        <?php if ($message != ""): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Auditorium Name</th>
                        <th>Location</th>
                        <th>Capacity</th>
                        <th>Price/Day</th>
                        <th>Facilities</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['auditorium_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['auditorium_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['location']); ?></td>
                                <td><?php echo $row['capacity']; ?> Guests</td>
                                <td>₹<?php echo number_format($row['price'], 2); ?></td>
                                <td><small><?php echo htmlspecialchars(substr($row['facilities'], 0, 40)); ?>...</small></td>
                                <td><span class="badge badge-<?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
                                <td>
                                    <a href="my_auditoriums.php?delete_id=<?php echo $row['auditorium_id']; ?>" onclick="return confirm('Are you sure you want to delete this auditorium?');" class="btn btn-danger btn-sm">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 30px; color:#64748b;">
                                You haven't added any auditoriums yet. <a href="add_auditorium.php" style="color:#2563eb; font-weight:600;">Add your first auditorium now!</a>
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