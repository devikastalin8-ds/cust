<?php
// Main Home Portal
include 'db.php';

$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

$sql = "SELECT * FROM auditoriums WHERE status='Available'";
if ($search != "") {
    $sql .= " AND (auditorium_name LIKE '%$search%' OR location LIKE '%$search%')";
}
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Auditorium Booking System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Header Navigation -->
    <header>
        <div class="nav-container">
            <div class="logo">
                <a href="index.php">🎭 Auditorium Booking</a>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php" class="active">Home</a></li>
                    <li><a href="customer/search_auditorium.php">Browse Auditoriums</a></li>
                    <li><a href="customer/login.php">Customer Login</a></li>
                    <li><a href="owner/login.php">Owner Login</a></li>
                    <li><a href="admin/login.php">Admin Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">

        <!-- Hero Search Section with Integrated Filter Button -->
        <div class="hero">
            <h1>Find & Book Your Perfect Auditorium</h1>
            <p>Easily reserve top-rated auditoriums, convention centers, and banquet halls for weddings, corporate events, concerts, and cultural gatherings.</p>
            
            <div class="search-bar-wrapper">
                <form method="GET" action="customer/search_auditorium.php" id="heroSearchForm">
                    <div class="search-box-pill">
                        
                        <!-- Search Input -->
                        <input type="text" name="search" placeholder="Search by auditorium name or location (e.g. Bangalore, MG Road)..." value="<?php echo htmlspecialchars($search); ?>">
                        
                        <!-- Filter Button Beside Search Bar -->
                        <button type="button" class="filter-toggle-btn" id="filterToggleBtn" onclick="toggleFilterDropdown()">
                            🎛️ Filters
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
                                    <option value="">All Catering & Packages</option>
                                    <option value="Catering">Buffet & Catering Available</option>
                                    <option value="Standard Buffet">Standard Buffet Package</option>
                                    <option value="Deluxe Royal Buffet">Deluxe Royal Buffet</option>
                                </select>
                            </div>

                            <!-- Event & Stage Services Category -->
                            <div class="popover-group">
                                <label for="event_service_filter">✨ Event & Stage Services</label>
                                <select id="event_service_filter" name="event_service">
                                    <option value="">All Event Services</option>
                                    <option value="Decoration">🌸 Elegant Stage & Floral Decoration</option>
                                    <option value="DJ/Sound">🎵 Event DJ & Sound Package</option>
                                    <option value="Photography">📸 Complete Event Coverage (Photo & Video)</option>
                                    <option value="Security">👮 Valet Parking & Security Support</option>
                                    <option value="Event Support">🤝 Event Support Package</option>
                                </select>
                            </div>

                            <!-- Estimated Guest Count / Capacity -->
                            <div class="popover-group">
                                <label for="min_capacity">👥 Estimated Guest Count</label>
                                <select id="min_capacity" name="min_capacity">
                                    <option value="">Any Guest Count</option>
                                    <option value="100">100+ Guests</option>
                                    <option value="250">250+ Guests</option>
                                    <option value="500">500+ Guests</option>
                                    <option value="1000">1000+ Guests</option>
                                    <option value="2000">2000+ Guests</option>
                                </select>
                            </div>

                            <!-- Price Range Per Day -->
                            <div class="popover-group">
                                <label>💰 Rent Price Range Per Day (₹)</label>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <input type="number" name="min_price" placeholder="Min ₹" step="500">
                                    <span style="color: #94a3b8;">-</span>
                                    <input type="number" name="max_price" placeholder="Max ₹" step="500">
                                </div>
                            </div>

                            <!-- Hall Facilities -->
                            <div class="popover-group">
                                <label for="facility">⚡ Hall Facilities</label>
                                <select id="facility" name="facility">
                                    <option value="">All Facilities</option>
                                    <option value="AC">Air Conditioned (AC)</option>
                                    <option value="Sound">Sound System</option>
                                    <option value="Stage">Stage Setup</option>
                                    <option value="Parking">Parking Space</option>
                                    <option value="Lighting">Lighting System</option>
                                </select>
                            </div>

                            <!-- Sort Results By -->
                            <div class="popover-group">
                                <label for="sort">🏷️ Sort Results By</label>
                                <select id="sort" name="sort">
                                    <option value="default">Newest First</option>
                                    <option value="price_asc">Price: Low to High</option>
                                    <option value="price_desc">Price: High to Low</option>
                                    <option value="capacity_desc">Guest Capacity: Largest First</option>
                                    <option value="name_asc">Name: A to Z</option>
                                </select>
                            </div>

                        </div>

                        <div class="filter-popover-footer">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="resetFiltersInPopover('heroSearchForm')">Clear</button>
                            <button type="submit" class="btn btn-primary btn-sm">Apply & Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- 3-Tier Portal Access Cards -->
        <h2 class="section-title">✨ Select Your Portal</h2>
        <div class="grid-3">
            
            <!-- Customer Card -->
            <div class="card">
                <div class="card-body" style="text-align:center; padding: 30px;">
                    <div style="font-size: 40px; margin-bottom: 10px;">👥</div>
                    <h3 class="card-title">Customer Portal</h3>
                    <p style="color: #64748b; font-size: 14px; margin-bottom: 20px;">Search available auditoriums, check availability, book for your event date, and download tickets.</p>
                    <a href="customer/login.php" class="btn btn-primary" style="margin-right: 8px;">Customer Login</a>
                    <a href="customer/register.php" class="btn btn-secondary">Sign Up</a>
                </div>
            </div>

            <!-- Owner Card -->
            <div class="card">
                <div class="card-body" style="text-align:center; padding: 30px;">
                    <div style="font-size: 40px; margin-bottom: 10px;">🏢</div>
                    <h3 class="card-title">Auditorium Owner</h3>
                    <p style="color: #64748b; font-size: 14px; margin-bottom: 20px;">List your auditoriums, manage hall details, view incoming customer bookings, and confirm reservations.</p>
                    <a href="owner/login.php" class="btn btn-success" style="margin-right: 8px;">Owner Login</a>
                    <a href="owner/register.php" class="btn btn-secondary">Register Hall</a>
                </div>
            </div>

            <!-- Admin Card -->
            <div class="card">
                <div class="card-body" style="text-align:center; padding: 30px;">
                    <div style="font-size: 40px; margin-bottom: 10px;">🛡️</div>
                    <h3 class="card-title">Admin Portal</h3>
                    <p style="color: #64748b; font-size: 14px; margin-bottom: 20px;">Manage registered customers, auditorium owners, view all bookings, and monitor platform performance.</p>
                    <a href="admin/login.php" class="btn btn-primary btn-block">Admin Login</a>
                </div>
            </div>

        </div>

        <!-- Featured Auditoriums -->
        <h2 class="section-title">🏛️ Featured Auditoriums</h2>
        <div class="grid-3">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Fallback placeholder images
                    $img_src = "https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=600&q=80";
                    if ($row['auditorium_id'] == 1) $img_src = "https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=600&q=80";
                    if ($row['auditorium_id'] == 2) $img_src = "https://images.unsplash.com/photo-1587825140708-dfaf72ae4b04?auto=format&fit=crop&w=600&q=80";
                    if ($row['auditorium_id'] == 3) $img_src = "https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=600&q=80";
                    if ($row['auditorium_id'] == 4) $img_src = "https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=600&q=80";
                    ?>
                    <div class="card">
                        <img src="<?php echo $img_src; ?>" alt="Auditorium" class="card-img">
                        <div class="card-body">
                            <h3 class="card-title"><?php echo htmlspecialchars($row['auditorium_name']); ?></h3>
                            <p class="card-info">📍 <?php echo htmlspecialchars($row['location']); ?></p>
                            <p class="card-info">👥 Capacity: <?php echo htmlspecialchars($row['capacity']); ?> Seats</p>
                            <p class="card-price">₹<?php echo number_format($row['price'], 2); ?> <span style="font-size: 13px; color: #64748b; font-weight: normal;">/ day</span></p>
                            <div style="display: flex; gap: 8px;">
                                <a href="customer/auditorium_details.php?id=<?php echo $row['auditorium_id']; ?>" class="btn btn-secondary btn-sm" style="flex:1;">View Details</a>
                                <a href="customer/book_auditorium.php?id=<?php echo $row['auditorium_id']; ?>" class="btn btn-primary btn-sm" style="flex:1;">Book Now</a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No auditoriums found.</p>";
            }
            ?>
        </div>

    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Auditorium Booking System | Developed for College Mini Project</p>
    </footer>

<script src="assets/js/main.js"></script>
</body>
</html>