<?php
    include "../conn.php";
    include("../auth.php");

    if(!isset($_SESSION['role']) || $_SESSION['role'] !== "ADMIN"){
        header("Location: ../login/login.php");
        exit();
    }

    $user_count_sql = "SELECT COUNT(*) AS count FROM Users WHERE account_status = 'ACTIVE'";
    $user_result = mysqli_query($db_connect, $user_count_sql);
    if ($user_result) {
        $row = mysqli_fetch_assoc($user_result);
        $user_count = $row['count']; 
    } else {
        $user_count = 0; 
    }

    $event_count_sql = "SELECT COUNT(*) AS count FROM Events WHERE status != 'REJECTED'";
    $event_result = mysqli_query($db_connect, $event_count_sql);
    if ($event_result) {
        $row = mysqli_fetch_assoc($event_result);
        $event_count = $row['count']; 
    } else {
        $event_count = 0; 
    }

    $voucher_count_sql = "SELECT COUNT(*) AS count, SUM(v.points_cost) AS points FROM Voucher_Claims vc JOIN Vouchers v ON vc.voucher_id = v.voucher_id WHERE vc.expires_at >= CURDATE()";
    $voucher_result = mysqli_query($db_connect, $voucher_count_sql);
    if ($voucher_result) {
        $row = mysqli_fetch_assoc($voucher_result);
        $voucher_count = $row['count']; 
        $voucher_value = $row['points'];
    } else {
        $voucher_count = 0; 
    }

    $category_count_sql = "SELECT 
        c.name, 
        COUNT(ep.user_id) AS count
        FROM Categories c
        LEFT JOIN Events e ON c.category_id = e.category_id
        LEFT JOIN Event_Participants ep ON e.event_id = ep.event_id
        WHERE e.status = 'APPROVED' OR e.status = 'COMPLETED'
        GROUP BY c.category_id, c.name HAVING count > 0;
    ";
    $category_result = mysqli_query($db_connect, $category_count_sql);
    $category_data = [];

    while($row = mysqli_fetch_assoc($category_result)) {
        $category_data[] = $row;
    }

    $organizer_event_sql = "SELECT u.username, COUNT(*) AS count FROM Events as e JOIN Users u ON e.organizer_id = u.user_id WHERE e.status = 'APPROVED' OR e.status = 'COMPLETED' GROUP BY organizer_id";
    $organizer_event_result = mysqli_query($db_connect, $organizer_event_sql);
    $organizer_event_data = [];

    while($row = mysqli_fetch_assoc($organizer_event_result)){
        $organizer_event_data[] = $row;
    }

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
    <link rel="stylesheet" href="report.css">
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

            <li>
                <a href="event.php"><img src="../assets/images/event.png" alt="Event Icon">Events Management</a>
            </li>

            <li>
                <a href="voucher.php"><img src="../assets/images/reward.png" alt="Reward Icon">Voucher Management</a>
            </li>

            <li>
                <a href="user.php"><img src="../assets/images/group.png" alt="Group Icon">User Management</a>
            </li>

            <li class="active_nav">
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

        <header class="main-header">
            <div class="page-heading">
                <h1>System Report</h1>
                <p>View and analyse the impact everybody made to the environment.</p>
            </div>
        </header>

        <div class="stat-grid">
                <div class="metric-card">
                    <div class="card-header">
                        <div class="icon-box">
                            <img src="../assets/images/host.png" alt="host icon" class="card-icon">
                        </div>
                    </div>
                    <h3>TOTAL ACTIVE USERS</h3>
                    <div class="value"><?php echo $user_count ?></div>
                    <p>Students Registered</p>
                </div>

                <div class="metric-card">
                    <div class="card-header">
                        <div class="icon-box">
                            <img src="../assets/images/timetable.png" alt="timetable icon" class="card-icon">
                        </div>
                    </div>
                    <h3>TOTAL EVENTS HOSTED</h3>
                    <div class="value"><?php echo $event_count ?></div>
                    <p>Events Created</p>
                </div>

                <div class="metric-card">
                    <div class="card-header">
                        <div class="icon-box"> <img src="../assets/images/voucher.png" alt="voucher icon" class="card-icon">
                        </div>
                    </div>
                    <h3>TOTAL VOUCHERS CLAIMED</h3>
                    <div class="value"><?php echo $voucher_count ?></div>
                    <p>Vouchers Redeemed</p>
                </div>
            </div>

        <main class="dashboard-grid">
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Participation By Category</h3>
                </div>
                <div class="bar-chart-mockup">
                    <?php foreach($category_data as $row){ ?>
                        <div class="bar-group">
                            <div class="bar bar-green" data-value="<?php echo $row['count'] ?>">
                                <span class="tooltip"><?php echo $row["name"] ?> <br> value : <?php echo $row["count"] ?></span>
                            </div>
                            <span class="bar-label"><?php echo $row["name"] ?></span>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-header">
                    <h3>Event By Organizer</h3>
                </div>
                <div class="bar-chart-mockup">
                    <?php foreach($organizer_event_data as $row){ ?>
                        <div class="bar-group">
                            <div class="bar bar-green" data-value="<?php echo $row['count'] ?>">
                                <span class="tooltip"><?php echo $row["username"] ?> <br> value : <?php echo $row["count"] ?></span>
                            </div>
                            <span class="bar-label"><?php echo $row["username"] ?></span>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </main>

        <footer class="status-banner">
            <div class="status-container">
                <div class="banner-header">
                    <h2>Voucher Distribution Status</h2>
                    <p>Monitor integrity and participation certificates across the platform.</p>
                </div>
                <div class="metrics-grid">
                    <div class="metric">
                        <span class="metric-label">Issued</span>
                        <span class="metric-value"><?php echo $voucher_count ?></span>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Total Points</span>
                        <span class="metric-value"><?php echo $voucher_value ?></span>
                    </div>
                </div>
            </div>

            <div class="status-container">
                <div class="banner-header">
                    <h2>Overall Environment Impact</h2>
                    <p>Measure the real-world results of campus green initiatives.</p>
                </div>
                <div class="metrics-grid">
                    <div class="metric">
                        <span class="metric-label">Trees Planted</span>
                        <span class="metric-value"><?php echo $total_trees ?></span>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Waste Collected</span>
                        <span class="metric-value"><?php echo $total_waste ?></span>
                    </div>
                </div>
            </div>
        </footer>
    <script src="header.js"></script>
    <script src="report.js"></script>
</body>
</html>