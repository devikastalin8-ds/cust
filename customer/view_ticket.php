<?php
session_start();
include '../db.php';

if (!isset($_GET['booking_id'])) {
    header("Location: my_bookings.php");
    exit();
}

$booking_id = intval($_GET['booking_id']);

// Fetch Booking, Auditorium, Customer, Payment info
$sql = "SELECT b.*, a.auditorium_name, a.location, a.facilities, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone 
        FROM bookings b 
        JOIN auditoriums a ON b.auditorium_id = a.auditorium_id 
        JOIN users u ON b.user_id = u.user_id 
        WHERE b.booking_id = '$booking_id'";
$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
    die("Ticket not found.");
}

$ticket = $result->fetch_assoc();

// Fetch Selected Services
$services_res = $conn->query("SELECT * FROM booking_services WHERE booking_id = '$booking_id'");

// Fetch Payments for this booking
$pay_res = $conn->query("SELECT * FROM payments WHERE booking_id = '$booking_id' ORDER BY payment_id ASC");
$payments = [];
while ($p = $pay_res->fetch_assoc()) {
    $payments[] = $p;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation Pass #<?php echo $booking_id; ?> | Auditorium Booking</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header class="no-print">
        <div class="nav-container">
            <div class="logo"><a href="../index.php">🎭 Auditorium Booking</a></div>
            <nav>
                <ul>
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="my_bookings.php">My Bookings</a></li>
                    <li><a href="notifications.php">🔔 Notifications</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        
        <div class="no-print" style="margin-bottom: 20px; display:flex; justify-content:space-between; align-items:center;">
            <a href="my_bookings.php" class="btn btn-secondary btn-sm">&larr; Back to My Bookings</a>
            <button onclick="printTicketPass()" class="btn btn-primary">🖨️ Print Booking Pass & Invoice</button>
        </div>

        <div class="ticket-box">
            
            <div class="ticket-header">
                <h1 style="font-size: 24px; color: #1e3a8a; margin-bottom: 4px;">🎭 Auditorium Booking Confirmation Pass</h1>
                <p style="color: #64748b; font-size: 13px;">Official Event Reservation & Itemized Payment Receipt</p>
                <div style="margin-top: 10px;">
                    <span class="badge badge-<?php echo $ticket['status']; ?>" style="font-size: 14px; padding: 6px 14px;">Booking Status: <?php echo $ticket['status']; ?></span>
                </div>
            </div>

            <div class="ticket-row">
                <strong>Booking Reference ID:</strong>
                <span>#AUD-BK-<?php echo str_pad($ticket['booking_id'], 5, '0', STR_PAD_LEFT); ?></span>
            </div>

            <div class="ticket-row">
                <strong>Booking Timestamp:</strong>
                <span><?php echo date('d M, Y h:i A', strtotime($ticket['created_at'])); ?></span>
            </div>

            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 15px 0;">

            <div class="ticket-row">
                <strong>Auditorium Name:</strong>
                <span style="font-weight: 600; color: #0f172a;"><?php echo htmlspecialchars($ticket['auditorium_name']); ?></span>
            </div>

            <div class="ticket-row">
                <strong>Auditorium Location:</strong>
                <span><?php echo htmlspecialchars($ticket['location']); ?></span>
            </div>

            <div class="ticket-row">
                <strong>Event Name / Purpose:</strong>
                <span><?php echo htmlspecialchars($ticket['event_name']); ?></span>
            </div>

            <div class="ticket-row">
                <strong>Reserved Event Date:</strong>
                <span style="font-weight: 700; color: #2563eb;"><?php echo date('l, d F Y', strtotime($ticket['booking_date'])); ?></span>
            </div>

            <div class="ticket-row">
                <strong>Expected Guests:</strong>
                <span><?php echo $ticket['guest_count']; ?> Persons</span>
            </div>

            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 15px 0;">

            <div class="ticket-row">
                <strong>Customer Name:</strong>
                <span><?php echo htmlspecialchars($ticket['customer_name']); ?></span>
            </div>

            <div class="ticket-row">
                <strong>Customer Contact:</strong>
                <span><?php echo htmlspecialchars($ticket['customer_email']); ?> | <?php echo htmlspecialchars($ticket['customer_phone']); ?></span>
            </div>

            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 15px 0;">

            <!-- Itemized Services Breakdown -->
            <h4 style="color: #334155; margin-bottom: 10px; font-size: 15px;">🧾 Itemized Pricing & Services Breakdown</h4>
            
            <div class="ticket-row">
                <span>Auditorium Base Rent:</span>
                <span>₹<?php echo number_format($ticket['base_price'], 2); ?></span>
            </div>

            <?php if ($services_res && $services_res->num_rows > 0): ?>
                <?php while ($srv = $services_res->fetch_assoc()): ?>
                    <div class="ticket-row" style="color: #475569; padding-left: 10px;">
                        <span>+ <?php echo htmlspecialchars($srv['service_name']); ?>:</span>
                        <span>₹<?php echo number_format($srv['service_price'], 2); ?></span>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>

            <div class="ticket-row" style="font-size: 16px; font-weight: 700; color: #0f172a; margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0;">
                <span>Total Event Amount:</span>
                <span>₹<?php echo number_format($ticket['total_price'], 2); ?></span>
            </div>

            <div class="ticket-row" style="color: #16a34a; font-weight: 600;">
                <span>Amount Paid (Advance / Full):</span>
                <span>₹<?php echo number_format($ticket['advance_amount'] > 0 ? $ticket['advance_amount'] : $ticket['total_price'], 2); ?></span>
            </div>

            <?php if ($ticket['balance_amount'] > 0): ?>
                <div class="ticket-row" style="color: #dc2626; font-weight: 700;">
                    <span>Pending Balance Due:</span>
                    <span>₹<?php echo number_format($ticket['balance_amount'], 2); ?></span>
                </div>
            <?php endif; ?>

            <!-- Payment Transactions Details -->
            <?php if (!empty($payments)): ?>
                <div style="margin-top: 15px; padding: 10px; background: #f8fafc; border-radius: 6px; font-size: 12px;">
                    <strong>Recorded Transactions:</strong>
                    <?php foreach ($payments as $p): ?>
                        <div style="margin-top: 4px; color:#475569;">
                            &bull; <strong><?php echo htmlspecialchars($p['payment_method']); ?></strong> (Txn: <code><?php echo htmlspecialchars($p['transaction_id']); ?></code>) - ₹<?php echo number_format($p['amount'], 2); ?> [<?php echo $p['payment_type']; ?>] on <?php echo date('d M, Y', strtotime($p['payment_date'])); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div style="margin-top: 25px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px dashed #cbd5e1; padding-top: 12px;">
                Please present this pass upon arrival at the venue on the event date.
            </div>

        </div>

    </div>

    <footer class="no-print">
        <p>&copy; <?php echo date('Y'); ?> Online Auditorium Booking System</p>
    </footer>

<script src="../assets/js/main.js"></script>
</body>
</html>