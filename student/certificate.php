<?php
    include("../conn.php");
    include("../auth.php");

    if(!isset($_SESSION['role']) || $_SESSION['role'] !== "STUDENT"){
        header("Location: ../login/login.php");
        exit();
    }

    $user_id = $_SESSION["user_id"];

    $username = "";
    $total_volunteer_hours = 0.00;
    $all_participated_events = [];

    $user_sql = "SELECT username, total_volunteer_hours FROM Users WHERE user_id = ?";
    $stmt_user = $db_connect->prepare($user_sql);
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $user_result = $stmt_user->get_result();
    $user_data = $user_result->fetch_assoc();

    if($user_data){
        $username = $user_data["username"];
        $total_volunteer_hours = $user_data["total_volunteer_hours"];
    }

    $events_sql = "SELECT e.title, e.event_date, e.time_start, e.time_end FROM Event_Participants ep JOIN Events e ON ep.event_id = e.event_id JOIN Users u ON ep.user_id = u.user_id WHERE u.user_id = ? AND ep.attendance_status = 'PRESENT'";
    $stmt_events = $db_connect->prepare($events_sql);
    $stmt_events->bind_param("i", $user_id);
    $stmt_events->execute();
    $events_result = $stmt_events->get_result();
    while($row = $events_result->fetch_assoc()){
        $all_participated_events[] = $row;
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoRiseAPU</title>
    <link rel="icon" href="../assets/images/logo.png" type="image/png">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&family=Roboto:wght@300;400;500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="certificate.css">

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
            <li>
                <a href="leaderboard.php"><img src="../assets/images/leaderboard.png" alt="leaderboard">Leaderboard</a>
            </li>
            <li class="active_nav">
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

        <main>
            <div class="page-heading">
                <div>
                    <h1>Certificate</h1>
                    <p>Certified proof of your total volunteer hours and green impact.</p>
                </div>
                <div>
                    <button id="download-btn" class="download-btn">Download PDF</button>
                </div>
            </div>
            <div class="certificate" id="certificate">
                <img class="logo" src="../assets/images/logo-black.png" alt="Logo Icon" />
                <div class="subtitle">Certificate of</div>
                <div class="title">Volunteer Participation</div>

                <p class="desc">This certificate is awarded to</p>
                <div class="name"><?php echo $username ?></div>
                <p class="desc">
                    For actively participating in community environmental activities
                    organized by EcoRiseAPU.
                </p>

                <div class="hours-box">
                    <div class="hours-title">Total Volunteer Hours</div>
                    <div class="hours"><?php echo $total_volunteer_hours ?> Hours</div>
                </div>

                <h3>Events Participated</h3>

                <table>
                    <tr>
                        <th>Event Name</th>
                        <th>Date</th>
                        <th>Hours</th>
                    </tr>
                    <?php foreach($all_participated_events as $row){ ?>
                        <?php 
                            $start = new DateTime($row['time_start']);
                            $end = new DateTime($row['time_end']);

                            $interval = $start->diff($end);

                            $hours_diff = number_format($interval->h + ($interval->i / 60), 2);
                        ?>
                        <tr>
                            <td><?php echo $row['title'] ?></td>
                            <td><?php echo $row['event_date'] ?></td>
                            <td><?php echo $hours_diff ?></td>
                        </tr>
                    <?php } ?>
                </table>

            </div>
        </main>
</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="header.js"></script>
<script src="certificate.js"></script>