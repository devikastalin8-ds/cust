<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$msg_type = "";

// Handle Delete Customer with complete cascading cleanup
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // 1. Delete notifications for this customer
    $conn->query("DELETE FROM notifications WHERE user_id='$delete_id' AND user_type='customer'");

    // 2. Delete booking services for customer's bookings
    $conn->query("DELETE bs FROM booking_services bs 
                  JOIN bookings b ON bs.booking_id = b.booking_id 
                  WHERE b.user_id='$delete_id'");

    // 3. Delete payments for customer's bookings
    $conn->query("DELETE p FROM payments p 
                  JOIN bookings b ON p.booking_id = b.booking_id 
                  WHERE b.user_id='$delete_id'");

    // 4. Delete bookings for customer
    $conn->query("DELETE FROM bookings WHERE user_id='$delete_id'");

    // 5. Delete customer account
    if ($conn->query("DELETE FROM users WHERE user_id='$delete_id'") === TRUE) {
        $message = "Customer #$delete_id and all associated booking records have been deleted successfully.";
        $msg_type = "success";
    } else {
        $message = "Error deleting customer: " . $conn->error;
        $msg_type = "danger";
    }
}

$result = $conn->query("SELECT * FROM users ORDER BY user_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers | Admin</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header>
        <div class="nav-container">
            <div class="logo"><a href="dashboard.php">🛡️ Admin Dashboard</a></div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Overview</a></li>
                    <li><a href="manage_users.php" class="active">Customers</a></li>
                    <li><a href="manage_owners.php">Owners</a></li>
                    <li><a href="manage_auditoriums.php">Auditoriums</a></li>
                    <li><a href="manage_bookings.php">All Bookings</a></li>
                    <li><a href="logout.php" style="color: #fca5a5;">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        
        <h1 class="section-title">👥 Registered Customers</h1>

        <?php if ($message != ""): ?>
            <div class="alert alert-<?php echo $msg_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Registered Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['user_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td><?php echo date('d M, Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <a href="manage_users.php?delete_id=<?php echo $row['user_id']; ?>" onclick="return confirm('Are you sure you want to permanently delete customer <?php echo htmlspecialchars(addslashes($row['name'])); ?>? This will also remove their booking history.');" class="btn btn-danger btn-sm">Delete Customer</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 25px; color:#64748b;">No registered customers found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Auditorium Booking System</p>
    </footer>

    <script src="../assets/js/main.js"></script>
</body>
</html>