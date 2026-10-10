<?php
session_start();
include '../db.php';

if (!isset($_GET['id'])) {
    header("Location: search_auditorium.php");
    exit();
}

$id = intval($_GET['id']);
$sql = "SELECT a.*, o.name AS owner_name, o.phone AS owner_phone, o.is_verified AS owner_is_verified, o.id_proof_type, o.id_proof_number 
        FROM auditoriums a 
        LEFT JOIN owners o ON a.owner_id = o.owner_id 
        WHERE a.auditorium_id = '$id'";
$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
    die("Auditorium not found. <a href='search_auditorium.php'>Go back</a>");
}

$hall = $result->fetch_assoc();

// Fetch Additional Services for this Auditorium
$services_res = $conn->query("SELECT * FROM services WHERE auditorium_id = '$id'");

$img_src = "https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=800&q=80";
if ($hall['auditorium_id'] == 1) $img_src = "https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=800&q=80";
if ($hall['auditorium_id'] == 2) $img_src = "https://images.unsplash.com/photo-1587825140708-dfaf72ae4b04?auto=format&fit=crop&w=800&q=80";
if ($hall['auditorium_id'] == 3) $img_src = "https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=800&q=80";
if ($hall['auditorium_id'] == 4) $img_src = "https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=800&q=80";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($hall['auditorium_name']); ?> | Auditorium Booking</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header>
        <div class="nav-container">
            <div class="logo"><a href="../index.php">🎭 Auditorium Booking</a></div>
            <nav>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="search_auditorium.php">Browse Auditoriums</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="index.php">Dashboard</a></li>
                        <li><a href="my_bookings.php">My Bookings</a></li>
                        <li><a href="logout.php" style="color: #fca5a5;">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        
        <a href="search_auditorium.php" class="btn btn-secondary btn-sm" style="margin-bottom: 20px;">&larr; Back to Auditoriums</a>

        <div class="grid-2">
            
            <!-- Auditorium Image & Highlights -->
            <div>
                <img src="<?php echo $img_src; ?>" alt="Auditorium" style="width: 100%; height: 320px; object-fit: cover; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                
                <!-- Hall Facilities -->
                <div class="card" style="margin-top: 20px;">
                    <div class="card-body">
                        <h3 class="card-title">🏢 In-Hall Facilities</h3>
                        <p style="color: #475569; font-size: 14px; margin-top: 8px;">
                            <?php echo htmlspecialchars($hall['facilities']); ?>
                        </p>
                    </div>
                </div>

                <!-- Available Additional Services -->
                <div class="card" style="margin-top: 20px;">
                    <div class="card-body">
                        <h3 class="card-title">✨ Available Add-on Services</h3>
                        <p style="font-size: 13px; color: #64748b; margin-bottom: 12px;">You can select any of these extra services during booking:</p>
                        
                        <?php if ($services_res && $services_res->num_rows > 0): ?>
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <?php while ($srv = $services_res->fetch_assoc()): ?>
                                    <div style="background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <strong style="color: #1e3a8a; font-size: 14px;">
                                                <?php
                                                $icon = "✨";
                                                if ($srv['service_category'] == 'Catering') $icon = "🍽️";
                                                if ($srv['service_category'] == 'Photography') $icon = "📸";
                                                if ($srv['service_category'] == 'Decoration') $icon = "🌸";
                                                if ($srv['service_category'] == 'DJ/Sound') $icon = "🎵";
                                                if ($srv['service_category'] == 'Security') $icon = "👮";
                                                echo "$icon " . htmlspecialchars($srv['service_name']);
                                                ?>
                                            </strong>
                                            <span style="font-weight: 700; color: #16a34a; font-size: 14px;">+₹<?php echo number_format($srv['price'], 2); ?></span>
                                        </div>
                                        <p style="font-size: 12px; color: #64748b; margin-top: 4px;">
                                            <?php echo htmlspecialchars($srv['description']); ?>
                                        </p>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p style="font-size: 13px; color: #94a3b8;">No additional services listed for this hall.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Details & Booking Action -->
            <div>
                <div class="card">
                    <div class="card-body">
                        <?php if ($hall['owner_is_verified'] == 1): ?>
                            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 10px; padding: 12px 15px; margin-bottom: 16px;">
                                <div style="display: flex; align-items: center; gap: 8px; color: #047857; font-weight: 600; font-size: 14px;">
                                    <span>🛡️</span> Genuine Verified Owner
                                </div>
                                <p style="font-size: 12.5px; color: #065f46; margin-top: 4px; line-height: 1.4;">
                                    This hall owner has been verified by the Admin using Government ID Proof (<strong><?php echo htmlspecialchars($hall['id_proof_type'] ?? 'ID Proof'); ?></strong>). You can safely book this venue.
                                </p>
                            </div>
                        <?php endif; ?>

                        <h1 style="font-size: 24px; color: #0f172a; margin-bottom: 10px;"><?php echo htmlspecialchars($hall['auditorium_name']); ?></h1>
                        <p class="card-info" style="font-size: 15px;">📍 <strong>Location:</strong> <?php echo htmlspecialchars($hall['location']); ?></p>
                        <p class="card-info" style="font-size: 15px;">👥 <strong>Capacity:</strong> <?php echo htmlspecialchars($hall['capacity']); ?> Guests</p>
                        <p class="card-info" style="font-size: 15px;">🟢 <strong>Status:</strong> <span class="badge badge-success"><?php echo $hall['status']; ?></span></p>
                        
                        <div class="card-price" style="font-size: 26px; margin: 15px 0;">
                            ₹<?php echo number_format($hall['price'], 2); ?> <span style="font-size: 14px; color: #64748b; font-weight: normal;">/ per day (Hall Rent)</span>
                        </div>

                        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 15px 0;">

                        <h4 style="color: #334155; margin-bottom: 8px;">About this Auditorium</h4>
                        <p style="color: #64748b; font-size: 14px; margin-bottom: 20px;">
                            <?php echo nl2br(htmlspecialchars($hall['description'])); ?>
                        </p>

                        <h4 style="color: #334155; margin-bottom: 6px;">Contact Information</h4>
                        <p style="color: #64748b; font-size: 14px; margin-bottom: 25px;">
                            👤 Owner: <strong><?php echo htmlspecialchars($hall['owner_name'] ?? 'Authorized Owner'); ?></strong> 
                            <?php if ($hall['owner_is_verified'] == 1): ?>
                                <span class="badge badge-success" style="font-size: 11px;">✓ Verified</span>
                            <?php endif; ?><br>
                            📞 Phone: <strong><?php echo htmlspecialchars($hall['owner_phone'] ?? '9876543210'); ?></strong>
                        </p>

                        <a href="book_auditorium.php?id=<?php echo $hall['auditorium_id']; ?>" class="btn btn-primary btn-block" style="padding: 14px; font-size: 16px;">
                            📅 Book Auditorium & Select Services
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Auditorium Booking System</p>
    </footer>

</body>
</html>