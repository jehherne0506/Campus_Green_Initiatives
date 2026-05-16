<?php
    include "../conn.php";
    include("../auth.php");

    if(!isset($_SESSION['role']) || $_SESSION['role'] !== "ADMIN"){
        header("Location: ../login/login.php");
        exit();
    }

    $hoursQuery = "SELECT 
    ep.user_id,
    SUM(TIMESTAMPDIFF(HOUR, e.time_start, e.time_end)) AS total_hours
    FROM event_participants ep
    JOIN events e ON ep.event_id = e.event_id
    WHERE ep.attendance_status = 'PRESENT';";
    $hoursResult = mysqli_query($db_connect, $hoursQuery);
    $totalHours = mysqli_fetch_assoc($hoursResult)['total_hours'] ?? 0;

    $activeEventsQuery = "SELECT COUNT(*) AS active_count FROM events WHERE status = 'APPROVED'";
    $activeEvents = mysqli_fetch_assoc(mysqli_query($db_connect, $activeEventsQuery))['active_count'] ?? 0;

    $pendingEventsQuery = "SELECT COUNT(*) AS pending_count FROM events WHERE status = 'Pending'";
    $pendingEvents = mysqli_fetch_assoc(mysqli_query($db_connect, $pendingEventsQuery))['pending_count'] ?? 0;

    $totalUsersQuery = "SELECT COUNT(*) AS total FROM users WHERE account_status = 'Active'";
    $totalUsers = mysqli_fetch_assoc(mysqli_query($db_connect, $totalUsersQuery))['total'] ?? 0;

    $studentsQuery = "SELECT COUNT(*) AS students FROM users WHERE role = 'STUDENT'";
    $totalStudents = mysqli_fetch_assoc(mysqli_query($db_connect, $studentsQuery))['students'] ?? 0;

    $organizersQuery = "SELECT COUNT(*) AS organizers FROM users WHERE role = 'ORGANIZER'";
    $totalOrganizers = mysqli_fetch_assoc(mysqli_query($db_connect, $organizersQuery))['organizers'] ?? 0;

    $recentEventsQuery = "SELECT event_id, title, event_date, event_image FROM events WHERE status = 'PENDING' ORDER BY event_date DESC LIMIT 3";
    $recentEventsResult = mysqli_query($db_connect, $recentEventsQuery);

    $environment_impact_sql = "SELECT COALESCE(SUM(trees_planted), 0) AS total_trees, COALESCE(SUM(waste_collected), 0) AS total_waste FROM Events";
    $environment_impact_result = mysqli_query($db_connect, $environment_impact_sql);
    if ($environment_impact_result) {
        $row = mysqli_fetch_assoc($environment_impact_result);
        $total_trees = $row['total_trees'];
        $total_waste = $row['total_waste'];
    } else {
        $total_trees = 0;
        $total_waste = 0;
    }

    $target_trees = 1000;
    $target_waste = 5000;

    $tree_score = $total_trees / $target_trees * 50;
    $waste_score = $total_waste / $target_waste * 50;
    $total_impact_score = min(100, round($tree_score + $waste_score, 1));
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
    <link rel="stylesheet" href="dashboard.css">
<body>
    <img src="../assets/images/close.png" alt="Close Icon" class="close-sidebar-btn" onclick="toggleSidebarVisibility()" />
    <aside class="sidebar">
        <div class="top-sidebar">
            <img src="../assets/images/logo.png" alt="EcoRiseAPU Logo"/>
        </div>
        <ul class="nav-links">
            <li class="active_nav"><a href="dashboard.php"><img src="../assets/images/home.png" alt="Home Icon">Dashboard</a></li>
            <li><a href="event.php"><img src="../assets/images/event.png" alt="Event Icon">Events Management</a></li>
            <li><a href="voucher.php"><img src="../assets/images/reward.png" alt="Reward Icon">Voucher Management</a></li>
            <li><a href="user.php"><img src="../assets/images/group.png" alt="Group Icon">User Management</a></li>
            <li><a href="report.php"><img src="../assets/images/report.png" alt="Group Icon">System Report</a></li>
            <li><a href="profile.php"><img src="../assets/images/user.png" alt="User Icon">Profile</a></li>
            <li class="signout-item"><a href="../logout.php"><img src="./../assets/images/logout.png" alt="Sign Out Icon">Sign Out</a></li>
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
                    <strong><?php echo htmlspecialchars($username); ?></strong>
                    <small>ADMIN</small>
                </div>
                <img src="<?php echo htmlspecialchars($avatar); ?>" alt="user icon" class="user-icon" href="profile.php">
            </div>
        </header>

        <section class="hero-banner">
            <div class="hero-content">
                <h1>Welcome back,<br><span class="name-banner"><?php echo htmlspecialchars($username); ?>!</span></h1>
                <p>Review and approve submitted events to ensure they meet campus guidelines. See the latest event requests and manage approvals below.</p>
                <a href="event.php" class="btn-primary">APPROVE EVENTS<span>🡺</span></a>
            </div>
        </section>

        <section class="stats-container">
            <div class="stat-card" style="--card-color: #00FF88;">
                <div class="stat-header">
                    <img src="../assets/images/clock.png" alt="Clock Icon">
                    <p>TOTAL</p>
                </div>
                <h2><?php echo $totalHours; ?></h2>
                <label>VOLUNTEER HOURS</label>
            </div>
            <div class="stat-card" style="--card-color: #60A5FA;">
                <div class="stat-header">
                    <img src="./../assets/images/calendar-check.png" alt="Calendar Icon">
                    <p>TOTAL</p>
                </div>
                <h2><?php echo $activeEvents; ?></h2>
                <label>ACTIVE EVENTS</label>
            </div>
            <div class="stat-card" style="--card-color: #C084FC;">
                <div class="stat-header">
                    <img src="./../assets/images/user_green.png" alt="active-voucher">
                    <p>TOTAL</p>
                </div>
                <h2><?php echo $totalUsers; ?></h2>
                <label>ACTIVE USERS</label>
            </div>
        </section>

        <div class="lower-grid">
            <div class="opportunities-panel">
                <div class="panel-header">
                    <h3>Events Management</h3>
                    <a href="event.php" id="browse-all">BROWSE ALL 🡺</a>
                </div>
                
                <?php
                if(mysqli_num_rows($recentEventsResult) > 0) {
                    // Loop through the database results
                    while($event = mysqli_fetch_assoc($recentEventsResult)) {

                        $dateFormatted = date("Y F d", strtotime($event['event_date']));

                        $imgSrc = $event['event_image'];
                ?>
                        <div class="opp-item">
                            <div class="opp-thumb">
                                <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="event" class="event-img">
                            </div>
                            <div class="opp-info">
                                <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                <p>📅 <?php echo $dateFormatted; ?></p>
                            </div>
                            <a href="event.php" class="btn-outline" style="text-decoration:none;">APPROVE ></a>
                        </div>
                <?php
                    }
                } else {
                    echo "<p style='padding: 15px; color: var(--text-muted);'>No events pending approval. All caught up!</p>";
                }
                ?>
            </div>

            <div class="goal-tracking-panel">
                <div class="badge-icon">🌿</div> 
                <label class="label-light">COMMUNITY IMPACT</label>
                <h2>Eco Achievements</h2>
                
                <p>Through your approved events, the APU community has successfully contributed to a greener campus this semester.</p>
                
                <div class="stats-mini-grid" style="display: flex; justify-content: space-between; margin-top: 15px; color: white;">
                    <div class="mini-stat">
                        <span style="font-size: 0.8rem; opacity: 0.8;">TREES PLANTED</span>
                        <h3 style="margin: 5px 0;"><?php echo $total_trees ?></h3>
                    </div>
                    <div class="mini-stat">
                        <span style="font-size: 0.8rem; opacity: 0.8;">WASTE COLLECTED</span>
                        <h3 style="margin: 5px 0;"><?php echo $total_waste ?> KG</h3>
                    </div>
                </div>

                <div class="progress-section" style="margin-top: 20px;">
                    <div class="progress-labels">
                        <span>SEMESTER GOAL: <?php echo $total_impact_score ?>%</span>
                        <span>TARGET: 100%</span>
                    </div>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: <?php echo $total_impact_score ?>%; background: #00FF88;"></div>
                    </div>
                </div>
                
                <button class="btn-white" onclick="location.href='report.php'" style="margin-top: 20px;">VIEW FULL REPORT</button>
            </div>
        </div>
    </div>
    <script src="header.js"></script>
</body>
</html>