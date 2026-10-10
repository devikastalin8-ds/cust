<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$msg_type = "";

// Handle Verification Actions (Approve / Reject)
if (isset($_GET['verify_id'])) {
    $verify_id = intval($_GET['verify_id']);
    if ($conn->query("UPDATE owners SET is_verified = 1 WHERE owner_id='$verify_id'") === TRUE) {
        $message = "Auditorium Owner #$verify_id has been verified as Genuine successfully!";
        $msg_type = "success";
    }
}
if (isset($_GET['reject_id'])) {
    $reject_id = intval($_GET['reject_id']);
    if ($conn->query("UPDATE owners SET is_verified = 2 WHERE owner_id='$reject_id'") === TRUE) {
        $message = "Verification status for Owner #$reject_id has been set to Rejected.";
        $msg_type = "warning";
    }
}

// Handle Delete Owner with complete cascading cleanup
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Get all auditorium IDs owned by this owner
    $aud_res = $conn->query("SELECT auditorium_id FROM auditoriums WHERE owner_id='$delete_id'");
    $aud_ids = [];
    while ($a = $aud_res->fetch_assoc()) {
        $aud_ids[] = $a['auditorium_id'];
    }

    if (!empty($aud_ids)) {
        $aud_list = implode(',', $aud_ids);
        
        // Delete booking_services for owner's halls
        $conn->query("DELETE bs FROM booking_services bs JOIN bookings b ON bs.booking_id = b.booking_id WHERE b.auditorium_id IN ($aud_list)");
        
        // Delete payments for owner's halls
        $conn->query("DELETE p FROM payments p JOIN bookings b ON p.booking_id = b.booking_id WHERE b.auditorium_id IN ($aud_list)");
        
        // Delete bookings for owner's halls
        $conn->query("DELETE FROM bookings WHERE auditorium_id IN ($aud_list)");
        
        // Delete services for owner's halls
        $conn->query("DELETE FROM services WHERE auditorium_id IN ($aud_list)");
        
        // Delete auditoriums
        $conn->query("DELETE FROM auditoriums WHERE owner_id='$delete_id'");
    }

    // Delete owner account
    if ($conn->query("DELETE FROM owners WHERE owner_id='$delete_id'") === TRUE) {
        $message = "Auditorium Owner #$delete_id and their listed halls have been deleted successfully.";
        $msg_type = "success";
    } else {
        $message = "Error deleting owner: " . $conn->error;
        $msg_type = "danger";
    }
}

// Fetch owners with count of their auditoriums
$sql = "SELECT o.*, COUNT(a.auditorium_id) AS total_halls 
        FROM owners o 
        LEFT JOIN auditoriums a ON o.owner_id = a.owner_id 
        GROUP BY o.owner_id 
        ORDER BY o.owner_id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage & Verify Owners | Admin</title>
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
                    <li><a href="manage_owners.php" class="active">Owners & Verification</a></li>
                    <li><a href="manage_auditoriums.php">Auditoriums</a></li>
                    <li><a href="manage_bookings.php">All Bookings</a></li>
                    <li><a href="logout.php" style="color: #fca5a5;">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        
        <h1 class="section-title">🏢 Registered Auditorium Owners & Genuine Verification</h1>

        <?php if ($message != ""): ?>
            <div class="alert alert-<?php echo $msg_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Owner ID</th>
                        <th>Name / Organization</th>
                        <th>Email & Phone</th>
                        <th>Submitted ID Proof</th>
                        <th>Verification Status</th>
                        <th>Halls Listed</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['owner_id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                    <?php if ($row['is_verified'] == 1): ?>
                                        <br><span class="badge badge-success" style="font-size: 11px;">✓ Verified Genuine</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    ✉️ <?php echo htmlspecialchars($row['email']); ?><br>
                                    📞 <?php echo htmlspecialchars($row['phone']); ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['id_proof_type'] ?? 'Aadhaar Card'); ?></strong><br>
                                    <span style="font-size: 12px; color:#64748b;">No: <?php echo htmlspecialchars($row['id_proof_number'] ?? 'N/A'); ?></span>
                                    <?php if (!empty($row['id_proof_file'])): ?>
                                        <br><a href="../uploads/id_proofs/<?php echo htmlspecialchars($row['id_proof_file']); ?>" target="_blank" class="btn btn-secondary btn-sm" style="padding: 2px 8px; font-size: 11px; margin-top: 4px;">📄 View Document</a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['is_verified'] == 1): ?>
                                        <span class="badge badge-success">🟢 Verified Genuine</span>
                                    <?php elseif ($row['is_verified'] == 2): ?>
                                        <span class="badge badge-danger">🔴 Rejected</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">🟡 Pending Verification</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo $row['total_halls']; ?></strong> Halls</td>
                                <td>
                                    <div style="display:flex; flex-direction:column; gap:4px;">
                                        <?php if ($row['is_verified'] != 1): ?>
                                            <a href="manage_owners.php?verify_id=<?php echo $row['owner_id']; ?>" onclick="return confirm('Verify owner <?php echo htmlspecialchars(addslashes($row['name'])); ?> as genuine?');" class="btn btn-success btn-sm">✓ Approve & Verify</a>
                                        <?php endif; ?>

                                        <?php if ($row['is_verified'] != 2): ?>
                                            <a href="manage_owners.php?reject_id=<?php echo $row['owner_id']; ?>" class="btn btn-secondary btn-sm">❌ Reject</a>
                                        <?php endif; ?>

                                        <a href="manage_owners.php?delete_id=<?php echo $row['owner_id']; ?>" onclick="return confirm('Are you sure you want to delete owner <?php echo htmlspecialchars(addslashes($row['name'])); ?>? This will also delete all auditoriums listed by this owner.');" class="btn btn-danger btn-sm">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 25px; color:#64748b;">No registered owners found.</td>
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