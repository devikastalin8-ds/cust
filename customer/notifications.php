<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Mark all as read
$conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = '$user_id' AND user_type = 'customer'");

// Fetch notifications
$sql = "SELECT * FROM notifications WHERE user_id = '$user_id' AND user_type = 'customer' ORDER BY notification_id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications & Event Reminders | Customer</title>
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
                    <li><a href="notifications.php" class="active">🔔 Notifications</a></li>
                    <li><a href="logout.php" style="color: #fca5a5;">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1 class="section-title" style="margin-bottom: 0;">🔔 Notifications & Event Reminders</h1>
            <a href="index.php" class="btn btn-secondary btn-sm">&larr; Back to Dashboard</a>
        </div>

        <div class="card" style="max-width: 800px; margin: 0 auto;">
            <div class="card-body">
                <?php if ($result && $result->num_rows > 0): ?>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php
                            $bg = "#f8fafc";
                            $icon = "🔔";
                            $badge_color = "#3b82f6";
                            if ($row['type'] == 'confirmation') { $icon = "✅"; $bg = "#f0fdf4"; $badge_color = "#16a34a"; }
                            if ($row['type'] == 'payment') { $icon = "💳"; $bg = "#eff6ff"; $badge_color = "#2563eb"; }
                            if ($row['type'] == 'reminder') { $icon = "⏰"; $bg = "#fefce8"; $badge_color = "#ca8a04"; }
                            ?>
                            <div style="background: <?php echo $bg; ?>; border: 1px solid #e2e8f0; padding: 16px; border-radius: 10px;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                                    <h4 style="color: #1e293b; margin: 0; font-size: 16px;">
                                        <?php echo $icon; ?> <?php echo htmlspecialchars($row['title']); ?>
                                    </h4>
                                    <span style="font-size: 11px; background: <?php echo $badge_color; ?>; color: white; padding: 3px 8px; border-radius: 12px; text-transform: uppercase; font-weight:600;">
                                        <?php echo htmlspecialchars($row['type']); ?>
                                    </span>
                                </div>
                                <p style="color: #475569; font-size: 14px; margin-bottom: 8px;">
                                    <?php echo htmlspecialchars($row['message']); ?>
                                </p>
                                <div style="font-size: 12px; color: #94a3b8;">
                                    🕒 <?php echo date('d M, Y h:i A', strtotime($row['created_at'])); ?>
                                    <?php if ($row['booking_id']): ?>
                                        &bull; <a href="view_ticket.php?booking_id=<?php echo $row['booking_id']; ?>" style="color: #2563eb; font-weight:600;">View Ticket & Invoice &rarr;</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: #64748b; padding: 30px;">No notifications yet.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Auditorium Booking System</p>
    </footer>

</body>
</html>