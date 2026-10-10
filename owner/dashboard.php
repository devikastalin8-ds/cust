<?php
session_start();
include '../db.php';

// Check if owner is logged in
if (!isset($_SESSION['owner_id'])) {
    header("Location: login.php");
    exit();
}

$owner_id = $_SESSION['owner_id'];
$owner_name = $_SESSION['owner_name'];

// Fetch Owner Profile Details for Verification Check
$owner_data = $conn->query("SELECT * FROM owners WHERE owner_id='$owner_id'")->fetch_assoc();
$is_verified = $owner_data['is_verified'] ?? 0;

// 1. Total Auditoriums Count
$halls_res = $conn->query("SELECT COUNT(*) AS total FROM auditoriums WHERE owner_id='$owner_id'");
$total_halls = $halls_res->fetch_assoc()['total'];

// 2. Total Additional Services Offered
$services_res = $conn->query("SELECT COUNT(s.service_id) AS total 
                              FROM services s 
                              JOIN auditoriums a ON s.auditorium_id = a.auditorium_id 
                              WHERE a.owner_id='$owner_id'");
$total_services = $services_res->fetch_assoc()['total'];

// 3. Total Bookings Received for Owner's Auditoriums
$bookings_res = $conn->query("SELECT COUNT(b.booking_id) AS total, SUM(CASE WHEN b.status='Confirmed' THEN b.total_price ELSE 0 END) AS total_revenue 
                             FROM bookings b 
                             JOIN auditoriums a ON b.auditorium_id = a.auditorium_id 
                             WHERE a.owner_id='$owner_id'");
$booking_stats = $bookings_res->fetch_assoc();
$total_bookings = $booking_stats['total'];
$total_revenue = $booking_stats['total_revenue'] ?? 0;

// 4. Recent Bookings for Owner
$recent_bookings = $conn->query("SELECT b.*, a.auditorium_name, u.name AS customer_name, u.phone AS customer_phone 
                                 FROM bookings b 
                                 JOIN auditoriums a ON b.auditorium_id = a.auditorium_id 
                                 JOIN users u ON b.user_id = u.user_id 
                                 WHERE a.owner_id='$owner_id' 
                                 ORDER BY b.booking_id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard | Auditorium Booking</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header>
        <div class="nav-container">
            <div class="logo"><a href="../index.php">🎭 Auditorium Booking</a></div>
            <nav>
                <ul>
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="my_auditoriums.php">My Auditoriums</a></li>
                    <li><a href="manage_services.php">Manage Services</a></li>
                    <li><a href="view_bookings.php">Customer Bookings</a></li>
                    <li><a href="logout.php" style="color: #fca5a5;">Logout (<?php echo htmlspecialchars($owner_name); ?>)</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        
        <div style="margin-bottom: 25px;">
            <h1 style="font-size: 26px; color:#0f172a;">Welcome, <?php echo htmlspecialchars($owner_name); ?>! 🏢</h1>
            <p style="color: #64748b;">Manage your auditorium listings, add-on services (Catering, Photography, Decor), and customer reservations.</p>
        </div>

        <!-- Genuine Owner Verification Status Banner -->
        <?php if ($is_verified == 1): ?>
            <div class="alert alert-success" style="display: flex; align-items: center; gap: 12px; margin-bottom: 25px;">
                <span style="font-size: 24px;">🛡️</span>
                <div>
                    <strong style="font-size: 15px;">Verified Genuine Owner Account:</strong><br>
                    <span style="font-size: 13.5px;">Your ID Proof (<?php echo htmlspecialchars($owner_data['id_proof_type'] ?? 'Government ID'); ?>: <?php echo htmlspecialchars($owner_data['id_proof_number'] ?? ''); ?>) has been verified by Admin. Your venues display the <strong>"✓ Verified Genuine Owner"</strong> trust badge.</span>
                </div>
            </div>
        <?php elseif ($is_verified == 2): ?>
            <div class="alert alert-danger" style="display: flex; align-items: center; gap: 12px; margin-bottom: 25px;">
                <span style="font-size: 24px;">⚠️</span>
                <div>
                    <strong style="font-size: 15px;">ID Proof Verification Rejected:</strong><br>
                    <span style="font-size: 13.5px;">Your ID proof document was rejected by the Admin. Please contact support or re-register with valid Government ID / Business License proof.</span>
                </div>
            </div>
        <?php else: ?>
            <div style="background: #fef3c7; border: 1px solid #fde68a; color: #92400e; padding: 16px 20px; border-radius: 10px; margin-bottom: 25px; display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 24px;">🟡</span>
                <div>
                    <strong style="font-size: 15px;">Genuine Owner Verification Under Review:</strong><br>
                    <span style="font-size: 13.5px;">Your submitted ID proof (<?php echo htmlspecialchars($owner_data['id_proof_type'] ?? 'ID Proof'); ?>: <?php echo htmlspecialchars($owner_data['id_proof_number'] ?? 'Submitted'); ?>) is currently under review by Admin.</span>
                </div>
            </div>
        <?php endif; ?>

        <!-- KPI Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>My Listed Auditoriums</h3>
                <div class="stat-number"><?php echo $total_halls; ?></div>
            </div>

            <div class="stat-card">
                <h3>Add-on Services Offered</h3>
                <div class="stat-number" style="color: #7c3aed;"><?php echo $total_services; ?></div>
            </div>

            <div class="stat-card">
                <h3>Total Bookings Received</h3>
                <div class="stat-number" style="color: #2563eb;"><?php echo $total_bookings; ?></div>
            </div>

            <div class="stat-card">
                <h3>Total Earnings</h3>
                <div class="stat-number" style="color: #16a34a;">₹<?php echo number_format($total_revenue, 2); ?></div>
            </div>
        </div>

        <!-- Quick Action Buttons -->
        <div style="margin-bottom: 30px; display: flex; gap: 15px; flex-wrap: wrap;">
            <a href="add_auditorium.php" class="btn btn-primary">+ Add New Auditorium</a>
            <a href="add_service.php" class="btn btn-success">+ Add Catering / Photo Service</a>
            <a href="manage_services.php" class="btn btn-secondary">Manage Services</a>
            <a href="view_bookings.php" class="btn btn-primary">View Customer Bookings</a>
        </div>

        <!-- Recent Bookings Table -->
        <h2 class="section-title">📥 Recent Customer Bookings</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Auditorium</th>
                        <th>Customer</th>
                        <th>Event Name</th>
                        <th>Booking Date</th>
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
                                <td><?php echo htmlspecialchars($row['customer_name']); ?><br><small style="color:#64748b;"><?php echo htmlspecialchars($row['customer_phone']); ?></small></td>
                                <td><?php echo htmlspecialchars($row['event_name']); ?></td>
                                <td><?php echo date('d M, Y', strtotime($row['booking_date'])); ?></td>
                                <td>₹<?php echo number_format($row['total_price'], 2); ?></td>
                                <td><span class="badge badge-<?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 25px; color:#64748b;">
                                No bookings received yet.
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