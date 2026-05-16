<?php
    include("../conn.php");
    include("../auth.php");

    if(!isset($_SESSION['role']) || $_SESSION['role'] !== "ORGANIZER"){
        header("Location: ../login/login.php");
        exit();
    };

    $user_id = $_SESSION["user_id"];
    $all_events_stats = [];
    $total_student_registered = 0;
    $total_attendance = 0;
    $total_absent = 0;
    $total_pending = 0;
    $total_attendance_rate = 0;
    $total_volunteer_hours = 0;

    $all_events_basic_data = [];

    $target_trees = 500;
    $target_waste = 1000;
    $actual_trees = 0;
    $actual_waste = 0;
    $total_impact_score = 0;

    $total_volunteer_hours = 0;
    $user_events_data = [];
    $participant_result = null;
    
    try{
        $all_events_sql = "SELECT ep.*, e.time_start, e.time_end FROM Event_Participants ep JOIN Events e ON ep.event_id = e.event_id WHERE e.organizer_id = ? AND (e.status = 'APPROVED' OR e.status = 'COMPLETED')";
        $stmt1 = $db_connect->prepare($all_events_sql);
        $stmt1->bind_param("i", $user_id);
        $stmt1->execute();
        $all_events_result = $stmt1->get_result();
        
        while($row = $all_events_result->fetch_assoc()){
            $all_events_stats[] = $row;
        }

        if(count($all_events_stats) > 0){
            $total_student_registered = count($all_events_stats);
            foreach($all_events_stats as $row){
                if($row["attendance_status"] === "PRESENT"){
                    $total_attendance ++;

                    $start = strtotime($row['time_start']);
                    $end = strtotime($row['time_end']);
                    $hours_diff = ($end - $start) / 3600;
                    $total_volunteer_hours += $hours_diff;
                    
                } else if($row["attendance_status"] === "ABSENT"){
                    $total_absent ++;

                } else if($row["attendance_status"] === "PENDING"){
                    $total_pending ++;
                    
                }
            }
            $total_attendance_rate = round($total_attendance / $total_student_registered * 100);
         }
         
         $stmt1->close();

         $sql_all_events_goals = "SELECT title, event_id, COALESCE(trees_planted, 0) AS trees_planted, COALESCE(waste_collected, 0) AS waste_collected FROM Events WHERE organizer_id = ? AND (status = 'APPROVED' OR status = 'COMPLETED')";
         $stmt2 = $db_connect->prepare($sql_all_events_goals);
         $stmt2->bind_param("i", $user_id);
         $stmt2->execute();
         $all_goals_result = $stmt2->get_result();
         while($row = $all_goals_result->fetch_assoc()){
            $actual_trees += $row["trees_planted"];
            $actual_waste += $row["waste_collected"];

            $all_events_basic_data[] = $row;
        }

        $tree_score = $actual_trees / $target_trees * 5;
        $waste_score = $actual_waste / $target_waste * 5;
        $total_impact_score = min(10, round($tree_score + $waste_score, 1));

        $participant_sql = "SELECT u.username, ep.date_joined, ep.attendance_status, e.time_start, e.time_end FROM Event_Participants ep JOIN Users u ON ep.user_id = u.user_id JOIN Events e ON ep.event_id = e.event_id WHERE e.organizer_id = ? AND (e.status = 'APPROVED' OR e.status = 'COMPLETED') ORDER BY ep.date_joined DESC";
        $stmt3 = $db_connect->prepare($participant_sql);
        $stmt3->bind_param("i", $user_id);
        $stmt3->execute();
        $participant_result = $stmt3->get_result();
        if(!$participant_result){
            throw new Exception("Failed to fetch participant data.");
        }
    } catch(Exception $e){
        error_log("Failed to fetch all organizer's events data: " . $e->getMessage());
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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

            <li>
                <a href="attendance.php"><img src="../assets/images/group.png" alt="Participants Icon">Attendance Approval</a>
            </li>

            <li class="active_nav">
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

        <div class="page-heading">
            <h1>Impact Report</h1>
            <p>View and analyse the impact you made to the environment!</p>
        </div>

        <div class="controls-row">
            <select id="event-switch">
                <option value="all">All Events</option>
                <?php
                    foreach($all_events_basic_data as $event){
                        echo "<option value='" . $event['event_id'] . "'>" . $event["title"] . "</option>";
                    }
                ?>
            </select>
        </div>

        <main>
            <div class="stat-grid">
                <div class="metric-card">
                    <div class="card-header">
                        <div class="icon-box">
                            <img src="../assets/images/group.png" alt="group icon" class="card-icon">
                        </div>
                    </div>
                    <h3>TOTAL REGISTERED</h3>
                    <div id="val-registered" class="value"><?php echo $total_student_registered ?></div>
                    <p>Students Registered</p>
                </div>

                <div class="metric-card">
                    <div class="card-header">
                        <div class="icon-box">
                            <img src="../assets/images/check-mark.png" alt="check mark icon" class="card-icon">
                        </div>
                    </div>
                    <h3>ATTENDANCE RATE</h3>
                    <div id="val-attendance" class="value"><?php echo $total_attendance_rate ?>%</div>
                    <p>Students Attended</p>
                </div>

                <div class="metric-card">
                    <div class="card-header">
                        <div class="icon-box">
                            <img src="../assets/images/clock2.png" alt="clock icon" class="card-icon">
                        </div>
                    </div>
                    <h3>VOLUNTEER HOURS</h3>
                    <div id="val-hours" class="value"><?php echo number_format($total_volunteer_hours, 2) ?></div>
                    <p>Total hours volunteered</p>
                </div>

                <div class="metric-card">
                    <div class="card-header">
                        <div class="icon-bo">
                            <img src="../assets/images/diagram.png" alt="diagram icon" class="card-icon">
                        </div>
                    </div>
                    <h3>IMPACT SCORE</h3>
                    <div id="val-impact" class="value"><?php echo $total_impact_score ?></div>
                    <p>Overall impact of the events</p>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Participation Statistics</h3>
                    </div>
                    <div class="bar-chart-mockup">
                        <div class="bar-group">
                            <div class="bar bar-green" data-value="<?php echo $total_student_registered ?>">
                                <span class="tooltip"></span>
                            </div>
                            <span>Registered</span> 
                        </div>
                        <div class="bar-group">
                            <div class="bar bar-orange" data-value="<?php echo $total_pending ?>">
                                <span class="tooltip"></span>
                            </div>
                            <span>Pending</span>
                        </div>
                        <div class="bar-group">
                            <div class="bar bar-green-mid" data-value="<?php echo $total_attendance ?>">
                                <span class="tooltip"></span>
                            </div>
                            <span>Present</span>
                        </div>
                        <div class="bar-group">
                            <div class="bar bar-red" data-value="<?php echo $total_absent ?>">
                                <span class="tooltip"></span>
                            </div>
                            <span>Absent</span>
                        </div>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Environment Impact</h3>
                        <div class="legend">
                            <span><i class="dot actual"></i>Actual</span>
                            <span><i class="dot target"></i>Target</span>
                        </div>
                    </div>
                    <div class="progress-section">
                        <div class="progress-item">
                            <label id="label-trees">Trees Planted (<?php echo min(100, round($actual_trees / $target_trees * 100)) ?>%)</label>
                            <div class="progress-track"><div class="progress-fill" id="bar-trees" style="width: <?php echo min(100, round($actual_trees / $target_trees * 100)) ?>%;"></div></div>
                        </div>
                        <div class="progress-item">
                            <label id="label-waste">Waste Collected (<?php echo min(100, round($actual_waste / $target_waste * 100)) ?>%)</label>
                            <div class="progress-track"><div class="progress-fill" id="bar-waste" style="width: <?php echo min(100, round($actual_waste / $target_waste * 100)) ?>%;"></div></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="volunteer-card">
                <div class="card-header-row">
                    <div class="title-group">
                        <h2>Volunteer Contributions</h2>
                        <p>Individual student impact and hours</p>
                    </div>
                    <a href="javascript:void(0)" id="view-all-btn" onclick="toggleUsers()">View All</a>
                </div>

                <table class="volunteer-table">
                    <thead>
                        <tr>
                            <th>VOLUNTEER NAME</th>
                            <th>DATE_JOINED</th>
                            <th>HOURS LOGGED</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody id="volunteer-body" class="limit-view">
                        <!-- Ensure it's an Object -->
                        <?php if ($participant_result instanceof mysqli_result){ ?>
                            <?php while($row = $participant_result->fetch_assoc()){ 
                                $start = strtotime($row['time_start']);
                                $end = strtotime($row['time_end']);
                                $hours = ($row['attendance_status'] === 'PRESENT') ? round(($end - $start) / 3600, 1) : 0;
                            ?>
                                <tr>
                                    <td class="name"><?php echo htmlspecialchars($row['username']) ?></td>
                                    <td><?php echo date("d M Y", strtotime($row['date_joined'])); ?></td>
                                    <td><?php echo $hours ?></td>
                                    <td><span class="status-<?php echo strtolower($row['attendance_status']); ?>">
                                        <?php echo $row['attendance_status']; ?>
                                    </span></td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <script src="header.js"></script>

    <?php 
        // Convert the result to an array before passing to JS
        $participants_array = [];
        if($participant_result) {
            $participant_result->data_seek(0); // Reset pointer
            while($row = $participant_result->fetch_assoc()) {
                $participants_array[] = $row;
            }
        }
    ?>
    <script>
        const initialDashboardState = {
            registered: <?php echo $total_student_registered; ?>,
            attendanceRate: <?php echo $total_attendance_rate; ?>,
            volunteerHours: <?php echo $total_volunteer_hours; ?>,
            impactScore: <?php echo $total_impact_score; ?>,
            treesPlanted: <?php echo $actual_trees; ?>,
            wasteCollected: <?php echo $actual_waste; ?>,
            participantResult: <?php echo json_encode($participants_array) ?>,
        };
    </script>
    <script src="report.js"></script>
</body>
</html>