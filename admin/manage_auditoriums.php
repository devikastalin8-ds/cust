<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$message = "";

// Handle Delete Auditorium
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM auditoriums WHERE auditorium_id='$delete_id'");
    $message = "Auditorium #$delete_id has been removed.";
}

$sql = "SELECT a.*, o.name AS owner_name, o.phone AS owner_phone 
        FROM auditoriums a 
        LEFT JOIN owners o ON a.owner_id = o.owner_id 
        ORDER BY a.auditorium_id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Auditoriums | Admin</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header>
        <div class="nav-container">
            <div class="logo"><a href="dashboard.php">🛡️ Admin Dashboard</a></div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Overview</a></li>
                    <li><a href="manage_users.php">Customers</a></li>
                    <li><a href="manage_owners.php">Owners</a></li>
                    <li><a href="manage_auditoriums.php" class="active">Auditoriums</a></li>
                    <li><a href="manage_bookings.php">All Bookings</a></li>
                    <li><a href="logout.php" style="color: #fca5a5;">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        
        <h1 class="section-title">🎭 All Listed Auditoriums</h1>

        <?php if ($message != ""): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Auditorium Name</th>
                        <th>Owner</th>
                        <th>Location</th>
                        <th>Capacity</th>
                        <th>Price/Day</th>
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
                                <td><?php echo htmlspecialchars($row['owner_name'] ?? 'N/A'); ?><br><small style="color:#64748b;"><?php echo htmlspecialchars($row['owner_phone'] ?? ''); ?></small></td>
                                <td><?php echo htmlspecialchars($row['location']); ?></td>
                                <td><?php echo $row['capacity']; ?> Seats</td>
                                <td>₹<?php echo number_format($row['price'], 2); ?></td>
                                <td><span class="badge badge-<?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
                                <td>
                                    <a href="manage_auditoriums.php?delete_id=<?php echo $row['auditorium_id']; ?>" onclick="return confirm('Are you sure you want to delete this auditorium?');" class="btn btn-danger btn-sm">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 25px; color:#64748b;">No auditoriums found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Auditorium Booking System | Admin Portal</p>
    </footer>

</body>
</html>