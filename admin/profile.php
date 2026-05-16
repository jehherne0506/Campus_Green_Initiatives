<?php
    include("../conn.php");
    include("../auth.php");

    if(!isset($_SESSION['role']) || $_SESSION['role'] !== "ADMIN"){
        header("Location: ../login/login.php");
        exit();
    }

    $user_id = $_SESSION["user_id"];
    $total_volunteer_hours = 0;
    $current_points = 0;
    $avatar = "../assets/uploads/default_user_icon.webp";
    $username = "";
    $email = "";

    try{
        $user_sql = "SELECT * FROM Users WHERE user_id = ?";
        $stmt = $db_connect->prepare($user_sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user_result = $stmt->get_result();
        $user_data = $user_result->fetch_assoc();
        if(!$user_data){
            throw new Exception("Fail to fetch user data.");
        }
        $avatar = $user_data["avatar"];
        $username = $user_data["username"];
        $email = $user_data["email"];

        $stmt->close();
    } catch(Exception $e){
        error_log("Profile Page Database Error: " . $e->getMessage());
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
    <link rel="stylesheet" href="profile.css">
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

            <li>
                <a href="report.php"><img src="../assets/images/report.png" alt="Group Icon">System Report</a>
            </li>

            <li class="active_nav">
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
                 <img src="../assets/images/hamburger-menu.png" alt="Hamburger Menu" />
            </div>
            <div class="logo-icon">
                <img src="../assets/images/logo-black.png" alt="EcoRiseAPU Logo" />
            </div>
            <div class="user-profile">
                <div class="user-text">
                    <strong><?php echo htmlspecialchars($username) ?></strong>
                    <small>ADMIN</small>
                </div>
                <img src="<?php echo htmlspecialchars($avatar)  ?>" alt="user icon" class="user-icon" href="profile.php">
            </div>
        </header>

        <div class="main-content">
            <div class="page-heading">
                <h1>My Profile</h1>
                <p>Manage your account settings and view your impact.</p>
            </div>

            <section class="settings-card">
                <h2>Public Profile</h2>
                <hr class="divider">

                <form id="basic_form" class="profile-form" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="remove_avatar" id="remove_avatar_flag" value="0">
                    <div class="profile-picture-section">
                        <img src="<?php echo $avatar ?>" alt="user icon" class="profile-preview">
                        <div class="picture-controls">
                            <h3>Profile Picture</h3>
                            <p>PNG, JPEG. Max size of 1MB</p>
                            <div class="btn-group">
                                <label class="btn btn-main" style="cursor: pointer;">
                                    <input id="avatar_input" name="avatar_input" type="file" style="display: none;" accept="image/*">
                                    Upload New
                                </label>
                                <button id="remove_avatar" class="btn btn-sub">Remove</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" value="<?php echo $username ?>" required minlength="2">
                        </div>

                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" value="<?php echo $email ?>" required>
                        </div>
                    </div>

                    <div class="form-row read-only-row">
                        <div class="form-group">
                            <label>Account Role</label>
                            <input type="text" value="ADMIN" disabled class="disabled-input">
                        </div>
                        <div class="form-group">
                            <label>Account Status</label>
                            <div class="status-badge active-status">ACTIVE</div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-main">Save Profile Changes</button>
                    </div>
                </form>
            </section>

            <section class="settings-card mt-20">
                <div class="security_header">
                    <h2>Security</h2>
                    <button id="dlt-account">Delete Account</button>
                </div>
                <hr class="divider">
                
                <form id="security_form" class="profile-form" method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" id="current_password" name="current_password" placeholder="Enter current password">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" id="new_password" name="new_password" placeholder="Enter new password" minlength="8">
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" minlength="8">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-sub">Update Password</button>
                    </div>
                </form>
            </section>
        </div>

    <div id="confirmationDeleteModal" class="blur-background">
        <div class="modal-card">
            <div class="modal-icon">
                <img src="./../assets/images/delete.png" alt="Delete Icon" />
            </div>
            <h2>Account Delete Confirmation</h2>
            <p>Are you sure you want to delete your account? This action is irreversible, all your data and progress will be deleted.</p>
            <div class="modal-actions">
                <button class="btn-secondary" onclick="closeCompleteModal(this)">Cancel</button>
                <button class="btn-confirm" style="background-color: #dc3545;" onclick="deleteAccount()">Yes</button>
            </div>
        </div>
    </div>
    
    <div id="successModal" class="blur-background">
        <div class="modal-card">
            <div class="modal-icon">
                <img src="./../assets/images/tick.png" alt="Success Icon" />
            </div>
            <h2></h2>
            <p></p>
            <div class="modal-actions">
                <button class="btn-confirm" style="width: 100%; background-color: rgb(38 65 21);" onclick="closeCompleteModal(this)">OK</button>
            </div>
        </div>
    </div>

    <div id="errorModal" class="blur-background">
        <div class="modal-card">
            <div class="modal-icon">
                <img src="./../assets/images/cross.png" alt="Error Icon" />
            </div>
            <h2></h2>
            <p></p>
            <div class="modal-actions">
                <button class="btn-confirm" style="width: 100%; background-color: rgb(200, 50, 50);" onclick="closeCompleteModal(this)">OK</button>
            </div>
        </div>
    </div>
    </div>
    <script src="header.js"></script>
    <script src="profile.js"></script>
</body>
</html>