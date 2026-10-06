<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    $current_url = "book_auditorium.php?id=" . ($_GET['id'] ?? '');
    header("Location: login.php?redirect=" . urlencode($current_url));
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: search_auditorium.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$auditorium_id = intval($_GET['id']);

// Fetch Auditorium
$res = $conn->query("SELECT * FROM auditoriums WHERE auditorium_id = '$auditorium_id'");
if (!$res || $res->num_rows == 0) {
    die("Auditorium not found.");
}
$hall = $res->fetch_assoc();

// Fetch Catering Packages
$catering_res = $conn->query("SELECT * FROM services WHERE auditorium_id = '$auditorium_id' AND service_category = 'Catering'");
$catering_services = [];
while ($row = $catering_res->fetch_assoc()) {
    $catering_services[] = $row;
}

// Fetch Event Services (Decoration, Photography, Sound, Lighting, Security, Cleaning, Stage)
$event_res = $conn->query("SELECT * FROM services WHERE auditorium_id = '$auditorium_id' AND service_category != 'Catering'");
$event_services = [];
while ($row = $event_res->fetch_assoc()) {
    $event_services[] = $row;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_date = $_POST['booking_date'];
    $event_name   = trim($_POST['event_name']);
    $guest_count  = intval($_POST['guest_count']);
    $base_price   = floatval($hall['price']);
    
    // Process selected services (Catering + Event Services)
    $selected_service_ids = isset($_POST['services']) ? $_POST['services'] : [];
    $services_total = 0.00;
    $chosen_services = [];

    // All available services for this hall
    $all_services = array_merge($catering_services, $event_services);
    foreach ($all_services as $srv) {
        if (in_array($srv['service_id'], $selected_service_ids)) {
            $services_total += floatval($srv['price']);
            $chosen_services[] = $srv;
        }
    }

    $grand_total = $base_price + $services_total;

    // 1. Check double booking
    $check_sql = "SELECT * FROM bookings 
                  WHERE auditorium_id = '$auditorium_id' 
                  AND booking_date = '$booking_date' 
                  AND status != 'Cancelled'";
    $check_res = $conn->query($check_sql);

    if ($check_res->num_rows > 0) {
        $message = "Sorry! This auditorium is already booked on " . date('d M, Y', strtotime($booking_date)) . ". Please select a different date.";
    } else {
        // 2. Insert new booking
        $insert_sql = "INSERT INTO bookings (user_id, auditorium_id, booking_date, event_name, guest_count, base_price, services_price, total_price, status) 
                       VALUES ('$user_id', '$auditorium_id', '$booking_date', '$event_name', '$guest_count', '$base_price', '$services_total', '$grand_total', 'Pending')";
        
        if ($conn->query($insert_sql) === TRUE) {
            $booking_id = $conn->insert_id;

            // 3. Save selected services
            foreach ($chosen_services as $cs) {
                $sid = $cs['service_id'];
                $sname = $conn->real_escape_string($cs['service_name']);
                $sprice = floatval($cs['price']);
                $conn->query("INSERT INTO booking_services (booking_id, service_id, service_name, service_price) 
                              VALUES ('$booking_id', '$sid', '$sname', '$sprice')");
            }

            // 4. Create Booking Notification
            $notif_msg = "Booking created for {$hall['auditorium_name']} on " . date('d M, Y', strtotime($booking_date)) . ". Please complete payment.";
            $conn->query("INSERT INTO notifications (user_id, user_type, booking_id, title, message, type) 
                          VALUES ('$user_id', 'customer', '$booking_id', 'Booking Reserved', '$notif_msg', 'confirmation')");

            header("Location: payment.php?booking_id=$booking_id");
            exit();
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Auditorium & Customize Services | <?php echo htmlspecialchars($hall['auditorium_name']); ?></title>
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
        <div class="form-container" style="max-width: 720px;">
            <h2 style="text-align:center; color:#1e3a8a; margin-bottom: 4px;">Reserve Auditorium & Select Packages</h2>
            <p style="text-align:center; color:#64748b; font-size:14px; margin-bottom: 20px;">
                <strong><?php echo htmlspecialchars($hall['auditorium_name']); ?></strong> (Base Rent: ₹<?php echo number_format($hall['price'], 2); ?> / day)
            </p>

            <?php if ($message != ""): ?>
                <div class="alert alert-danger"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST">
                
                <!-- Event Basic Details -->
                <div class="form-group">
                    <label>Event Name / Occasion:</label>
                    <input type="text" name="event_name" required placeholder="e.g. Annual Wedding Gala, Tech Conference, Birthday Party" value="<?php echo isset($_POST['event_name']) ? htmlspecialchars($_POST['event_name']) : ''; ?>">
                </div>

                <div style="display:flex; gap:15px;">
                    <div class="form-group" style="flex:1;">
                        <label>Required Event Date:</label>
                        <input type="date" name="booking_date" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo isset($_POST['booking_date']) ? htmlspecialchars($_POST['booking_date']) : ''; ?>">
                    </div>

                    <div class="form-group" style="flex:1;">
                        <label>Estimated Number of Guests:</label>
                        <input type="number" name="guest_count" required min="10" max="<?php echo $hall['capacity']; ?>" value="<?php echo isset($_POST['guest_count']) ? htmlspecialchars($_POST['guest_count']) : '100'; ?>">
                    </div>
                </div>

                <!-- 1. CATERING MODULE PACKAGES -->
                <div style="margin-top: 25px;">
                    <h3 style="font-size: 16px; color: #1e3a8a; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 12px;">
                        🍽️ Food & Catering Packages
                    </h3>
                    
                    <?php if (!empty($catering_services)): ?>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <?php foreach ($catering_services as $srv): ?>
                                <label style="display: flex; align-items: flex-start; gap: 12px; background: #fffbeb; padding: 12px 14px; border-radius: 8px; border: 1px solid #fef3c7; cursor: pointer;">
                                    <input type="checkbox" name="services[]" value="<?php echo $srv['service_id']; ?>" data-price="<?php echo $srv['price']; ?>" onchange="calculateBookingTotal(<?php echo floatval($hall['price']); ?>)" style="width: 18px; height: 18px; margin-top: 3px; cursor: pointer;">
                                    <div style="flex: 1;">
                                        <div style="display: flex; justify-content: space-between;">
                                            <strong style="color: #92400e; font-size: 14px;">🍲 <?php echo htmlspecialchars($srv['service_name']); ?></strong>
                                            <span style="color: #16a34a; font-weight: 700; font-size: 14px;">+₹<?php echo number_format($srv['price'], 2); ?></span>
                                        </div>
                                        <div style="color: #78350f; font-size: 12px; margin-top: 3px;"><?php echo htmlspecialchars($srv['description']); ?></div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p style="color: #94a3b8; font-size: 13px;">No catering packages listed for this hall.</p>
                    <?php endif; ?>
                </div>

                <!-- 2. EVENT SERVICES MODULE (Decoration, Photo, Sound, Lighting, Staff) -->
                <div style="margin-top: 25px;">
                    <h3 style="font-size: 16px; color: #1e3a8a; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 12px;">
                        ✨ Event & Stage Services
                    </h3>
                    
                    <?php if (!empty($event_services)): ?>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <?php foreach ($event_services as $srv): ?>
                                <?php
                                $icon = "✨";
                                if ($srv['service_category'] == 'Decoration') $icon = "🌸";
                                if ($srv['service_category'] == 'Photography') $icon = "📸";
                                if ($srv['service_category'] == 'DJ/Sound') $icon = "🎵";
                                if ($srv['service_category'] == 'Lighting') $icon = "💡";
                                if ($srv['service_category'] == 'Security') $icon = "👮";
                                if ($srv['service_category'] == 'Cleaning') $icon = "🧹";
                                if ($srv['service_category'] == 'Stage') $icon = "🏛️";
                                ?>
                                <label style="display: flex; align-items: flex-start; gap: 12px; background: #f8fafc; padding: 12px 14px; border-radius: 8px; border: 1px solid #e2e8f0; cursor: pointer;">
                                    <input type="checkbox" name="services[]" value="<?php echo $srv['service_id']; ?>" data-price="<?php echo $srv['price']; ?>" onchange="calculateBookingTotal(<?php echo floatval($hall['price']); ?>)" style="width: 18px; height: 18px; margin-top: 3px; cursor: pointer;">
                                    <div style="flex: 1;">
                                        <div style="display: flex; justify-content: space-between;">
                                            <strong style="color: #0f172a; font-size: 14px;"><?php echo "$icon " . htmlspecialchars($srv['service_name']); ?></strong>
                                            <span style="color: #16a34a; font-weight: 700; font-size: 14px;">+₹<?php echo number_format($srv['price'], 2); ?></span>
                                        </div>
                                        <div style="color: #64748b; font-size: 12px; margin-top: 3px;"><?php echo htmlspecialchars($srv['description']); ?></div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p style="color: #94a3b8; font-size: 13px;">No event services listed for this hall.</p>
                    <?php endif; ?>
                </div>

                <!-- Price Summary & Advance Preview Box -->
                <div style="background: #eff6ff; padding: 18px; border-radius: 10px; border: 1px solid #bfdbfe; margin: 25px 0;">
                    <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 6px; color: #334155;">
                        <span>Auditorium Base Rent:</span>
                        <span>₹<?php echo number_format($hall['price'], 2); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 8px; color: #334155;">
                        <span>Selected Catering & Event Services:</span>
                        <span id="services_display_cost">₹0.00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 18px; color: #1e3a8a; border-top: 1px dashed #93c5fd; padding-top: 10px;">
                        <span>Total Event Budget:</span>
                        <span id="grand_total_display" style="color: #2563eb;">₹<?php echo number_format($hall['price'], 2); ?></span>
                    </div>
                    <div style="font-size: 13px; color: #15803d; margin-top: 6px;">
                        💡 <em>Tip: You can choose to pay <strong>50% Advance</strong> or <strong>100% Full Payment</strong> on the next screen.</em>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="padding: 14px; font-size: 16px;">
                    Proceed to Payment Options &rarr;
                </button>
            </form>
        </div>
    </div>

    <!-- Client-Side Real-Time Calculator -->
    <script>
        const baseRent = <?php echo floatval($hall['price']); ?>;

        function calculateTotal() {
            let servicesCost = 0;
            const checkboxes = document.querySelectorAll('input[name="services[]"]:checked');
            
            checkboxes.forEach(function(cb) {
                servicesCost += parseFloat(cb.getAttribute('data-price')) || 0;
            });

            const grandTotal = baseRent + servicesCost;

            document.getElementById('services_display_cost').innerText = '₹' + servicesCost.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('grand_total_display').innerText = '₹' + grandTotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
    </script>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Auditorium Booking System</p>
    </footer>

<script src="../assets/js/main.js"></script>
<script>calculateBookingTotal(<?php echo floatval($hall['price']); ?>);</script>
</body>
</html>