<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle Cancel Booking
if (isset($_GET['cancel_id'])) {
    $cancel_id = intval($_GET['cancel_id']);
    $conn->query("UPDATE bookings SET status='Cancelled' WHERE booking_id='$cancel_id' AND user_id='$user_id'");
    $message = "Booking #$cancel_id has been cancelled.";
}

// Fetch Customer Bookings
$sql = "SELECT b.*, a.auditorium_name, a.location 
        FROM bookings b 
        JOIN auditoriums a ON b.auditorium_id = a.auditorium_id 
        WHERE b.user_id = '$user_id' 
        ORDER BY b.booking_id DESC";
$result = $conn->query($sql);

// Fetch Payment History Records
$pay_sql = "SELECT p.*, b.event_name, a.auditorium_name 
            FROM payments p 
            JOIN bookings b ON p.booking_id = b.booking_id 
            JOIN auditoriums a ON b.auditorium_id = a.auditorium_id 
            WHERE b.user_id = '$user_id' 
            ORDER BY p.payment_id DESC";
$payments_res = $conn->query($pay_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings & Payment History | Customer</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header>
        <div class="nav-container">
            <div class="logo"><a href="../index.php">🎭 Auditorium Booking</a></div>
            <nav>
                <ul>
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="search_auditorium.php">Search Auditoriums</a></li>
                    <li><a href="my_bookings.php" class="active">My Bookings</a></li>
                    <li><a href="notifications.php">🔔 Notifications</a></li>
                    <li><a href="logout.php" style="color: #fca5a5;">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1 class="section-title" style="margin-bottom: 0;">📜 My Event Bookings</h1>
            <a href="search_auditorium.php" class="btn btn-primary btn-sm">+ Book Another Auditorium</a>
        </div>

        <?php if ($message != ""): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Active Bookings Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Auditorium</th>
                        <th>Event & Date</th>
                        <th>Services Included</th>
                        <th>Total Price</th>
                        <th>Payment Status</th>
                        <th>Booking Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php
                            $bk_id = $row['booking_id'];
                            $bs_res = $conn->query("SELECT service_name, service_price FROM booking_services WHERE booking_id = '$bk_id'");
                            ?>
                            <tr>
                                <td>#<?php echo $row['booking_id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['auditorium_name']); ?></strong><br>
                                    <small style="color:#64748b;">📍 <?php echo htmlspecialchars($row['location']); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['event_name']); ?></strong><br>
                                    <small style="color:#2563eb; font-weight:600;">📅 <?php echo date('d M, Y', strtotime($row['booking_date'])); ?></small>
                                </td>
                                <td>
                                    <?php if ($bs_res && $bs_res->num_rows > 0): ?>
                                        <ul style="padding-left:12px; margin:0; font-size:12px; color:#475569;">
                                            <?php while ($bs = $bs_res->fetch_assoc()): ?>
                                                <li><?php echo htmlspecialchars($bs['service_name']); ?></li>
                                            <?php endwhile; ?>
                                        </ul>
                                    <?php else: ?>
                                        <span style="font-size:12px; color:#94a3b8;">Hall Only</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong>₹<?php echo number_format($row['total_price'], 2); ?></strong>
                                </td>
                                <td>
                                    <?php if ($row['balance_amount'] > 0): ?>
                                        <span class="badge badge-warning">Advance (Paid: ₹<?php echo number_format($row['advance_amount'], 2); ?>)</span><br>
                                        <small style="color:#dc2626; font-weight:600;">Due: ₹<?php echo number_format($row['balance_amount'], 2); ?></small>
                                    <?php else: ?>
                                        <span class="badge badge-success">Fully Paid</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $row['status']; ?>"><?php echo $row['status']; ?></span>
                                </td>
                                <td>
                                    <a href="view_ticket.php?booking_id=<?php echo $row['booking_id']; ?>" class="btn btn-primary btn-sm" style="margin-bottom:4px;">Pass</a>
                                    
                                    <?php if ($row['balance_amount'] > 0 && $row['status'] != 'Cancelled'): ?>
                                        <a href="payment.php?booking_id=<?php echo $row['booking_id']; ?>&pay_balance=1" class="btn btn-success btn-sm" style="margin-bottom:4px;">Pay Balance</a>
                                    <?php endif; ?>

                                    <?php if ($row['status'] != 'Cancelled'): ?>
                                        <a href="my_bookings.php?cancel_id=<?php echo $row['booking_id']; ?>" onclick="return confirm('Are you sure you want to cancel this booking?');" class="btn btn-danger btn-sm">Cancel</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 30px; color: #64748b;">
                                No bookings found. <a href="search_auditorium.php" style="color: #2563eb; font-weight: 600;">Browse and book an auditorium today!</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Payment History & Transactions -->
        <h2 class="section-title" style="margin-top: 35px;">💳 Payment History & Transaction Records</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Txn ID</th>
                        <th>Event & Auditorium</th>
                        <th>Payment Type</th>
                        <th>Method</th>
                        <th>Amount Paid</th>
                        <th>Status</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($payments_res && $payments_res->num_rows > 0): ?>
                        <?php while ($p = $payments_res->fetch_assoc()): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($p['transaction_id']); ?></code></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($p['event_name']); ?></strong><br>
                                    <small style="color:#64748b;"><?php echo htmlspecialchars($p['auditorium_name']); ?></small>
                                </td>
                                <td>
                                    <span class="badge" style="background:#e0e7ff; color:#3730a3;">
                                        <?php echo htmlspecialchars($p['payment_type'] ?? 'Full'); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($p['payment_method']); ?></td>
                                <td style="font-weight:700; color:#16a34a;">₹<?php echo number_format($p['amount'], 2); ?></td>
                                <td><span class="badge badge-success"><?php echo htmlspecialchars($p['payment_status']); ?></span></td>
                                <td><?php echo date('d M, Y h:i A', strtotime($p['payment_date'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding:20px; color:#64748b;">No payment records found.</td>
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