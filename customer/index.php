<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Get Customer Stats
$total_bookings = $conn->query("SELECT COUNT(*) AS total FROM bookings WHERE user_id='$user_id'")->fetch_assoc()['total'];
$confirmed_bookings = $conn->query("SELECT COUNT(*) AS total FROM bookings WHERE user_id='$user_id' AND status='Confirmed'")->fetch_assoc()['total'];

// Check Unread Notifications
$unread_notif = $conn->query("SELECT COUNT(*) AS unread FROM notifications WHERE user_id='$user_id' AND user_type='customer' AND is_read=0")->fetch_assoc()['unread'];

// Check Upcoming Event Reminders (within next 30 days)
$today = date('Y-m-d');
$upcoming_event = $conn->query("SELECT b.*, a.auditorium_name, a.location 
                                FROM bookings b 
                                JOIN auditoriums a ON b.auditorium_id = a.auditorium_id 
                                WHERE b.user_id='$user_id' AND b.status='Confirmed' AND b.booking_date >= '$today' 
                                ORDER BY b.booking_date ASC LIMIT 1")->fetch_assoc();

// Recent Bookings
$recent_bookings = $conn->query("SELECT b.*, a.auditorium_name, a.location 
                                 FROM bookings b 
                                 JOIN auditoriums a ON b.auditorium_id = a.auditorium_id 
                                 WHERE b.user_id='$user_id' 
                                 ORDER BY b.booking_id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard | Auditorium Booking</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header>
        <div class="nav-container">
            <div class="logo"><a href="../index.php">🎭 Auditorium Booking</a></div>
            <nav>
                <ul>
                    <li><a href="index.php" class="active">Dashboard</a></li>
                    <li><a href="search_auditorium.php">Search Auditoriums</a></li>
                    <li><a href="my_bookings.php">My Bookings</a></li>
                    <li>
                        <a href="notifications.php">
                            🔔 Notifications 
                            <?php if ($unread_notif > 0): ?>
                                <span style="background:#ef4444; color:white; font-size:11px; padding:2px 6px; border-radius:10px;"><?php echo $unread_notif; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li><a href="logout.php" style="color: #fca5a5;">Logout (<?php echo htmlspecialchars($user_name); ?>)</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        
        <div style="margin-bottom: 25px;">
            <h1 style="font-size: 26px; color:#0f172a;">Welcome back, <?php echo htmlspecialchars($user_name); ?>! 👋</h1>
            <p style="color: #64748b;">Manage your event reservations, catering & event services, and download invoices.</p>
        </div>

        <!-- Automatic Event Reminder Alert Banner (Notifications & Alerts) -->
        <?php if ($upcoming_event): ?>
            <?php 
                $days_left = (strtotime($upcoming_event['booking_date']) - strtotime($today)) / (60 * 60 * 24);
                $day_text = ($days_left == 0) ? "TODAY!" : "in $days_left day(s)";
            ?>
            <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 1px solid #f59e0b; padding: 18px 22px; border-radius: 12px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div>
                    <h3 style="color: #92400e; margin: 0 0 4px 0; font-size: 17px;">
                        ⏰ Upcoming Event Reminder: <strong><?php echo htmlspecialchars($upcoming_event['event_name']); ?></strong>
                    </h3>
                    <p style="color: #78350f; margin: 0; font-size: 14px;">
                        Scheduled at <strong><?php echo htmlspecialchars($upcoming_event['auditorium_name']); ?></strong> on <strong><?php echo date('l, d M Y', strtotime($upcoming_event['booking_date'])); ?></strong> (<?php echo $day_text; ?>).
                    </p>
                </div>
                <div>
                    <a href="view_ticket.php?booking_id=<?php echo $upcoming_event['booking_id']; ?>" class="btn btn-primary btn-sm">View Entry Pass</a>
                </div>
            </div>
        <?php endif; ?>

        <!-- KPI Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Bookings</h3>
                <div class="stat-number"><?php echo $total_bookings; ?></div>
            </div>

            <div class="stat-card">
                <h3>Confirmed Events</h3>
                <div class="stat-number" style="color: #16a34a;"><?php echo $confirmed_bookings; ?></div>
            </div>

            <div class="stat-card">
                <h3>Notifications</h3>
                <div class="stat-number" style="color: #2563eb;">
                    <a href="notifications.php" style="text-decoration:none; color:#2563eb;"><?php echo $unread_notif; ?> New</a>
                </div>
            </div>
        </div>

        <!-- Recent Bookings Table -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h2 class="section-title" style="margin-bottom: 0;">📅 Your Recent Bookings</h2>
            <a href="my_bookings.php" class="btn btn-secondary btn-sm">View All Bookings</a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Auditorium</th>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Payment Type</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent_bookings && $recent_bookings->num_rows > 0): ?>
                        <?php while ($row = $recent_bookings->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['booking_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['auditorium_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['event_name']); ?></td>
                                <td><?php echo date('d M, Y', strtotime($row['booking_date'])); ?></td>
                                <td>
                                    <?php if ($row['balance_amount'] > 0): ?>
                                        <span class="badge badge-warning">Advance Paid (Bal: ₹<?php echo number_format($row['balance_amount'], 2); ?>)</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Fully Paid</span>
                                    <?php endif; ?>
                                </td>
                                <td>₹<?php echo number_format($row['total_price'], 2); ?></td>
                                <td><span class="badge badge-<?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
                                <td>
                                    <a href="view_ticket.php?booking_id=<?php echo $row['booking_id']; ?>" class="btn btn-primary btn-sm">Ticket</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center; padding: 25px; color:#64748b;">
                                You haven't made any bookings yet. <a href="search_auditorium.php" style="color:#2563eb; font-weight:600;">Browse auditoriums now!</a>
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