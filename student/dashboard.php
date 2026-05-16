<?php
    include("../conn.php");
    include("../auth.php");

    if(!isset($_SESSION['role']) || $_SESSION['role'] !== "STUDENT"){
        header("Location: ../login/login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    /* STORE RESULTS */
    $leaders=[];

    /* LEADERBOARD TOP 3 */
    $leaderboard = mysqli_query($db_connect,"
    SELECT username, avatar, total_volunteer_hours
    FROM users
    WHERE role='STUDENT'
    ORDER BY total_volunteer_hours DESC
    LIMIT 3
    ");

    while($row=mysqli_fetch_assoc($leaderboard)){
    $leaders[]=$row;
    }


    /* TOTAL VOLUNTEER HOURS */
    $hours_query = mysqli_query($db_connect,"
    SELECT total_volunteer_hours 
    FROM users 
    WHERE user_id='$user_id'
    ");

    $hours_data = mysqli_fetch_assoc($hours_query);
    $total_hours = $hours_data['total_volunteer_hours'] ?? 0;


    /* EVENTS PARTICIPATED */
    $events_query = mysqli_query($db_connect,"
    SELECT COUNT(*) as total 
    FROM event_participants 
    WHERE user_id='$user_id'
    AND attendance_status='Present'
    ");

    $events_data = mysqli_fetch_assoc($events_query);
    $total_events = $events_data['total'] ?? 0;


    /* ACTIVE VOUCHERS */
    $voucher_query = mysqli_query($db_connect,"
    SELECT COUNT(*) as total
    FROM voucher_claims
    WHERE user_id='$user_id'
    AND expires_at >= CURDATE()
    ");

    $voucher_data = mysqli_fetch_assoc($voucher_query);
    $total_vouchers = $voucher_data['total'] ?? 0;


    /* WEEKLY OPPORTUNITIES */
    $weekly_events = mysqli_query($db_connect,"
    SELECT *
    FROM events
    WHERE event_date >= CURDATE() AND status = 'APPROVED'
    ORDER BY event_date ASC
    LIMIT 3
    ");

    /* MY RANKING */
    $rank_query = mysqli_query($db_connect,"
    SELECT rank FROM (

    SELECT user_id,
    RANK() OVER (ORDER BY total_volunteer_hours DESC) as rank

    FROM users
    WHERE role='STUDENT'

    ) ranked

    WHERE user_id='$user_id'
    ");

    $rank_data = mysqli_fetch_assoc($rank_query);
    $my_rank = $rank_data['rank'] ?? '-';

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
    <link rel="stylesheet" href="student.css">
</head>
<body>
    <img src="../assets/images/close.png" alt="Close Icon" class="close-sidebar-btn" onclick="toggleSidebarVisibility()" />
    <aside class="sidebar">
        <div class="top-sidebar">
            <img src="../assets/images/logo.png" alt="EcoRiseAPU Logo"/>
        </div>
        <ul class="nav-links">
            <li class="active_nav">
                <a href="student_dashboard.php"><img src="../assets/images/home.png" alt="home">Dashboard</a>
            </li>
            <li>
                <a href="events.php"><img src="../assets/images/event.png" alt="calender">Events</a>
            </li>
            <li>
                <a href="my_events.php"><img src="../assets/images/timetable.png" alt="timetable">My Events</a>
            </li>
            <li>
                <a href="rewards.php"><img src="../assets/images/reward.png" alt="reward">Rewards</a>
            </li>
            <li>
                <a href="leaderboard.php"><img src="../assets/images/leaderboard.png" alt="leaderboard">Leaderboard</a>
            </li>
            <li>
                <a href="certificate.php"><img src="../assets/images/certificate.png" alt="certificate">Certificate</a>
            </li>
            <li>
                <a href="profile.php"><img src="../assets/images/user.png" alt="user">Profile</a>
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
                    <small>STUDENT</small>
                </div>
                <img src="<?php echo htmlspecialchars($avatar) ?>" alt="user icon" class="user-icon" href="profile.php">
            </div>
        </header>

        <section class="hero-banner">
            <div class="hero-content">
                <h1>Welcome back,<br><span class="name-banner"><?php echo htmlspecialchars($username) ?>!</span></h1>
                <p>You're making a real difference. Check out your impact summary below and see what new initiatives are happening this week.</p>
                <a href="events.php" class="btn-primary">BROWSE NEW EVENTS <span>🡺</span></a>
            </div>
        </section>

        <section class="stats-container">
            <div class="stat-card" style="--card-color: #00FF88;">
                <div class="stat-header">
                    <img src="../assets/images/clock.png" alt="Clock Icon">
                    <p>TOTAL</p>
                </div>
                <h2><?php echo $total_hours; ?></h2>
                <label>VOLUNTEER HOURS</label>
            </div>
            <div class="stat-card" style="--card-color: #60A5FA;">
                <div class="stat-header">
                    <img src="../assets/images/calendar-check.png" alt="Calendar Icon">
                    <p>COMPLETED</p>
                </div>
                <h2><?php echo $total_events; ?></h2>
                <label>EVENT PARTICIPATED</label>
            </div>
            <div class="stat-card" style="--card-color: #C084FC;">
                <div class="stat-header">
                    <img src="../assets/images/coupon.png" alt="active-voucher">
                    <p>AVAILABLE</p>
                </div>
                <h2><?php echo $total_vouchers; ?></h2>
                <label>ACTIVE VOUCHERS</label>
            </div>
        </section>

        <div class="lower-grid">
            <div class="opportunities-panel">
                <div class="panel-header">
                    <h3>Weekly Opportunities</h3>
                    <a href="events.php" id="browse-all">BROWSE ALL 🡺</a>
                </div>
                
                <?php while($event=mysqli_fetch_assoc($weekly_events)) { ?>
                <div class="opp-item">
                    <div class="opp-thumb">
                        <img src="<?php echo $event['event_image']; ?>" class="event-img">
                    </div>

                    <div class="opp-info">
                        <h4><?php echo $event['title']; ?></h4>
                        <p>
                            📅 <?php echo $event['event_date']; ?>
                            &nbsp; 📍 <?php echo $event['location']; ?>
                        </p>
                    </div>
                    <button class="btn-outline" onclick="window.location.href='events.php'">REGISTER ></button>
                </div>
                <?php } ?>
            </div>

            <div class="leaderboard-container">
                <div class="leaderboard-header">
                    <h1>Leaderboard</h1>
                </div>

                <div class="podium-container">
                    <div class="podium-item second">
                        <div class="avatar-wrapper">
                            <img src="<?php echo $leaders[1]['avatar'] ?? '../assets/uploads/default_user_icon.webp'; ?>">
                            <div class="score-badge">🍃 <?php echo $leaders[1]["total_volunteer_hours"] ?? 0; ?></div>
                        </div>
                        <p class="name"><?php echo $leaders[1]["username"] ?? '-'; ?></p>
                        <div class="bar">#2</div>
                    </div>

                    <div class="podium-item first">
                        <div class="avatar-wrapper">
                            <img src="<?php echo $leaders[0]['avatar'] ?? '../assets/images/user-icon.png'; ?>">
                            <div class="score-badge">🍃 <?php echo $leaders[0]['total_volunteer_hours'] ?? 0.00; ?> </div>
                        </div>
                        <p class="name"><?php echo $leaders[0]['username'] ?? '-'; ?></p>
                        <div class="bar">#1</div>
                    </div>

                    <div class="podium-item third">
                        <div class="avatar-wrapper">
                            <img src="<?php echo $leaders[2]['avatar'] ?? '../assets/images/user-icon.png'; ?>">
                            <div class="score-badge">🍃 <?php echo $leaders[2]['total_volunteer_hours'] ?? 0.00; ?></div>
                        </div>
                        <p class="name"><?php echo $leaders[2]['username'] ?? '-'; ?></p>
                        <div class="bar">#3</div>
                    </div>
                </div>

                <div class="user-ranking-footer">
                    <div class="rank-id">#<?php echo $my_rank; ?></div>
                    <img src="<?php echo !empty($avatar) ? htmlspecialchars($avatar) : '../assets/uploads/default_user_icon.webp'; ?>" class="mini-avatar" alt="User Avatar">
                    <div class="user-info">
                        <p class="user-name"><?php echo htmlspecialchars($username) ?></p>
                        <span class="user-score">🍃 <?php echo $total_hours; ?> hours</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="header.js"></script>
</body>
</html>