<?php
    include "../conn.php";
    include("../auth.php");

    if(!isset($_SESSION['role']) || $_SESSION['role'] !== "ORGANIZER"){
        header("Location: ../login/login.php");
        exit();
    }

    $events_data = [];
    $user_id = $_SESSION['user_id']; 

    try {
        $sql = "SELECT * FROM events WHERE organizer_id = ? ORDER BY event_date DESC";
        $stmt = mysqli_prepare($db_connect, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if(!$result){
            throw new Exception("Fail to fetch organizer events");
        }

        while($row = mysqli_fetch_assoc($result)){
            $events_data[] = $row;
        }

        // Summary Calculations for Stats Cards
        $total_active = 0;
        $total_trees = 0;
        $total_waste = 0;

        foreach($events_data as $e) {
            if($e['status'] === 'APPROVED') $total_active++; 
            $total_trees += $e['trees_planted'];
            $total_waste += $e['waste_collected'];
        }

    } catch(Exception $e) {
        error_log("Organizer failed to fetch events: " . $e->getMessage());
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
    <link rel="stylesheet" href="dashboard.css">
<body>
    <img src="../assets/images/close.png" alt="Close Icon" class="close-sidebar-btn" onclick="toggleSidebarVisibility()" />
    <aside class="sidebar">
        <div class="top-sidebar">
            <img src="../assets/images/logo.png" alt="EcoRiseAPU Logo"/>
        </div>
        <ul class="nav-links">
            <li class="active_nav">
                <a href="dashboard.php"><img src="../assets/images/home.png" alt="Home Icon">Dashboard</a>
            </li>

            <li>
                <a href="events.php"><img src="../assets/images/event.png" alt="Calendar Icon">Event Management</a>
            </li>

            <li>
                <a href="attendance.php"><img src="../assets/images/group.png" alt="Participants Icon">Attendance Approval</a>
            </li>

            <li>
                <a href="report.php"><img src="../assets/images/business-report.png" alt="Report Icon">Impact Report</a>
            </li>

            <li>
                <a href="profile.php"><img src="../assets/images/user.png" alt="User Icon">Profile</a>
            </li>
            <li class="signout-item">
                <a href="../logout.php"><img src="../assets/images/logout.png" alt="Sign Out Icon">Sign Out</a>
            </li>
        </ul>
    </aside>

    <div class="main-wrapper">
        <header class="top-header">
            <div class="hamburger-menu" onclick="toggleSidebarVisibility()">
                 <img src="../assets/images/hamburger-menu.png" alt="Hamburger Menu" />
            </div>
            <div class="logo-icon">
                <img src="../assets/images/logo-black.png" alt="EcoRiseAPU Logo" />
            </div>
            <div class="user-profile">
                <div class="user-text">
                    <strong><?php echo htmlspecialchars($username) ?></strong>
                    <small>ORGANIZER</small>
                </div>
                <img src="<?php echo htmlspecialchars($avatar) ?>" alt="user icon" class="user-icon" href="profile.php">
            </div>
        </header>

        <section class="hero-banner">
            <div class="hero-content">
                <h1>Welcome back,<br><span class="name-banner"><?php echo htmlspecialchars($username) ?>!</span></h1>
                <p>You're making a real difference. Check out your impact summary below and see what new initiatives are happening this week.</p>
                <a href="events.php" class="btn-primary">CREATE NEW EVENTS <span>🡺</span></a>
            </div>
        </section>

        <section class="stats-container">
            <div class="stat-card" style="--card-color: #00FF88;">
                <div class="stat-header">
                    <img src="../assets/images/calendar-check.png" alt="Calendar Icon">
                    <p>MY EVENTS</p>
                </div>
                <h2><?php echo count($events_data); ?></h2>
                <label>TOTAL CREATED</label>
            </div>
            <div class="stat-card" style="--card-color: #60A5FA;">
                <div class="stat-header">
                    <img src="../assets/images/tree.png" alt="Tree Icon">
                    <p>PLANTED</p>
                </div>
                <h2><?php echo number_format($total_trees); ?></h2>
                <label>TREES TOTAL</label>
            </div>
            <div class="stat-card" style="--card-color: #C084FC;">
                <div class="stat-header">
                    <img src="../assets/images/bin.png" alt="Bin Icon">
                    <p>CLEANUP</p>
                </div>
                <h2><?php echo number_format($total_waste, 1); ?> kg</h2>
                <label>WASTE COLLECTED</label>
            </div>
        </section>

        <div class="events-panel">
            <div class="panel-header">
                <h3>Recent Events</h3>
                <a href="events.php" id="browse-all">Manage All 🡺</a>
            </div>
            
            <?php 
            $found_approved = false;
            $count = 0;

            if (!empty($events_data)): 
                foreach ($events_data as $row): 
                    // ONLY SHOW APPROVED EVENTS (based on previous prompt)
                    if (strtolower($row['status']) === 'approved'): 
                        $found_approved = true;
                        $count++;
                        
                        // Limit to the most recent 3 approved events
                        if($count > 3) break; 

                        // --- NEW IMAGE LOGIC ---
                        // Set the path to your default fallback image
                        $default_placeholder = "../assets/uploads/default-event.png"; 

                        // Check if event_image is not empty in DB AND the file actually exists on the server
                        if (!empty($row['event_image']) && file_exists($row['event_image'])) {
                            // Use the image from the database
                            $event_image_src = htmlspecialchars($row['event_image']);
                        } else {
                            // Use the default placeholder image
                            $event_image_src = $default_placeholder;
                        }
            ?>
                <div class="events-item">
                    <div class="events-thumb">
                        <img src="<?php echo $event_image_src; ?>" alt="thumbnail for <?php echo htmlspecialchars($row['title']); ?>" class="event-img">
                    </div>
                    <div class="events-info">
                        <h4><?php echo htmlspecialchars($row['title']); ?></h4>
                        <h5><?php echo htmlspecialchars(substr($row['description'], 0, 60)) . '...'; ?></h5>
                        <p>📅 <?php echo date('Y M d', strtotime($row['event_date'])); ?></p> 
                        <p>📍 <?php echo htmlspecialchars($row['location']); ?></p>
                        <p>👥 Quota <?php echo htmlspecialchars($row['max_participants']); ?></p>
                    </div>
                    <a href="events.php" class="btn-outline">View ></a>
                </div>
            <?php 
                    endif; 
                endforeach; 
            endif; 

            // If no approved events found, display a message
            if (!$found_approved): 
            ?>
                <p style="padding: 20px;">No approved events to display.</p>
            <?php endif; ?>
        </div>
            <br>
            <div class="page-frame">
                <div class="stats-card">
                    <h2 class="stats-title">Detailed Statistics</h2>
                    <div class="stats-grid-container">
                        <div class="stats-header">
                            <div>EVENT</div>
                            <div>MAX PARTICIPANTS</div>
                            <div>TREES</div>
                            <div>WASTE</div>
                        </div>

                        <?php 
                        $found_completed_rows = false;
                        foreach ($events_data as $row): 
                            // ONLY SHOW COMPLETED EVENTS
                            if (isset($row['status']) && strtolower($row['status']) === 'completed'): 
                                $found_completed_rows = true;
                        ?>
                        <div class="events-item stats-row">
                            <div class="events-info">
                                <h4 class="event-display-name"><?php echo htmlspecialchars($row['title']); ?></h4>
                            </div>
                            <div class="stat-value" data-label="PARTICIPANTS"><?php echo $row['max_participants']; ?></div>
                            <div class="stat-value green" data-label="TREES"><?php echo $row['trees_planted']; ?></div>
                            <div class="stat-value blue" data-label="WASTE"><?php echo $row['waste_collected'] ?? 0 ?> kg</div>
                        </div>
                        <?php 
                            endif;
                        endforeach; 

                        if (!$found_completed_rows):
                            echo '<p style="text-align:center; padding: 20px; color: #6c757d;">No completed events available yet.</p>';
                        endif;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="header.js"></script>
</body>
</html>