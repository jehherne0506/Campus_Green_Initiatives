<?php
    include "../conn.php";
    include("../auth.php");

    if(!isset($_SESSION['role']) || $_SESSION['role'] !== "ADMIN"){
        header("Location: ../login/login.php");
        exit();
    }

    $vouchers_data = [];

    try{
        $sql = "SELECT * FROM Vouchers WHERE status != 'DELETED'";
        $result = mysqli_query($db_connect, $sql);

        if(!$result){
            throw new Exception("Fail to fetch vouchers");
        }

        while($row = mysqli_fetch_assoc($result)){
            $vouchers_data[] = $row;
        }
    } catch(Exception $e) {
        error_log("Admin failed to fetch vouchers query: " . $e->getMessage());
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
    <link rel="stylesheet" href="voucher.css">
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

            <li class="active_nav">
                <a href="voucher.php"><img src="../assets/images/reward.png" alt="Reward Icon">Voucher Management</a>
            </li>

            <li>
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
                    <strong><?php echo htmlspecialchars($username) ?></strong>
                    <small>ADMIN</small>
                </div>
                <img src="<?php echo htmlspecialchars($avatar) ?>" alt="user icon" class="user-icon" href="profile.php">
            </div>
        </header>

        <div class="page-heading">
            <h1>Voucher Management</h1>
            <p>Create, edit and remove vouchers from the student catalog</p>
        </div>
        
        <main>
            <div class="add-voucher">
                <h1>Add New Voucher</h1>
                <form class="voucher-input" id="createVoucherForm" method="post" enctype="multipart/form-data">
                    <div class="input-row">
                        <div class="input-field">
                            <label for="title">Voucher Title</label>
                            <input name="title" id="title" type="text" required min="2" />
                        </div>
                        <div class="input-field">
                            <label for="points">Points Required</label>
                            <input name="points" id="points" type="number" required />
                        </div>
                        <div class="input-field">
                            <label for="badge">Badge Label (Optional)</label>
                            <input name="badge" id="badge" type="text" />
                        </div>
                        <div class="input-field">
                            <label for="logo">Partner Logo (Image)</label>
                            <input name="logo" id="logo" type="file" accept="image/*" required />
                        </div>
                    </div>
                    <div>
                        <div class="input-field">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" rows="2" required></textarea>
                        </div>
                    </div>
                    <div class="create-voucher">
                        <button class="create-voucher-btn">Create Voucher</button>
                    </div>
                </form>
            </div>

            <div class="all-vouchers">
                <h1>All Vouchers</h1>
                <div class="vouchers-container">
                    <?php foreach($vouchers_data as $row){ ?>
                        <div class="voucher-card" data-id="<?php echo htmlspecialchars($row['voucher_id']) ?>" data-status="<?php echo htmlspecialchars($row['status']) ?>">
                            <div class="card-content">
                                <div class="card-header">
                                    <?php if($row['badge']){ ?>
                                        <div class="badge"><?php echo htmlspecialchars($row['badge']) ?></div>
                                    <?php } else{ ?>
                                        <div></div>
                                    <?php } ?>
                                    <div class="card-actions">
                                        <button class="edit-btn"><img src="../assets/images/edit.png" alt="Edit Icon" /></button>
                                        <button class="pause-btn"><img src="../assets/images/pause.png" alt="Pause Icon" /></button>
                                        <button class="delete-btn"><img src="../assets/images/delete.png" alt="Delete Icon" /></button>
                                    </div>
                                </div>
                                <div class="card-title">
                                    <img src="<?php echo htmlspecialchars($row['voucher_image']) ?>" alt="Brand Logo" />
                                    <h2 class="title"><?php echo htmlspecialchars($row['title']) ?></h2>
                                </div>
                                <p class="description"><?php echo htmlspecialchars($row['description']) ?></p>
                            </div>
                            <div class="card-footer">
                                <span class="points"><?php echo htmlspecialchars(string: $row['points_cost']) ?> pts</span>
                                <p class="status" style="color: <?php echo $row['status'] === "ACTIVE" ? "green" : "red" ?>;"><?php echo htmlspecialchars($row['status']) ?></p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </main>
    </div>

    <div id="confirmationToggleStatusModal" class="blur-background">
        <div class="modal-card">
            <div class="modal-icon">
                <img src="./../assets/images/pause.png" alt="Pause Icon" />
            </div>
            <h2>Voucher <span class="toggleStatus"></span> Confirmation</h2>
            <p>Are you sure you want to <span class="toggleStatus"></span> <strong id="confirmationToggleStatusVoucherTitle"></strong>?</p>
            <div class="modal-actions">
                <button class="btn-secondary">Cancel</button>
                <button class="btn-confirm" style="background-color: rgb(38 65 21);" onclick="toggleVoucherStatus(this)">Yes</button>
            </div>
        </div>
    </div>

    <div id="confirmationDeleteModal" class="blur-background">
        <div class="modal-card">
            <div class="modal-icon">
                <img src="./../assets/images/delete.png" alt="Delete Icon" />
            </div>
            <h2>Voucher Delete Confirmation</h2>
            <p>Are you sure you want to delete <strong id="confirmationDeleteVoucherTitle"></strong>?</p>
            <div class="modal-actions">
                <button class="btn-secondary">Cancel</button>
                <button class="btn-confirm" style="background-color: rgb(38 65 21);" onclick="deleteVoucher(this)">Yes</button>
            </div>
        </div>
    </div>

    <form id="editVoucherForm" class="blur-background">
        <div class="edit-voucher-modal">
            <h1>Edit Voucher</h1>
            <span class="close-modal">&times;</span>
            <div class="voucher-input">
                <input type="hidden" name="editId" id="editId" />
                <div class="input-row">
                    <div class="input-field">
                        <label for="editTitle">Voucher Title</label>
                        <input name="editTitle" id="editTitle" type="text" required min="2" />
                    </div>
                    <div class="input-field">
                        <label for="editPoints">Points Required</label>
                        <input name="editPoints" id="editPoints" type="number" required />
                    </div>
                    <div class="input-field">
                        <label for="editBadge">Badge Label (Optional)</label>
                        <input name="editBadge" id="editBadge" type="text" />
                    </div>
                    <div class="input-field">
                        <div class="logo-field">
                        <label for="editLogo">Partner Logo (Image)</label>
                
                        </div>
                        <div style="display: flex;">
                        <input name="editLogo" id="editLogo" type="file" accept="image/*" /><img id="editLogoPreview" src="">
                        </div>
                    </div>
                </div>
                <div>
                    <div class="input-field">
                        <label for="editDescription">Description</label>
                        <textarea name="editDescription" id="editDescription" rows="2" required></textarea>
                    </div>
                </div>
                <div class="edit-voucher">
                    <button class="edit-voucher-btn">Edit Voucher</button>
                </div>
            </div>
        </div>
    </form>

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
    <script src="header.js"></script>
    <script src="voucher.js"></script>
</body>
</html>