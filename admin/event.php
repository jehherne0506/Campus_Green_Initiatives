<?php
    include "../conn.php";
    include("../auth.php");

    if(!isset($_SESSION['role']) || $_SESSION['role'] !== "ADMIN"){
        header("Location: ../login/login.php");
        exit();
    }

    $query = "SELECT e.*, c.name AS category_name, u.username AS organizer_name 
              FROM events e 
              JOIN categories c ON e.category_id = c.category_id 
              JOIN users u ON e.organizer_id = u.user_id 
              WHERE e.status = 'PENDING' 
              ORDER BY e.event_date ASC";

    $result = mysqli_query($db_connect, $query);

    if (!$result) {
        die("Database Query Failed: " . mysqli_error($db_connect));
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoRiseAPU</title>
    <link rel="icon" href="../assets/images/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="event.css">
</head>
<body>
    <img src="../assets/images/close.png" alt="Close Icon" class="close-sidebar-btn" onclick="toggleSidebarVisibility()" />
    
    <aside class="sidebar">
        <div class="top-sidebar">
            <img src="../assets/images/logo.png" alt="EcoRiseAPU Logo"/>
        </div>
        <ul class="nav-links">
            <li>
                <a href="dashboard.php"><img src="../assets/images/home.png" alt="Home Icon">Dashboard</a>
            </li>

            <li class="active_nav">
                <a href="event.php"><img src="../assets/images/event.png" alt="Event Icon">Events Management</a>
            </li>

            <li>
                <a href="voucher.php"><img src="../assets/images/reward.png" alt="Reward Icon">Voucher Management</a>
            </li>

            <li>
                <a href="user.php"><img src="../assets/images/group.png" alt="Group Icon">User Management</a>
            </li>

            <li>
                <a href="report.php"><img src="../assets/images/report.png" alt="Group Icon">System Report</a>
            </li>

            <li>
                <a href="profile.php"><img src="../assets/images/user.png" alt="User Icon">Profile</a>
            </li>

            <li class="signout-item">
                <a href="../logout.php"><img src="./../assets/images/logout.png" alt="Sign Out Icon">Sign Out</a>
            </li>
        </ul>
    </aside>

    <div class="main-wrapper">
        <header class="top-header">
            <div class="hamburger-menu" onclick="toggleSidebarVisibility()">
                 <img src="./../assets/images/hamburger-menu.png" alt="Hamburger Menu" />
            </div>
            <div class="logo-icon">
                <img src="./../assets/images/logo-black.png" alt="EcoRiseAPU Logo" />
            </div>
            <div class="user-profile">
                <div class="user-text">
                    <strong><?php echo $username ?></strong>
                    <small>ADMIN</small>
                </div>
                <img src="<?php echo $avatar ?>" alt="user icon" class="user-icon" href="profile.php">
            </div>
        </header>

        <div class="page-heading">
            <h1>Event Management</h1>
            <p>Manage and approve events created by students.</p>
        </div>

        <div class="main-content">
            <div class="filter-wrapper">
                <button class="mobile-filter-btn" onclick="toggleMobileFilters()">
                    <span>Filter Events</span>
                    <span id="filter-arrow"><img src="./../assets/images/dropdown.svg" alt="Dropdown Icon" /></span>
                </button>

                <div class="multi-filter-group" id="filterGroup"> 
                    <div class="filter-item">
                        <label for="categoryFilter" class="filter-label">CATEGORY</label>
                        <select id="categoryFilter" class="filter-input" onchange="filterEvents()">
                            <option value="All">All</option>
                        </select>
                    </div>

                    <div class="filter-item">
                        <label for="dateFilter" class="filter-label">MONTH</label>
                        <input type="month" id="dateFilter" class="filter-input" onchange="filterEvents()">
                    </div>

                    <button class="btn-clear-filters" onclick="resetFilter()">Reset</button>
                </div>
            </div>      

        <div class="all-events">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="detailed-event-card" data-id="<?php echo $row['event_id']; ?>" data-category="<?php echo $row["category_name"] ?>" data-location="<?php echo $row["location"] ?>" data-date="<?php echo date('Y-m', strtotime($row['event_date'])); ?>">
                        <img src="<?php echo $row['event_image']; ?>" class="event-hero-img">
                        <div class="event-body">
                            <div class="event-tags">
                                <span class="tag type-tag"><?php echo htmlspecialchars($row['category_name']); ?></span>
                                <span class="tag hours-tag"><?php echo $row['max_participants']; ?> Max Pax</span>
                            </div>
                            <h4><?php echo htmlspecialchars($row['title']); ?></h4>
                            <div class="event-meta">
                                <div class="event-info"><img src="../assets/images/calendar-check.png"><p><?php echo $row['event_date']; ?></p></div>
                                <div class="event-info"><img src="../assets/images/location.png"><p><?php echo htmlspecialchars($row['location']); ?></p></div>
                                <div class="event-info"><img src="../assets/images/host.png"><p><?php echo htmlspecialchars($row['organizer_name']); ?></p></div>
                                <div class="event-info"><img src="../assets/images/clock.png"><p><?php echo $row['time_start'] . " - " . $row['time_end']; ?></p></div>
                            </div>
                            <div class="event-actions">
                                <button class="btn-primary" onclick="updateEventStatus(<?php echo $row['event_id']; ?>, 'REJECTED')">REJECT <span>🡺</span></button>
                                <button class="btn-primary2" onclick="updateEventStatus(<?php echo $row['event_id']; ?>, 'APPROVED')">APPROVE <span>🡺</span></button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="grid-column: 1/-1; text-align: center; padding: 50px;">No pending events found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="header.js"></script>
<script src="event.js"></script>
</body>
</html>