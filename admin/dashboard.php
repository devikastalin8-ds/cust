<?php
session_start();
include '../db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// 1. Total Customers Count
$users_res = $conn->query("SELECT COUNT(*) AS total FROM users");
$total_users = $users_res->fetch_assoc()['total'];

// 2. Total Owners Count
$owners_res = $conn->query("SELECT COUNT(*) AS total FROM owners");
$total_owners = $owners_res->fetch_assoc()['total'];

// 3. Total Auditoriums Count
$aud_res = $conn->query("SELECT COUNT(*) AS total FROM auditoriums");
$total_auditoriums = $aud_res->fetch_assoc()['total'];

// 4. Total Bookings Count & Revenue
$bk_res = $conn->query("SELECT COUNT(*) AS total, SUM(CASE WHEN status='Confirmed' THEN total_price ELSE 0 END) AS revenue FROM bookings");
$bk_stats = $bk_res->fetch_assoc();
$total_bookings = $bk_stats['total'];
$total_revenue = $bk_stats['revenue'] ?? 0;

// Recent Bookings across system
$recent_bookings = $conn->query("SELECT b.*, a.auditorium_name, u.name AS customer_name 
                                 FROM bookings b 
                                 JOIN auditoriums a ON b.auditorium_id = a.auditorium_id 
                                 JOIN users u ON b.user_id = u.user_id 
                                 ORDER BY b.booking_id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Auditorium Booking</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header>
        <div class="nav-container">
            <div class="logo"><a href="dashboard.php">🛡️ Admin Dashboard</a></div>
            <nav>
                <ul>
                    <li><a href="dashboard.php" class="active">Overview</a></li>
                    <li><a href="manage_users.php">Customers</a></li>
                    <li><a href="manage_owners.php">Owners</a></li>
                    <li><a href="manage_auditoriums.php">Auditoriums</a></li>
                    <li><a href="manage_bookings.php">All Bookings</a></li>
                    <li><a href="logout.php" style="color: #fca5a5;">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        
        <div style="margin-bottom: 25px;">
            <h1 style="font-size: 26px; color:#0f172a;">Platform Administration Overview 📊</h1>
            <p style="color: #64748b;">Monitor platform statistics, manage customer and owner accounts, and oversee all auditorium reservations.</p>
        </div>

        <!-- KPI Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Customers</h3>
                <div class="stat-number" style="color:#2563eb;"><?php echo $total_users; ?></div>
                <a href="manage_users.php" style="font-size: 13px; color:#64748b;">View Details &rarr;</a>
            </div>

            <div class="stat-card">
                <h3>Total Owners</h3>
                <div class="stat-number" style="color:#7c3aed;"><?php echo $total_owners; ?></div>
                <a href="manage_owners.php" style="font-size: 13px; color:#64748b;">View Details &rarr;</a>
            </div>

            <div class="stat-card">
                <h3>Auditoriums Listed</h3>
                <div class="stat-number" style="color:#0284c7;"><?php echo $total_auditoriums; ?></div>
                <a href="manage_auditoriums.php" style="font-size: 13px; color:#64748b;">View Details &rarr;</a>
            </div>

            <div class="stat-card">
                <h3>Total Bookings</h3>
                <div class="stat-number" style="color:#ea580c;"><?php echo $total_bookings; ?></div>
                <a href="manage_bookings.php" style="font-size: 13px; color:#64748b;">View Details &rarr;</a>
            </div>

            <div class="stat-card">
                <h3>Total Gross Revenue</h3>
                <div class="stat-number" style="color:#16a34a;">₹<?php echo number_format($total_revenue, 2); ?></div>
                <span style="font-size: 12px; color:#64748b;">Confirmed bookings</span>
            </div>
        </div>

        <!-- Quick Access Section -->
        <h2 class="section-title">⚡ Quick Management Portals</h2>
        <div class="grid-3" style="margin-bottom: 30px;">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">👥 Manage Customers</h3>
                    <p style="color:#64748b; font-size:14px; margin-bottom:15px;">View list of all registered customers and manage user accounts.</p>
                    <a href="manage_users.php" class="btn btn-primary btn-sm">Manage Customers</a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">🏢 Manage Owners</h3>
                    <p style="color:#64748b; font-size:14px; margin-bottom:15px;">Review auditorium owner accounts and check their registered listings.</p>
                    <a href="manage_owners.php" class="btn btn-primary btn-sm">Manage Owners</a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">🎭 Manage Auditoriums</h3>
                    <p style="color:#64748b; font-size:14px; margin-bottom:15px;">Browse, inspect, and remove auditorium venues across all owners.</p>
                    <a href="manage_auditoriums.php" class="btn btn-primary btn-sm">Manage Auditoriums</a>
                </div>
            </div>
        </div>

        <!-- System Recent Bookings -->
        <h2 class="section-title">📋 Global Recent Bookings</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Auditorium</th>
                        <th>Customer</th>
                        <th>Event Name</th>
                        <th>Event Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent_bookings && $recent_bookings->num_rows > 0): ?>
                        <?php while ($row = $recent_bookings->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['booking_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['auditorium_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['event_name']); ?></td>
                                <td><?php echo date('d M, Y', strtotime($row['booking_date'])); ?></td>
                                <td>₹<?php echo number_format($row['total_price'], 2); ?></td>
                                <td><span class="badge badge-<?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 25px; color:#64748b;">No bookings found in the system.</td>
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