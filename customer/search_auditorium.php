<?php
session_start();
include '../db.php';

// Filter Parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$min_price = (isset($_GET['min_price']) && $_GET['min_price'] !== '') ? floatval($_GET['min_price']) : '';
$max_price = (isset($_GET['max_price']) && $_GET['max_price'] !== '') ? floatval($_GET['max_price']) : '';
$min_capacity = (isset($_GET['min_capacity']) && $_GET['min_capacity'] !== '') ? intval($_GET['min_capacity']) : '';
$facility = isset($_GET['facility']) ? trim($_GET['facility']) : '';
$service_category = isset($_GET['service_category']) ? trim($_GET['service_category']) : '';
$event_service = isset($_GET['event_service']) ? trim($_GET['event_service']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'default';

// Construct Dynamic SQL Query
$sql = "SELECT DISTINCT a.* FROM auditoriums a";
if ($service_category !== '' || $event_service !== '') {
    $sql .= " LEFT JOIN services s ON a.auditorium_id = s.auditorium_id";
}
$sql .= " WHERE a.status='Available'";

if ($search !== '') {
    $search_clean = $conn->real_escape_string($search);
    $sql .= " AND (a.auditorium_name LIKE '%$search_clean%' OR a.location LIKE '%$search_clean%' OR a.description LIKE '%$search_clean%')";
}
if ($min_price !== '') {
    $sql .= " AND a.price >= $min_price";
}
if ($max_price !== '') {
    $sql .= " AND a.price <= $max_price";
}
if ($min_capacity !== '') {
    $sql .= " AND a.capacity >= $min_capacity";
}
if ($facility !== '') {
    $facility_clean = $conn->real_escape_string($facility);
    $sql .= " AND a.facilities LIKE '%$facility_clean%'";
}
if ($service_category !== '') {
    $cat_clean = $conn->real_escape_string($service_category);
    $sql .= " AND (s.service_category LIKE '%$cat_clean%' OR s.service_name LIKE '%$cat_clean%')";
}
if ($event_service !== '') {
    $event_clean = $conn->real_escape_string($event_service);
    $sql .= " AND (s.service_category LIKE '%$event_clean%' OR s.service_name LIKE '%$event_clean%')";
}

switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY a.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY a.price DESC";
        break;
    case 'capacity_desc':
        $sql .= " ORDER BY a.capacity DESC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY a.auditorium_name ASC";
        break;
    default:
        $sql .= " ORDER BY a.auditorium_id DESC";
        break;
}

$result = $conn->query($sql);
$has_active_filters = ($search !== '' || $min_price !== '' || $max_price !== '' || $min_capacity !== '' || $facility !== '' || $service_category !== '' || $event_service !== '' || $sort !== 'default');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search & Filter Auditoriums | Auditorium Booking System</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <!-- Header Navigation -->
    <header>
        <div class="nav-container">
            <div class="logo"><a href="../index.php">🎭 Auditorium Booking</a></div>
            <nav>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="search_auditorium.php" class="active">Search Auditoriums</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="index.php">Dashboard</a></li>
                        <li><a href="my_bookings.php">My Bookings</a></li>
                        <li><a href="notifications.php">🔔 Notifications</a></li>
                        <li><a href="logout.php" style="color: #fca5a5;">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Customer Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">

        <!-- Top Search & Filter Banner -->
        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); padding: 35px 25px; border-radius: 18px; margin-bottom: 35px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); text-align: center;">
            <h1 style="font-size: 28px; color: #ffffff; margin-bottom: 6px;">Find & Book Auditoriums</h1>
            <p style="color: #cbd5e1; font-size: 15px; margin-bottom: 22px;">Search venues by name or location, or click Filters for advanced options</p>

            <div class="search-bar-wrapper">
                <form method="GET" action="search_auditorium.php" id="searchAuditoriumForm">
                    <div class="search-box-pill">
                        
                        <!-- Search Input -->
                        <input type="text" name="search" placeholder="Search by auditorium name or location (e.g. Bangalore, MG Road)..." value="<?php echo htmlspecialchars($search); ?>">
                        
                        <!-- Filter Button Beside Search Bar -->
                        <button type="button" class="filter-toggle-btn <?php echo $has_active_filters ? 'active' : ''; ?>" id="filterToggleBtn" onclick="toggleFilterDropdown()">
                            🎛️ Filters <?php if ($has_active_filters): ?><span class="filter-badge-dot"></span><?php endif; ?>
                        </button>

                        <!-- Search Button -->
                        <button type="submit" class="search-submit-btn">
                            Search Hall
                        </button>
                    </div>

                    <!-- Dropdown Filter Popover Window -->
                    <div class="filter-dropdown-popover" id="filterDropdown">
                        <div class="filter-popover-header">
                            <h4>🎛️ Filter Auditoriums</h4>
                            <span class="close-filter" onclick="toggleFilterDropdown()">&times;</span>
                        </div>

                        <div class="filter-popover-grid">
                            
                            <!-- Food & Catering Packages Category -->
                            <div class="popover-group">
                                <label for="catering_filter">🍽️ Food & Catering Packages</label>
                                <select id="catering_filter" name="service_category">
                                    <option value="">All Catering Packages</option>
                                    <option value="Catering" <?php echo ($service_category == 'Catering') ? 'selected' : ''; ?>>Buffet & Catering Available</option>
                                    <option value="Standard Buffet" <?php echo ($service_category == 'Standard Buffet') ? 'selected' : ''; ?>>Standard Buffet Package</option>
                                    <option value="Deluxe Royal Buffet" <?php echo ($service_category == 'Deluxe Royal Buffet') ? 'selected' : ''; ?>>Deluxe Royal Buffet</option>
                                </select>
                            </div>

                            <!-- Event & Stage Services Category -->
                            <div class="popover-group">
                                <label for="event_service_filter">✨ Event & Stage Services</label>
                                <select id="event_service_filter" name="event_service">
                                    <option value="">All Event Services</option>
                                    <option value="Decoration" <?php echo ($event_service == 'Decoration') ? 'selected' : ''; ?>>🌸 Elegant Stage & Floral Decoration</option>
                                    <option value="DJ/Sound" <?php echo ($event_service == 'DJ/Sound') ? 'selected' : ''; ?>>🎵 Event DJ & Sound Package</option>
                                    <option value="Photography" <?php echo ($event_service == 'Photography') ? 'selected' : ''; ?>>📸 Complete Event Coverage (Photo & Video)</option>
                                    <option value="Security" <?php echo ($event_service == 'Security') ? 'selected' : ''; ?>>👮 Valet Parking & Security Support</option>
                                    <option value="Event Support" <?php echo ($event_service == 'Event Support') ? 'selected' : ''; ?>>🤝 Event Support Package</option>
                                </select>
                            </div>

                            <!-- Estimated Guest Count / Capacity -->
                            <div class="popover-group">
                                <label for="min_capacity">👥 Estimated Guest Count</label>
                                <select id="min_capacity" name="min_capacity">
                                    <option value="">Any Guest Count</option>
                                    <option value="100" <?php echo ($min_capacity == 100) ? 'selected' : ''; ?>>100+ Guests</option>
                                    <option value="250" <?php echo ($min_capacity == 250) ? 'selected' : ''; ?>>250+ Guests</option>
                                    <option value="500" <?php echo ($min_capacity == 500) ? 'selected' : ''; ?>>500+ Guests</option>
                                    <option value="1000" <?php echo ($min_capacity == 1000) ? 'selected' : ''; ?>>1000+ Guests</option>
                                    <option value="2000" <?php echo ($min_capacity == 2000) ? 'selected' : ''; ?>>2000+ Guests</option>
                                </select>
                            </div>

                            <!-- Price Range Per Day -->
                            <div class="popover-group">
                                <label>💰 Rent Price Range Per Day (₹)</label>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <input type="number" name="min_price" placeholder="Min ₹" value="<?php echo ($min_price !== '') ? htmlspecialchars($min_price) : ''; ?>" step="500">
                                    <span style="color: #94a3b8;">-</span>
                                    <input type="number" name="max_price" placeholder="Max ₹" value="<?php echo ($max_price !== '') ? htmlspecialchars($max_price) : ''; ?>" step="500">
                                </div>
                            </div>

                            <!-- Hall Facilities -->
                            <div class="popover-group">
                                <label for="facility">⚡ Hall Facilities</label>
                                <select id="facility" name="facility">
                                    <option value="">All Facilities</option>
                                    <option value="AC" <?php echo ($facility == 'AC') ? 'selected' : ''; ?>>Air Conditioned (AC)</option>
                                    <option value="Sound" <?php echo ($facility == 'Sound') ? 'selected' : ''; ?>>Sound System</option>
                                    <option value="Stage" <?php echo ($facility == 'Stage') ? 'selected' : ''; ?>>Stage Setup</option>
                                    <option value="Parking" <?php echo ($facility == 'Parking') ? 'selected' : ''; ?>>Parking Space</option>
                                    <option value="Lighting" <?php echo ($facility == 'Lighting') ? 'selected' : ''; ?>>Lighting System</option>
                                </select>
                            </div>

                            <!-- Sort Results By -->
                            <div class="popover-group">
                                <label for="sort">🏷️ Sort Results By</label>
                                <select id="sort" name="sort">
                                    <option value="default" <?php echo ($sort == 'default') ? 'selected' : ''; ?>>Newest First</option>
                                    <option value="price_asc" <?php echo ($sort == 'price_asc') ? 'selected' : ''; ?>>Price: Low to High</option>
                                    <option value="price_desc" <?php echo ($sort == 'price_desc') ? 'selected' : ''; ?>>Price: High to Low</option>
                                    <option value="capacity_desc" <?php echo ($sort == 'capacity_desc') ? 'selected' : ''; ?>>Guest Capacity: Largest First</option>
                                    <option value="name_asc" <?php echo ($sort == 'name_asc') ? 'selected' : ''; ?>>Name: A to Z</option>
                                </select>
                            </div>

                        </div>

                        <div class="filter-popover-footer">
                            <a href="search_auditorium.php" class="btn btn-secondary btn-sm" style="text-decoration:none;">Clear All</a>
                            <button type="submit" class="btn btn-primary btn-sm">Apply & Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main Results Grid Area -->
        <main>
            <!-- Active Filters Badges Bar -->
            <?php if ($has_active_filters): ?>
                <div class="active-filters" style="justify-content: space-between;">
                    <div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
                        <span style="font-size: 14px; font-weight: 600; color: #475569;">Active Filters:</span>
                        <?php if ($search !== ''): ?>
                            <span class="filter-tag">Keyword: "<?php echo htmlspecialchars($search); ?>"</span>
                        <?php endif; ?>
                        <?php if ($min_capacity !== ''): ?>
                            <span class="filter-tag">Capacity: <?php echo htmlspecialchars($min_capacity); ?>+ Guests</span>
                        <?php endif; ?>
                        <?php if ($min_price !== '' || $max_price !== ''): ?>
                            <span class="filter-tag">Price: ₹<?php echo $min_price ? $min_price : '0'; ?> - ₹<?php echo $max_price ? $max_price : 'Any'; ?></span>
                        <?php endif; ?>
                        <?php if ($service_category !== ''): ?>
                            <span class="filter-tag">Catering: "<?php echo htmlspecialchars($service_category); ?>"</span>
                        <?php endif; ?>
                        <?php if ($event_service !== ''): ?>
                            <span class="filter-tag">Event Service: "<?php echo htmlspecialchars($event_service); ?>"</span>
                        <?php endif; ?>
                        <?php if ($facility !== ''): ?>
                            <span class="filter-tag">Facility: <?php echo htmlspecialchars($facility); ?></span>
                        <?php endif; ?>
                        <?php if ($sort !== 'default'): ?>
                            <span class="filter-tag">Sort: <?php echo htmlspecialchars($sort); ?></span>
                        <?php endif; ?>
                    </div>
                    <a href="search_auditorium.php" class="btn btn-secondary btn-sm">Reset Filters</a>
                </div>
            <?php endif; ?>

            <!-- Auditorium Grid -->
            <div class="grid-3">
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Default images
                        $img_src = "https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=600&q=80";
                        if ($row['auditorium_id'] == 1) $img_src = "https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=600&q=80";
                        if ($row['auditorium_id'] == 2) $img_src = "https://images.unsplash.com/photo-1587825140708-dfaf72ae4b04?auto=format&fit=crop&w=600&q=80";
                        if ($row['auditorium_id'] == 3) $img_src = "https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=600&q=80";
                        if ($row['auditorium_id'] == 4) $img_src = "https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=600&q=80";
                        ?>
                        <div class="card">
                            <img src="<?php echo $img_src; ?>" alt="<?php echo htmlspecialchars($row['auditorium_name']); ?>" class="card-img">
                            <div class="card-body">
                                <h3 class="card-title"><?php echo htmlspecialchars($row['auditorium_name']); ?></h3>
                                <p class="card-info">📍 <?php echo htmlspecialchars($row['location']); ?></p>
                                <p class="card-info">👥 Capacity: <?php echo htmlspecialchars($row['capacity']); ?> Guests</p>
                                <p class="card-info">⚡ Facilities: <?php echo htmlspecialchars(substr($row['facilities'], 0, 42)); ?>...</p>
                                
                                <div class="card-price">
                                    ₹<?php echo number_format($row['price'], 2); ?> 
                                    <span style="font-size: 13px; color: #64748b; font-weight: normal;">/ day</span>
                                </div>
                                
                                <div style="display: flex; gap: 8px; margin-top: 15px;">
                                    <a href="auditorium_details.php?id=<?php echo $row['auditorium_id']; ?>" class="btn btn-secondary btn-sm" style="flex:1; text-align:center;">View Details</a>
                                    <a href="book_auditorium.php?id=<?php echo $row['auditorium_id']; ?>" class="btn btn-primary btn-sm" style="flex:1; text-align:center;">Book Hall</a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<div style='text-align:center; padding: 50px 20px; background:white; border-radius:14px; border:1px solid #e2e8f0; grid-column: 1/-1;'>";
                    echo "<h3 style='color:#0f172a; font-size: 20px;'>No auditoriums match your filter criteria.</h3>";
                    echo "<p style='color:#64748b; margin-top:8px;'>Try broadening your search or resetting the active filters.</p>";
                    echo "<a href='search_auditorium.php' class='btn btn-primary' style='margin-top:18px;'>Reset Filters</a>";
                    echo "</div>";
                }
                ?>
            </div>
        </main>

    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Auditorium Booking System</p>
    </footer>

    <script src="../assets/js/main.js"></script>
</body>
</html>
PHP;
file_put_contents("C:/xampp/htdocs/OnlineAuditoriumBooking/customer/search_auditorium.php", $search_content);
?>