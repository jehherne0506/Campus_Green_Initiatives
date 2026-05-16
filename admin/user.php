<?php
include "../conn.php";
include "../auth.php";

// Only allow admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "ADMIN") {
    header("Location: ../login/login.php");
    exit();
}

// Handle deletion after confirmation (POST)
if(isset($_POST['confirm_delete'])){
    $user_id = intval($_POST['user_id']);

    // Prevent admin from deleting themselves
    if($user_id == $_SESSION['user_id']){
        echo "<script>alert('You cannot delete your own account!'); window.location='user.php';</script>";
        exit();
    }

    $query = "DELETE FROM users WHERE user_id=?";
    $stmt = mysqli_prepare($db_connect, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);

    header("Location: user.php?deleted=1");
    exit();
}

// Fetch users & stats (same as before)
$total_users_query = "SELECT * FROM users ORDER BY user_id ASC";
$result = mysqli_query($db_connect, $total_users_query);
$total_users = mysqli_num_rows($result);

$active_query = "SELECT COUNT(*) as total FROM users WHERE account_status='ACTIVE'";
$active_result = mysqli_fetch_assoc(mysqli_query($db_connect, $active_query));

$banned_query = "SELECT COUNT(*) as total FROM users WHERE account_status='BANNED'";
$banned_result = mysqli_fetch_assoc(mysqli_query($db_connect, $banned_query));

// Ban user
if(isset($_POST['confirm_ban'])){
    $user_id = intval($_POST['ban_user_id']);
    if($user_id != $_SESSION['user_id']){
        $stmt = mysqli_prepare($db_connect, "UPDATE users SET account_status='BANNED' WHERE user_id=?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
    }
    header("Location: user.php?banned=1");
    exit();
}

// Unban user
if(isset($_POST['confirm_unban'])){
    $user_id = intval($_POST['unban_user_id']);
    $stmt = mysqli_prepare($db_connect, "UPDATE users SET account_status='ACTIVE' WHERE user_id=?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    header("Location: user.php?unbanned=1");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoAdmin - Events Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="user.css">
</head>
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

            <li class="active_nav">
                <a href="user.php"><img src="../assets/images/group.png" alt="Group Icon">User Management</a>
            </li>

            <li>
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

        <div class="page-heading">
            <h1>User Management</h1>
            <p>Manage and track all users in this platform.</p>
        </div>

        <div class="stat-grid">
            <div class="metric-card">
                <h3>TOTAL USER</h3>
                <div class="value"><?php echo $total_users; ?></div>
            </div>
            <div class="metric-card">
                <h3>ACTIVE ACCOUNTS</h3>
                <div class="value"><?php echo $active_result['total']; ?></div>
            </div>
            <div class="metric-card">
                <h3>BANNED ACCOUNTS</h3>
                <div class="value"><?php echo $banned_result['total']; ?></div>
            </div>
        </div>

        <div class="user-management-container">
            <div class="table-controls">
                <div class="search-box">
                    <input type="text" id="userSearch" placeholder="Search users..." onkeyup="filterTable()">
                </div>
                <div class="filter-group">
                    <button class="create-user-btn" onclick="openCreateUserModal()">+ Create User</button>
                    <select class="filter-select" id="roleFilter" onchange="filterRoles()">
                        <option value="all">All Roles</option>
                        <option>Organizer</option>
                        <option>Student</option>
                        <option>Admin</option>
                    </select>
                    <select class="filter-select" id="statusFilter" onchange="filterStatus()">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="banned">Banned</option>
                    </select>
                </div>
            </div>
        
            <div class="table-wrapper">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>USER ID</th>
                            <th>USER DETAILS</th>
                            <th>ROLE</th>
                            <th>STATUS</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><span class="id-badge"><?php echo $user['user_id']; ?></span></td>
                            <td>
                                <div class="user-info">
                                    <?php if(!empty($user['avatar'])): ?>
                                        <img src="<?php echo $user['avatar']; ?>" class="mini-avatar">
                                    <?php else: ?>
                                        <div class="avatar blue"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?php echo htmlspecialchars($user['username']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($user['email']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="role-badge <?php echo strtolower($user['role']); ?>">
                                    <?php echo htmlspecialchars($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-pill <?php echo strtolower($user['account_status']); ?>">
                                    <?php echo $user['account_status']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-container">
                                    <button class="edit-btn" onclick="openEditUserModal('<?php echo $user['user_id']; ?>', '<?php echo addslashes($user['username']); ?>', '<?php echo addslashes($user['email']); ?>')">Edit</button>
                                    
                                    <?php if($user['account_status'] == "ACTIVE"): ?>
                                        <button class="ban-btn" onclick="openBanModal('<?php echo $user['user_id']; ?>', '<?php echo addslashes($user['username']); ?>')">Ban</button>
                                    <?php else: ?>
                                        <button class="unban-btn" onclick="openUnbanModal('<?php echo $user['user_id']; ?>', '<?php echo addslashes($user['username']); ?>')">Unban</button>
                                    <?php endif; ?>
                                     <button class="delete-btn" onclick="openDeleteModal('<?php echo $user['user_id']; ?>', '<?php echo addslashes($user['username']); ?>', '<?php echo addslashes($user['email']); ?>')">Delete</button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div id="createUserModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Create New User</h2>
                <span class="close-btn" onclick="closeCreateUserModal()">&times;</span>
            </div>
            <form method="POST" action="create_user_process.php">
                <div class="form-row">
                    <div class="form-group">
                        <label>USERNAME</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>EMAIL</label>
                        <input type="email" name="email" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>PASSWORD</label>
                        <input type="password" name="password" minlength="8" required>
                    </div>
                    <div class="form-group">
                        <label>ROLE</label>
                        <select name="role">
                            <option value="Student">Student</option>
                            <option value="Organizer">Organizer</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="create_user" class="submit-btn">Create User</button>
            </form>
        </div>
    </div>

    <div id="editUserModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit User Details</h2>
                <span class="close-btn" onclick="closeEditUserModal()">&times;</span>
            </div>
            <form method="POST" action="update_user.php">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="form-row">
                    <div class="form-group">
                        <label>USERNAME</label>
                        <input type="text" name="username" id="edit_username" required>
                    </div>
                    <div class="form-group">
                        <label>EMAIL</label>
                        <input type="email" name="email" id="edit_email" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>NEW PASSWORD (Optional)</label>
                        <input type="password" name="password" id="edit_password" minlength="8" placeholder="Leave blank to keep current">
                    </div>
                </div>
                <button type="submit" name="update_user" class="submit-btn">Save Changes</button>
            </form>
        </div>
    </div>

    <div id="deleteModal" class="modal" style="display:none;">
        <div class="modal-content small-modal">
            <h2>Delete User</h2>
            <p>Are you sure you want to <strong>permanently delete</strong> this user?</p>
            <p id="deleteUserInfo"></p>
            <form method="POST" action="delete_user.php">
                <input type="hidden" name="user_id" id="delete_user_id">
                <button type="submit" name="confirm_delete" class="confirm-btn">Yes, Delete</button>
                <button type="button" class="cancel-btn" onclick="closeDeleteModal()">Cancel</button>
            </form>
        </div>
    </div>


    <!-- Ban Modal -->
    <div id="banModal" class="modal" style="display:none;">
        <div class="modal-content small-modal">
            <h2>Ban User</h2>
            <p>Are you sure you want to <strong>ban</strong> this user?</p>
            <p id="banUserInfo"></p>
            <form method="POST" action="user.php">
                <input type="hidden" name="ban_user_id" id="ban_user_id">
                <button type="submit" name="confirm_ban" class="confirm-btn">Yes, Ban</button>
                <button type="button" class="cancel-btn" onclick="closeBanModal()">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Unban Modal -->
    <div id="unbanModal" class="modal" style="display:none;">
        <div class="modal-content small-modal">
            <h2>Unban User</h2>
            <p>Are you sure you want to <strong>unban</strong> this user?</p>
            <p id="unbanUserInfo"></p>
            <form method="POST" action="user.php">
                <input type="hidden" name="unban_user_id" id="unban_user_id">
                <button type="submit" name="confirm_unban" class="confirm-btn">Yes, Unban</button>
                <button type="button" class="cancel-btn" onclick="closeUnbanModal()">Cancel</button>
            </form>
        </div>
    </div>

<script src="header.js"></script>
<script src="user.js"></script>
</body>
</html>