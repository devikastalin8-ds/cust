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

// Handle Confirm Booking
if (isset($_GET['confirm_id'])) {
    $confirm_id = intval($_GET['confirm_id']);
    $conn->query("UPDATE bookings b JOIN auditoriums a ON b.auditorium_id = a.auditorium_id 
                  SET b.status='Confirmed' 
                  WHERE b.booking_id='$confirm_id' AND a.owner_id='$owner_id'");
    $message = "Booking #$confirm_id has been confirmed.";
}

// Handle Cancel/Reject Booking
if (isset($_GET['cancel_id'])) {
    $cancel_id = intval($_GET['cancel_id']);
    $conn->query("UPDATE bookings b JOIN auditoriums a ON b.auditorium_id = a.auditorium_id 
                  SET b.status='Cancelled' 
                  WHERE b.booking_id='$cancel_id' AND a.owner_id='$owner_id'");
    $message = "Booking #$cancel_id has been cancelled.";
}

// Fetch all bookings for auditoriums belonging to this owner
$sql = "SELECT b.*, a.auditorium_name, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone, p.payment_method, p.payment_status 
        FROM bookings b 
        JOIN auditoriums a ON b.auditorium_id = a.auditorium_id 
        JOIN users u ON b.user_id = u.user_id 
        LEFT JOIN payments p ON b.booking_id = p.booking_id 
        WHERE a.owner_id = '$owner_id' 
        ORDER BY b.booking_id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Bookings | Owner Portal</title>
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
                    <li><a href="manage_services.php">Manage Services</a></li>
                    <li><a href="view_bookings.php" class="active">Customer Bookings</a></li>
                    <li><a href="logout.php" style="color: #fca5a5;">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        
        <h1 class="section-title">📥 Customer Bookings & Services Requested</h1>

        <?php if ($message != ""): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Auditorium</th>
                        <th>Customer Details</th>
                        <th>Event & Date</th>
                        <th>Selected Services</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
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
                                <td>
                                    <strong><?php echo htmlspecialchars($row['customer_name']); ?></strong><br>
                                    <small style="color:#64748b;">📞 <?php echo htmlspecialchars($row['customer_phone']); ?></small><br>
                                    <small style="color:#64748b;">✉️ <?php echo htmlspecialchars($row['customer_email']); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['event_name']); ?></strong><br>
                                    <small style="color:#2563eb; font-weight:600;">📅 <?php echo date('d M, Y', strtotime($row['booking_date'])); ?></small><br>
                                    <small style="color:#64748b;">👥 <?php echo $row['guest_count']; ?> Guests</small>
                                </td>
                                <td>
                                    <?php if ($bs_res && $bs_res->num_rows > 0): ?>
                                        <ul style="padding-left: 15px; margin: 0; font-size: 12px; color: #334155;">
                                            <?php while ($bs = $bs_res->fetch_assoc()): ?>
                                                <li><?php echo htmlspecialchars($bs['service_name']); ?> (₹<?php echo number_format($bs['service_price'], 2); ?>)</li>
                                            <?php endwhile; ?>
                                        </ul>
                                    <?php else: ?>
                                        <span style="color:#94a3b8; font-size:12px;">Only Hall (No add-ons)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong>₹<?php echo number_format($row['total_price'], 2); ?></strong><br>
                                    <span class="badge badge-<?php echo $row['payment_status'] ?? 'Pending'; ?>" style="font-size:10px;">
                                        <?php echo htmlspecialchars($row['payment_status'] ?? 'Pending'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $row['status']; ?>"><?php echo $row['status']; ?></span>
                                </td>
                                <td>
                                    <?php if ($row['status'] == 'Pending'): ?>
                                        <a href="view_bookings.php?confirm_id=<?php echo $row['booking_id']; ?>" class="btn btn-success btn-sm" style="margin-bottom: 4px;">Confirm</a>
                                    <?php endif; ?>

                                    <?php if ($row['status'] != 'Cancelled'): ?>
                                        <a href="view_bookings.php?cancel_id=<?php echo $row['booking_id']; ?>" onclick="return confirm('Are you sure you want to cancel this booking?');" class="btn btn-danger btn-sm">Cancel</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 30px; color:#64748b;">
                                No customer bookings found for your auditoriums.
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