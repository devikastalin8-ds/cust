<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['booking_id'])) {
    header("Location: my_bookings.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$booking_id = intval($_GET['booking_id']);

// Fetch Booking and Auditorium info
$sql = "SELECT b.*, a.auditorium_name, a.location 
        FROM bookings b 
        JOIN auditoriums a ON b.auditorium_id = a.auditorium_id 
        WHERE b.booking_id = '$booking_id' AND b.user_id = '$user_id'";
$res = $conn->query($sql);

if (!$res || $res->num_rows == 0) {
    die("Invalid booking request.");
}

$booking = $res->fetch_assoc();
$booked_services_res = $conn->query("SELECT * FROM booking_services WHERE booking_id = '$booking_id'");

$is_paying_balance = isset($_GET['pay_balance']) && $booking['balance_amount'] > 0;
$success = false;

// Handle Payment Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['payment_method'])) {
    $payment_method = $_POST['payment_method'];
    $payment_mode   = isset($_POST['payment_mode']) ? $_POST['payment_mode'] : 'Full'; // 'Full' or 'Advance'
    $transaction_id = "TXN" . rand(10000000, 99999999);

    if ($is_paying_balance) {
        // Paying off the remaining balance
        $amount_to_pay = $booking['balance_amount'];
        $advance_amount = $booking['advance_amount'] + $amount_to_pay;
        $balance_amount = 0.00;
        $payment_type = "Balance Payment";

        $conn->query("INSERT INTO payments (booking_id, amount, payment_method, payment_type, payment_status, transaction_id) 
                      VALUES ('$booking_id', '$amount_to_pay', '$payment_method', 'Balance', 'Paid', '$transaction_id')");

        $conn->query("UPDATE bookings SET advance_amount='$advance_amount', balance_amount='0.00', status='Confirmed' WHERE booking_id='$booking_id'");

        // Notification
        $notif_title = "Remaining Balance Paid Successfully";
        $notif_msg = "Your balance payment of ₹" . number_format($amount_to_pay, 2) . " for {$booking['auditorium_name']} has been received. Booking is now fully paid.";
        $conn->query("INSERT INTO notifications (user_id, user_type, booking_id, title, message, type) 
                      VALUES ('$user_id', 'customer', '$booking_id', '$notif_title', '$notif_msg', 'payment')");

    } else {
        // Initial Payment (Full or Advance)
        $total_price = floatval($booking['total_price']);
        if ($payment_mode == 'Advance') {
            $amount_to_pay  = round($total_price * 0.50, 2); // 50% Advance
            $advance_amount = $amount_to_pay;
            $balance_amount = $total_price - $amount_to_pay;
            $payment_type   = "Advance Payment";
        } else {
            $amount_to_pay  = $total_price;
            $advance_amount = $total_price;
            $balance_amount = 0.00;
            $payment_type   = "Full Payment";
        }

        $conn->query("INSERT INTO payments (booking_id, amount, payment_method, payment_type, payment_status, transaction_id) 
                      VALUES ('$booking_id', '$amount_to_pay', '$payment_method', '$payment_mode', 'Paid', '$transaction_id')");

        $conn->query("UPDATE bookings SET advance_amount='$advance_amount', balance_amount='$balance_amount', payment_type='$payment_mode', status='Confirmed' 
                      WHERE booking_id='$booking_id'");

        // Notifications
        $notif_title = "Payment Successful: Booking Confirmed";
        $notif_msg = "Payment of ₹" . number_format($amount_to_pay, 2) . " ($payment_type) confirmed for {$booking['auditorium_name']} on " . date('d M, Y', strtotime($booking['booking_date'])) . ". Txn ID: $transaction_id";
        $conn->query("INSERT INTO notifications (user_id, user_type, booking_id, title, message, type) 
                      VALUES ('$user_id', 'customer', '$booking_id', '$notif_title', '$notif_msg', 'payment')");

        // Event Reminder Notification
        $reminder_msg = "Reminder: Your event '{$booking['event_name']}' is scheduled at {$booking['auditorium_name']} on " . date('d M, Y', strtotime($booking['booking_date'])) . ".";
        $conn->query("INSERT INTO notifications (user_id, user_type, booking_id, title, message, type) 
                      VALUES ('$user_id', 'customer', '$booking_id', 'Event Scheduled 📅', '$reminder_msg', 'reminder')");
    }

    $success = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Gateway | Auditorium Booking</title>
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
                    <li><a href="my_bookings.php">My Bookings</a></li>
                    <li><a href="notifications.php">🔔 Notifications</a></li>
                    <li><a href="logout.php" style="color: #fca5a5;">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        
        <div class="form-container" style="max-width: 660px;">

            <?php if ($success): ?>
                <div style="text-align: center; padding: 20px 0;">
                    <div style="font-size: 50px;">🎉</div>
                    <h2 style="color: #16a34a; margin: 10px 0;">Payment Successful!</h2>
                    <p style="color: #64748b; margin-bottom: 20px;">
                        Your payment for <strong><?php echo htmlspecialchars($booking['auditorium_name']); ?></strong> has been processed and your reservation is <strong>Confirmed</strong>.
                    </p>
                    
                    <a href="view_ticket.php?booking_id=<?php echo $booking_id; ?>" class="btn btn-success" style="margin-right: 10px;">📄 View & Print Invoice Pass</a>
                    <a href="my_bookings.php" class="btn btn-secondary">Go to My Bookings</a>
                </div>
            <?php else: ?>

                <h2 style="text-align:center; color:#1e3a8a; margin-bottom: 6px;">💳 Payment Gateway Checkout</h2>
                <p style="text-align:center; color:#64748b; font-size:14px; margin-bottom: 20px;">
                    <?php echo $is_paying_balance ? "Clear Remaining Balance" : "Select Payment Option (Full / Advance)"; ?>
                </p>

                <!-- Itemized Invoice Summary Box -->
                <div style="background: #f8fafc; padding: 18px; border-radius: 10px; border: 1px solid #e2e8f0; margin-bottom: 20px;">
                    <div style="margin-bottom: 6px;"><strong>Auditorium:</strong> <?php echo htmlspecialchars($booking['auditorium_name']); ?></div>
                    <div style="margin-bottom: 6px;"><strong>Event:</strong> <?php echo htmlspecialchars($booking['event_name']); ?></div>
                    <div style="margin-bottom: 12px;"><strong>Event Date:</strong> <?php echo date('d M, Y', strtotime($booking['booking_date'])); ?></div>
                    
                    <div style="border-top: 1px dashed #cbd5e1; padding-top: 10px; margin-top: 10px;">
                        <div style="display:flex; justify-content:space-between; font-size:14px; margin-bottom: 4px;">
                            <span>Auditorium Base Rent:</span>
                            <span>₹<?php echo number_format($booking['base_price'], 2); ?></span>
                        </div>

                        <?php if ($booked_services_res && $booked_services_res->num_rows > 0): ?>
                            <?php while ($bs = $booked_services_res->fetch_assoc()): ?>
                                <div style="display:flex; justify-content:space-between; font-size:13px; color:#475569; margin-bottom: 4px; padding-left: 10px;">
                                    <span>+ <?php echo htmlspecialchars($bs['service_name']); ?>:</span>
                                    <span>₹<?php echo number_format($bs['service_price'], 2); ?></span>
                                </div>
                            <?php endwhile; ?>
                        <?php endif; ?>

                        <div style="display: flex; justify-content: space-between; margin-top: 10px; padding-top: 10px; border-top: 1px solid #cbd5e1; font-size: 16px; font-weight: 700;">
                            <span>Total Event Amount:</span>
                            <span>₹<?php echo number_format($booking['total_price'], 2); ?></span>
                        </div>
                    </div>
                </div>

                <form method="POST">
                    
                    <!-- Payment Mode (Advance vs Full) -->
                    <?php if (!$is_paying_balance): ?>
                        <div class="form-group">
                            <label style="font-weight:700; color:#1e3a8a;">Choose Payment Mode:</label>
                            <div style="display: flex; gap: 15px; margin-top: 8px;">
                                <label style="flex:1; border:2px solid #2563eb; background:#eff6ff; padding:12px; border-radius:8px; cursor:pointer;">
                                    <input type="radio" name="payment_mode" value="Full" checked onchange="updatePaymentButtonAmount(fullAmount, advanceAmount, isBalance)" style="margin-right:6px;">
                                    <strong>100% Full Payment</strong>
                                    <div style="font-size:13px; color:#2563eb; font-weight:700; margin-top:4px;">₹<?php echo number_format($booking['total_price'], 2); ?></div>
                                    <small style="font-size:11px; color:#64748b;">Pay total amount now (No balance)</small>
                                </label>

                                <label style="flex:1; border:2px solid #cbd5e1; background:#ffffff; padding:12px; border-radius:8px; cursor:pointer;">
                                    <input type="radio" name="payment_mode" value="Advance" onchange="updatePaymentButtonAmount(fullAmount, advanceAmount, isBalance)" style="margin-right:6px;">
                                    <strong>50% Advance Payment</strong>
                                    <div style="font-size:13px; color:#16a34a; font-weight:700; margin-top:4px;">₹<?php echo number_format($booking['total_price'] * 0.5, 2); ?></div>
                                    <small style="font-size:11px; color:#64748b;">Pay 50% now to reserve, rest later</small>
                                </label>
                            </div>
                        </div>
                    <?php else: ?>
                        <div style="background:#fef3c7; border:1px solid #f59e0b; padding:14px; border-radius:8px; margin-bottom:15px;">
                            <strong>Clearing Remaining Balance:</strong>
                            <div style="font-size:20px; font-weight:700; color:#92400e;">₹<?php echo number_format($booking['balance_amount'], 2); ?></div>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Select Payment Method:</label>
                        <select name="payment_method" id="payment_method" required onchange="togglePaymentMethodFields()">
                            <option value="Credit / Debit Card">💳 Credit / Debit Card</option>
                            <option value="UPI / QR Code">📱 UPI / QR Code</option>
                            <option value="Net Banking">🏦 Net Banking</option>
                            <option value="Cash at Venue">💵 Pay Cash at Venue</option>
                        </select>
                    </div>

                    <!-- Card Details Simulation -->
                    <div id="card_section">
                        <div class="form-group">
                            <label>Card Number:</label>
                            <input type="text" placeholder="1234 5678 9101 1121" maxlength="19">
                        </div>
                        <div style="display: flex; gap: 15px;">
                            <div class="form-group" style="flex: 1;">
                                <label>Expiry Date:</label>
                                <input type="text" placeholder="MM/YY" maxlength="5">
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label>CVV:</label>
                                <input type="password" placeholder="123" maxlength="3">
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="pay_btn" class="btn btn-success btn-block" style="padding: 14px; font-size: 16px; margin-top: 10px;">
                        <?php if ($is_paying_balance): ?>
                            Pay Balance ₹<?php echo number_format($booking['balance_amount'], 2); ?> & Clear Dues
                        <?php else: ?>
                            Pay ₹<?php echo number_format($booking['total_price'], 2); ?> & Confirm Booking
                        <?php endif; ?>
                    </button>
                </form>

            <?php endif; ?>

        </div>

    </div>

    <script>
        const fullAmount = <?php echo floatval($booking['total_price']); ?>;
        const advanceAmount = <?php echo round(floatval($booking['total_price']) * 0.5, 2); ?>;
        const isBalance = <?php echo $is_paying_balance ? 'true' : 'false'; ?>;

        function updatePayButton() {
            if (isBalance) return;
            const modes = document.getElementsByName('payment_mode');
            let selectedMode = 'Full';
            for (let m of modes) {
                if (m.checked) selectedMode = m.value;
            }

            const btn = document.getElementById('pay_btn');
            if (selectedMode === 'Advance') {
                btn.innerText = 'Pay 50% Advance (₹' + advanceAmount.toLocaleString('en-IN', {minimumFractionDigits: 2}) + ') & Confirm';
            } else {
                btn.innerText = 'Pay Full Amount (₹' + fullAmount.toLocaleString('en-IN', {minimumFractionDigits: 2}) + ') & Confirm';
            }
        }

        function togglePaymentFields() {
            var method = document.getElementById('payment_method').value;
            var cardSection = document.getElementById('card_section');
            if (method.includes('Card')) {
                cardSection.style.display = 'block';
            } else {
                cardSection.style.display = 'none';
            }
        }
    </script>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Auditorium Booking System</p>
    </footer>

<script src="../assets/js/main.js"></script>
</body>
</html>