<?php
    include("../conn.php");
    include("../auth.php");

    if(!isset($_SESSION['role']) || $_SESSION['role'] !== "STUDENT"){
        header("Location: ../login/login.php");
        exit();
    }

    $user_id = $_SESSION["user_id"];

    $event_id = $_GET['event_id'] ?? null;
    if(!$event_id){
        header("Location: ../login/login.php");
        exit();
    }

    $sql_validate = "SELECT attendance_status FROM Event_Participants WHERE event_id = ? AND user_id = ?";
    $stmt1 = $db_connect->prepare($sql_validate);
    $stmt1->bind_param("ii", $event_id, $user_id);
    $stmt1->execute();
    $validate_result = $stmt1->get_result();

    if(!$validate_result){
        header("Location: ../login/login.php");
        exit();
    }

    $validate_status = $validate_result->fetch_assoc();
    if($validate_status["attendance_status"] !== "REGISTERED"){
        header("Location: ../login/login.php");
        exit();
    }

    $sql_title = "SELECT title FROM Events WHERE event_id = ?";
    $stmt2 = $db_connect->prepare($sql_title);
    $stmt2->bind_param("i", $event_id);
    $stmt2->execute();
    $title_result = $stmt2->get_result();
    $event = $title_result->fetch_assoc();
    $event_title = $event["title"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoRiseAPU</title>
    <link rel="icon" href="../assets/images/logo.png" type="image/png">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="submit_proof.css">
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

    <div class="container">
        <h2>Submit Proof</h2><br>

        <form id="proofForm" method="post" enctype="multipart/form-data">
            <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event_id); ?>">

            <label>Title</label>
            <input type="text" id="title" name="title" value="<?php echo $event_title ?>" readonly>

            <label>Description</label>
            <textarea id="description" name="description" rows="5" placeholder="Describe your experience throughout the event" required minlength="10"></textarea>
            
            <label>Proof Attachment (Photo)</label>
            <div class="upload-container">
                <input type="file" id="photo" name="proof_image" accept="image/*" required>

                <label for="photo" class="upload-box" id="uploadBox" required>
                    <div class="upload-content" id="uploadContent">
                        <div class="icon-circle">
                            <img src="../assets/images/image.png" alt="icon" width="30">
                        </div>
                        <strong>Click to upload photo</strong>
                        <span>JPEG, PNG or GIF up to 10MB</span>                      
                    </div>

                    <img id="preview" class="preview" alt="Image Preview">  

                </label>
            </div>
            
            <button type="submit" id="submit">Submit Proof</button>
        </form>
    </div>

    <div id="successSubmitProofModal" class="blur-background">
        <div class="modal-card">
            <div class="modal-icon">
                <img src="../assets/images/tick.png" alt="Success Icon" />
            </div>
            <h2>Proof Submitted Successful!</h2>
            <p></p>
            <div class="modal-actions">
                <button class="btn-confirm" style="width: 100%; background-color: rgb(38 65 21);" onclick="closeSuccessModal()">OK</button>
            </div>
        </div>
    </div>

    <div id="errorSubmitProofModal" class="blur-background">
        <div class="modal-card">
            <div class="modal-icon">
                <img src="../assets/images/cross.png" alt="Error Icon" />
            </div>
            <h2></h2>
            <p></p>
            <div class="modal-actions">
                <button class="btn-confirm" style="width: 100%; background-color: rgb(200, 50, 50);" onclick="closeErrorModal()">OK</button>
            </div>
        </div>
    </div>
    
    <script src="header.js"></script>
    <script src="submit_proof.js"></script>
</body>
</html>