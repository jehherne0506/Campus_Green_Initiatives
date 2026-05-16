<?php
    include "../conn.php";
    include "../auth.php";

    if(!isset($_SESSION['role']) || $_SESSION['role'] !== "STUDENT"){
        header("Location: ../login/login.php");
        exit();
    }

    $user_id = $_SESSION["user_id"];

    $leaderboard_data = [];
    $user_volunteer_hours = 0;
    $user_ranking = "-";

    try{
        $leaderboard_result = mysqli_query($db_connect, "SELECT * FROM Users WHERE role = 'STUDENT' ORDER BY total_volunteer_hours DESC LIMIT 5");
        if($leaderboard_result){
            $leaderboard_data = mysqli_fetch_all($leaderboard_result, MYSQLI_ASSOC);
        } else{
            throw new Exception("Failed to fetch leaderboard data.");
        }

        $user_sql = "SELECT total_volunteer_hours, (SELECT COUNT(*) + 1 FROM Users u2 WHERE u2.total_volunteer_hours > Users.total_volunteer_hours AND u2.role = 'STUDENT') AS ranking FROM Users WHERE user_id = ? AND role = 'STUDENT'";
        $stmt = $db_connect->prepare($user_sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $user_result = $stmt->get_result();

        if($user_result && $user_result->num_rows > 0){
            $user_data = mysqli_fetch_assoc($user_result);
            $user_volunteer_hours = $user_data['total_volunteer_hours'];
            $user_ranking = $user_data['ranking'];
        }

        $stmt->close();
    } catch(Exception $e){
        error_log("Failed to fetch leaderboard data: " . $e->getMessage());
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../assets/images/logo.png" type="image/png">
    <title>EcoRiseAPU</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="leaderboard.css">
<body>
    <img src="../assets/images/close.png" alt="Close Icon" class="close-sidebar-btn" onclick="toggleSidebarVisibility()" />
    <aside class="sidebar">
        <div class="top-sidebar">
            <img src="../assets/images/logo.png" alt="EcoRiseAPU Logo"/>
        </div>
        <ul class="nav-links">
            <li>
                <a href="dashboard.php"><img src="../assets/images/home.png" alt="home">Dashboard</a>
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
            <li class="active_nav">
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

        <div class="page-heading">
            <h1>Leaderboard</h1>
            <p>See who is leading the charge in making our campus greener. Every hour counts!</p>
        </div>

        <div class="leaderboard-container">   
            <div class="podium-section">
                    <div class="podium-card rank-2">
                        <?php if(isset($leaderboard_data[1])){ ?>
                            <div class="podium-avatar">
                                <img src=<?php echo htmlspecialchars($leaderboard_data[1]['avatar']) ?> alt="User Icon">
                                <div class="podium-badge silver">2</div>
                            </div>
                            <h4><?php echo htmlspecialchars($leaderboard_data[1]['username']) ?></h4>
                            <p><?php echo htmlspecialchars($leaderboard_data[1]['total_volunteer_hours']) ?> Hours</p>
                        <?php } else{ ?>
                            <h4>Open Slot</h4>
                        <?php } ?>
                    </div>

                    <div class="podium-card rank-1">
                        <?php if(isset($leaderboard_data[0])){ ?>
                            <div class="podium-avatar">
                                <img src="<?php echo htmlspecialchars($leaderboard_data[0]['avatar']) ?>" alt="User Icon">
                                <div class="podium-badge gold">1</div>
                            </div>
                            <h4><?php echo htmlspecialchars($leaderboard_data[0]['username']) ?></h4>
                            <p><?php echo htmlspecialchars($leaderboard_data[0]['total_volunteer_hours']) ?> Hours</p>
                        <?php } else{ ?>
                            <h4>Open Slot</h4>
                        <?php } ?>
                    </div>

                
                    <div class="podium-card rank-3">
                        <?php if(isset($leaderboard_data[2])){ ?>
                            <div class="podium-avatar">
                                <img src=<?php echo htmlspecialchars($leaderboard_data[2]['avatar']) ?> alt="User Icon">
                                <div class="podium-badge bronze">3</div>
                            </div>
                            <h4><?php echo htmlspecialchars($leaderboard_data[2]['username']) ?></h4>
                            <p><?php echo htmlspecialchars($leaderboard_data[2]['total_volunteer_hours']) ?> Hours</p>
                        <?php } else{ ?>
                            <h4>Open Slot</h4>
                        <?php } ?>
                    </div>
                
            </div>

            <div class="ranking-list">
                <?php if(isset($leaderboard_data[3])){ ?>
                    <div class="rank-item">
                        <div class="rank-number">4</div>
                        <img src=<?php echo htmlspecialchars($leaderboard_data[3]['avatar']) ?> alt="User Icon" class="list-avatar">
                        <div class="rank-info">
                            <strong><?php echo htmlspecialchars($leaderboard_data[3]['username']) ?></strong>
                        </div>
                        <div class="rank-score">
                            <strong><?php echo htmlspecialchars($leaderboard_data[3]['total_volunteer_hours']) ?></strong> <small>HRS</small>
                        </div>
                    </div>
                <?php } ?>

                <?php if(isset($leaderboard_data[4])){ ?>
                    <div class="rank-item">
                        <div class="rank-number">5</div>
                        <img src=<?php echo htmlspecialchars($leaderboard_data[4]['avatar']) ?> alt="User Icon" class="list-avatar">
                        <div class="rank-info">
                            <strong><?php echo htmlspecialchars($leaderboard_data[4]['username']) ?></strong>
                        </div>
                        <div class="rank-score">
                            <strong><?php echo htmlspecialchars($leaderboard_data[4]['total_volunteer_hours']) ?></strong> <small>HRS</small>
                        </div>
                    </div>
                <?php } ?>

                <div class="rank-item current-user-row">
                    <div class="rank-number"><?php echo $user_ranking ?></div>
                    <img src=<?php echo $avatar ?> alt="User Icon" class="list-avatar">
                    <div class="rank-info">
                        <strong><?php echo htmlspecialchars($username) . "(You)" ?></strong>
                    </div>
                    <div class="rank-score">
                        <strong><?php echo htmlspecialchars($user_volunteer_hours) ?></strong> <small>HRS</small>
                    </div>
                </div>

            </div>
        </div>
    <script src="header.js"></script>
</body>
</html>