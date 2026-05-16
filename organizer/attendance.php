<?php
    include("../conn.php");
    include("../auth.php");

    if(!isset($_SESSION['role']) || $_SESSION['role'] !== "ORGANIZER"){
        header("Location: ../login/login.php");
        exit();
    };

    $user_id = $_SESSION["user_id"];

    $user_events_data = [];
    $first_event_id = "";

    try{
         $user_event_sql = "SELECT event_id, title, event_date FROM Events WHERE organizer_id = ? AND (status = 'APPROVED' OR status = 'COMPLETED')";
         $stmt = $db_connect->prepare($user_event_sql);
         $stmt->bind_param("i", $user_id);
         $stmt->execute();
         $result = $stmt->get_result();

         while($row = $result->fetch_assoc()){
            $user_events_data[] = $row;
         }

         if(count($user_events_data) > 0){
            $first_event_id = $user_events_data[0]["event_id"];
         }
         
         $stmt->close();
    } catch(Exception $e){
        error_log("Failed to get organizer event: " . $e->getMessage());
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
    <link rel="stylesheet" href="attendance.css">
<body>
    <img src="../assets/images/close.png" alt="Close Icon" class="close-sidebar-btn" onclick="toggleSidebarVisibility()" />
    <aside class="sidebar">
        <div class="top-sidebar">
            <img src="../assets/images/logo.png" alt="EcoRiseAPU Logo"/>
            <!-- <img  src="../assets/images/close.png" alt="Close Icon" onclick="toggleSidebarVisibility()" /> -->
        </div>
        <ul class="nav-links">
            <li>
                <a href="dashboard.php"><img src="../assets/images/home.png" alt="Home Icon">Dashboard</a>
            </li>
            <li>
                <a href="events.php"><img src="../assets/images/event.png" alt="Calendar Icon">Event Management</a>
            </li>
            <li class="active_nav">
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

        <main>
            <div class="page-heading">
                <h1>Attendance Approval</h1>
                <p>Easily review and approve your team's logged hours to keep our green initiatives moving forward.</p>
            </div>

            <div class="events_selection">
                <h1>Events Selection</h1>
                <div class="selection_header">
                    <div class="all_events">
                        <?php 
                            $count = 0;

                            foreach($user_events_data as $row){
                                $class = $count === 0 ? 'active' : '';
                                $formatted_date = date("M d Y", strtotime($row['event_date']));

                                echo '<button data-id=' . htmlspecialchars($row['event_id']) . ' class="' . $class . '">' . htmlspecialchars($row['title']) . " (" . $formatted_date . ")</button>";
                                $count++;       
                            }
                        ?>
                    </div>
                    <div class="event_stats">
                        <div class="stats_container registered">
                            <h2></h2>
                            <p>Registered</p>
                        </div>
                        <div class="stats_container pending">
                            <h2></h2>
                            <p>Pending</p>
                        </div>
                        <div class="stats_container completed">
                            <h2></h2>
                            <p>Completed</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pending_approval">
                <h1>Pending Approval</h1>
                <div class="all_approvals">

                </div>
            </div>

            <div class="attendance_overview">
                <h1>Attendance Overview</h1>
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Student Email</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="all-attendance">
                        
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <script src="header.js"></script>
    <script src="attendance.js"></script>
    <script>
        window.onload = function() {
            const initialId = "<?php echo $first_event_id; ?>";
            if (initialId) {
                fetchEventData(initialId);
            }
        };
    </script>
</body>
</html>