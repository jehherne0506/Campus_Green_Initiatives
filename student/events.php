<?php
    include "../conn.php";
    include("../auth.php");

    if(!isset($_SESSION['role']) || $_SESSION['role'] !== "STUDENT"){
        header("Location: ../login/login.php");
        exit();
    }

    $user_id = $_SESSION["user_id"];
    $events_result = null;
    $user_joined_events = [];

    try{
        $events_result = mysqli_query($db_connect, "SELECT e.*, u.username AS organizer_name, IFNULL(ep.participant_count, 0) AS participant_count, c.name AS category FROM Events e 
        JOIN Users u ON e.organizer_id = u.user_id JOIN Categories c ON e.category_id = c.category_id LEFT JOIN (SELECT event_id, COUNT(user_id) AS participant_count FROM Event_Participants GROUP BY event_id) ep ON e.event_id = ep.event_id WHERE e.event_date >= CURDATE() AND e.status = 'APPROVED'");

        if(!$events_result){
            throw new Exception("Fail to fetch events.");
        }

        $participant_sql = "SELECT * FROM Event_Participants ep LEFT JOIN Events e ON ep.event_id = e.event_id WHERE e.event_date >= CURDATE() AND ep.user_id = ?";
        $stmt = $db_connect->prepare($participant_sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $participant_result = $stmt->get_result();

        // Loop through all the results and add a new record into the empty array at the end
        while($joined_events_row = mysqli_fetch_assoc($participant_result)){
            $user_joined_events[] = $joined_events_row['event_id'];
        }

        $stmt->close();
    } catch(Exception $e){
        error_log("Events Page Database Error: " . $e->getMessage());
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
    <link rel="stylesheet" href="events.css">
</head>
<body>
    <img src="../assets/images/close.png" alt="Close Icon" class="close-sidebar-btn" onclick="toggleSidebarVisibility()" />
    
    <aside class="sidebar">
        <div class="top-sidebar">
            <img src="../assets/images/logo.png" alt="EcoRiseAPU Logo"/>
        </div>
        <ul class="nav-links">
            <li>
                <a href="dashboard.php"><img src="../assets/images/home.png" alt="home"> Dashboard</a>
            </li>
            <li class="active_nav">
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

        <div class="page-heading">
            <h1>Event Participation</h1>
            <p>Discover green initiatives around campus and the city. Register to earn volunteer hours.</p>
        </div>

        <div class="main-content">
            <div class="filter-wrapper">
                <button class="mobile-filter-btn" onclick="toggleMobileFilters()">
                    <span>Filter Events</span>
                    <span id="filter-arrow"><img src=".../assets/images/dropdown.svg" alt="Dropdown Icon" /></span>
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

                    <div class="filter-item">
                        <label for="locationFilter" class="filter-label">LOCATION</label>
                        <select id="locationFilter" class="filter-input" onchange="filterEvents()">
                            <option value="All">All</option>
                        </select>
                    </div>
                
                    <button class="btn-clear-filters" onclick="resetFilter()">Reset</button>
                </div>
            </div>      

        <div class="all-events">
            <?php if(mysqli_num_rows($events_result) > 0){ ?>
                    <?php while ($row = mysqli_fetch_assoc(($events_result))){ ?>
                        <?php 
                            $start = new DateTime($row['time_start']);
                            $end = new DateTime($row['time_end']);
                            
                            // Calculate the difference and hold the specific difference in years, months, days, hours, and minutes.
                            $duration = $start->diff($end);

                            // $duration->h takes the hours from duration, $duration->i takes the minutes from duration
                            $decimal_hours = $duration->h + ($duration->i / 60);
                            
                            // Format the output to 1 decimal place
                            $hours_total = number_format($decimal_hours, 1) . ' Fixed Hours' ?>

                        <div class='detailed-event-card' 
                            data-event_id='<?php echo htmlspecialchars($row['event_id'])  ?>'
                            data-category='<?php echo htmlspecialchars($row['category']) ?>'
                            data-date='<?php echo date('Y-m', strtotime($row['event_date'])) ?>' 
                            data-location='<?php echo htmlspecialchars($row['location']) ?>'
                        >
                            <img src='<?php echo htmlspecialchars($row['event_image']) ?>'
                            alt='<?php echo htmlspecialchars($row['title']) ?>'
                            class="event-hero-img">

                            <div class="event-body">
                                <div class="event-tags">
                                    <span class="tag type-tag"><?php echo htmlspecialchars($row['category']) ?></span>
                                    <span class="tag hours-tag"><?php echo $hours_total ?></span>
                                </div>
                                <h4><?php echo htmlspecialchars($row['title']) ?></h4>
                                <div class="event-meta">
                                    <div class="event-info">
                                        <img src="../assets/images/calendar-check.png" />
                                        <p><?php echo htmlspecialchars($row['event_date']) ?></p>
                                    </div>
                                    <div class="event-info">
                                        <img src="../assets/images/clock.png" />

                                        <!-- g: 12 hour format, i: minutes, A: am/pm -->
                                        <p>
                                            <?php echo date("g:i A", strtotime($row['time_start'])); ?> 
                                            - 
                                            <?php echo date("g:i A", strtotime($row['time_end'])); ?>
                                        </p>
                                    </div>
                                    <div class="event-info">
                                        <img src="../assets/images/location.png" />
                                        <p><?php echo htmlspecialchars($row['location']) ?></p>
                                    </div>
                                    <div class="event-info">
                                        <img src="../assets/images/host.png" />
                                        <p><?php echo htmlspecialchars($row['organizer_name']) ?></p>
                                    </div>
                                </div>
                                <button class="btn-primary" onclick="openConfirmationModal(this)" <?php if(htmlspecialchars($row['participant_count']) == htmlspecialchars($row['max_participants']) || in_array($row['event_id'], $user_joined_events)){ echo "disabled"; } ?>><?php echo in_array($row['event_id'], $user_joined_events) ? "REGISTERED" : "REGISTER NOW <span>🡺</span>"?></button>
                            </div>

                            <div class="event-participants <?php if(htmlspecialchars($row['participant_count']) == htmlspecialchars($row['max_participants'])){ echo "full"; } ?>">
                                <p><?php echo htmlspecialchars($row['participant_count']) ?>/<?php echo htmlspecialchars($row['max_participants']) ?> Participants</p>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
        </div>
    </div>
</div>

<div id="confirmationRegistrationModal" class="blur-background">
    <div class="modal-card">
        <div class="modal-icon">
            <img src="../assets/images/save-the-world.png" alt="Save the World Icon" />
        </div>
        <h2>Confirm Registration</h2>
        <p>Are you sure you want to join <strong id="confirmationEventName"></strong>?</p>
        <div class="modal-actions">
            <button class="btn-secondary" onclick="closeConfirmationModal()">Cancel</button>
            <button class="btn-confirm" style="background-color: rgb(38 65 21);" onclick="participateEvent()">Join</button>
        </div>
    </div>
</div>

<div id="successRegistrationModal" class="blur-background">
    <div class="modal-card">
        <div class="modal-icon">
            <img src="../assets/images/tick.png" alt="Success Icon" />
        </div>
        <h2>Registration Successful!</h2>
        <p>You have successfully joined <strong id="successEventName"></strong>. Thank you for making a difference!</p>
        <div class="modal-actions">
            <button class="btn-confirm" style="width: 100%; background-color: rgb(38 65 21);" onclick="closeSuccessModal()">OK</button>
        </div>
    </div>
</div>

<div id="errorRegistrationModal" class="blur-background">
    <div class="modal-card">
        <div class="modal-icon">
            <img src="../assets/images/cross.png" alt="Error Icon" />
        </div>
        <h2>Registration Unsuccessful!</h2>
        <p>You failed to join <strong id="errorEventName"></strong> as we faced an error. Please try again later.</p>
        <div class="modal-actions">
            <button class="btn-confirm" style="width: 100%; background-color: rgb(200, 50, 50);" onclick="closeErrorModal()">OK</button>
        </div>
    </div>
</div>
<script src="header.js"></script>
<script src="events.js"></script>
</body>
</html>