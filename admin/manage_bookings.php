<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$message = "";

// Cancel Booking
if (isset($_GET['cancel_id'])) {
    $cancel_id = intval($_GET['cancel_id']);
    $conn->query("UPDATE bookings SET status='Cancelled' WHERE booking_id='$cancel_id'");
    $message = "Booking #$cancel_id marked as Cancelled.";
}

// Fetch all bookings across platform
$sql = "SELECT b.*, a.auditorium_name, a.location, u.name AS customer_name, u.phone AS customer_phone, o.name AS owner_name, p.payment_method, p.payment_status 
        FROM bookings b 
        JOIN auditoriums a ON b.auditorium_id = a.auditorium_id 
        JOIN users u ON b.user_id = u.user_id 
        LEFT JOIN owners o ON a.owner_id = o.owner_id 
        LEFT JOIN payments p ON b.booking_id = p.booking_id 
        ORDER BY b.booking_id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Bookings & Services | Admin</title>
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
                    <li><a href="manage_auditoriums.php">Auditoriums</a></li>
                    <li><a href="manage_bookings.php" class="active">All Bookings</a></li>
                    <li><a href="logout.php" style="color: #fca5a5;">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        
        <h1 class="section-title">📅 Global Booking & Services Records</h1>

        <?php if ($message != ""): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Auditorium</th>
                        <th>Customer</th>
                        <th>Owner</th>
                        <th>Event & Date</th>
                        <th>Selected Services</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php
                            $bk_id = $row['booking_id'];
                            $bs_res = $conn->query("SELECT * FROM booking_services WHERE booking_id = '$bk_id'");
                            ?>
                            <tr>
                                <td>#<?php echo $row['booking_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['auditorium_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['customer_name']); ?><br><small style="color:#64748b;"><?php echo htmlspecialchars($row['customer_phone']); ?></small></td>
                                <td><?php echo htmlspecialchars($row['owner_name'] ?? 'N/A'); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['event_name']); ?></strong><br>
                                    <small style="color:#2563eb; font-weight:600;">📅 <?php echo date('d M, Y', strtotime($row['booking_date'])); ?></small>
                                </td>
                                <td>
                                    <?php if ($bs_res && $bs_res->num_rows > 0): ?>
                                        <ul style="padding-left: 15px; margin: 0; font-size: 12px; color: #334155;">
                                            <?php while ($bs = $bs_res->fetch_assoc()): ?>
                                                <li><?php echo htmlspecialchars($bs['service_name']); ?> (₹<?php echo number_format($bs['service_price'], 2); ?>)</li>
                                            <?php endwhile; ?>
                                        </ul>
                                    <?php else: ?>
                                        <span style="color:#94a3b8; font-size:12px;">Only Hall</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong>₹<?php echo number_format($row['total_price'], 2); ?></strong><br>
                                    <small style="color:#64748b;">(Hall: ₹<?php echo number_format($row['base_price'], 2); ?>)</small>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $row['payment_status'] ?? 'Pending'; ?>">
                                        <?php echo htmlspecialchars($row['payment_status'] ?? 'Pending'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $row['status']; ?>"><?php echo $row['status']; ?></span>
                                </td>
                                <td>
                                    <?php if ($row['status'] != 'Cancelled'): ?>
                                        <a href="manage_bookings.php?cancel_id=<?php echo $row['booking_id']; ?>" onclick="return confirm('Cancel this booking?');" class="btn btn-danger btn-sm">Cancel</a>
                                    <?php else: ?>
                                        <span style="color:#94a3b8; font-size:12px;">No action</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 25px; color:#64748b;">No bookings found.</td>
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