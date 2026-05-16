<?php
    include("../auth.php");

    if(!isset($_SESSION['role']) || $_SESSION['role'] !== "STUDENT"){
        header("Location: ../login/login.php");
        exit();
    }

    $user_id = $_SESSION["user_id"];
    $user_result = [];
    $total_points = 0;
    $vouchers_list = [];
    $user_vouchers_list = [];

    try{
        $user_sql = "SELECT u.user_id, u.total_volunteer_hours, u.current_points, COUNT(vc.user_id) AS total_claims FROM Users u LEFT JOIN Voucher_Claims vc ON u.user_id = vc.user_id WHERE u.user_id = ? GROUP BY u.user_id";
        $stmt1 = $db_connect->prepare($user_sql);
        $stmt1->bind_param("i", $user_id);
        $stmt1->execute();
        $result1 = $stmt1->get_result();

        if ($result1->num_rows > 0) {
            $user_result = $result1->fetch_assoc();
            $total_points = $user_result["total_volunteer_hours"] * 100;
        } else {
            throw new Exception("User ID not found in database.");
        }

        $stmt1->close();

        
        $vouchers_sql = "SELECT * FROM Vouchers WHERE status = 'ACTIVE'";
        $vouchers_result = $db_connect->query($vouchers_sql);
        if($vouchers_result){
            while($row = $vouchers_result->fetch_assoc()){
                $vouchers_list[] = $row;
            }             
        } else{
            throw new Exception("Fail to retrieve vouchers data.");
        }

        $user_vouchers_sql = "SELECT * FROM  Voucher_Claims vc JOIN Vouchers v ON vc.voucher_id = v.voucher_id WHERE vc.user_id = ? AND vc.expires_at >= CURDATE()";
        $stmt2 = $db_connect->prepare($user_vouchers_sql);
        $stmt2->bind_param("i", $user_id);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        if($result2->num_rows > 0){
            while($row = $result2->fetch_assoc()){
                $user_vouchers_list[] = $row;
            }
        } else{
            throw new Exception("Fail to retrieve user's vouchers data.");
        }
    } catch(Exception $e){
        error_log("Fail to retrieve user data: " . $e->getMessage());
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
    <link rel="stylesheet" href="rewards.css">
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
            <li class="active_nav">
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

        <section>
            <div class="page-heading">
                <h1>Eco-Rewards</h1>
                <p>Exchange your hard-earned contribution points for exclusive campus rewards and green vouchers.</p>

                <section class="hero-banner">
                    <p>YOUR BALANCE</p>
                <div class="hero-content">
                    <div class="hero-points">
                        <img src="../assets/images/leaf.png" alt="leaf">
                        <h1><?php echo htmlspecialchars($user_result['current_points']) ?> Points</h1>
                    </div>
                    <div class="hero-stats">
                        <p>Total Points Earned: <?php echo $total_points ?></p>
                        <p>Vouchers Redeemed: <?php echo htmlspecialchars($user_result["total_claims"]) ?></p>
                    </div>
                </div>
            </section>

            <div class="rewards-container">
                <?php foreach($vouchers_list as $row){ ?>
                <div class="reward-card" data-id="<?php echo htmlspecialchars($row["voucher_id"]) ?>" data-title="<?php echo htmlspecialchars($row["title"]) ?>" data-points="<?php echo htmlspecialchars($row["points_cost"]) ?>">
                    <div class="card-content">
                        <div class="card-header">
                            <?php if($row['badge']){ ?>
                                <div class="badge"><?php echo htmlspecialchars($row['badge']) ?></div>
                            <?php } else{ ?>
                                <div class="badge" style="visibility: hidden;">&nbsp;</div>
                            <?php } ?>
                        </div>
                        <div class="card-title">
                            <img src="<?php echo htmlspecialchars($row["voucher_image"])  ?>" alt="BilaBila Logo" />
                            <h2 class="title"><?php echo htmlspecialchars($row['title']) ?></h2>
                        </div>
                        <p class="description"><?php echo htmlspecialchars($row['description']) ?></p>
                    </div>
                    <div class="card-footer">
                        <span class="points"><?php echo htmlspecialchars($row['points_cost']) ?> pts</span>
                        <button class="redeem-btn" <?php echo $row['points_cost'] >  $user_result['current_points'] ? "disabled" : "" ?>>Redeem</button>
                    </div>
                </div>
                <?php } ?>
            </div>
        </section>

        <section class="my_voucher">
            <div class="page-heading" style="margin-bottom: 30px">
                <h1>My Vouchers</h1>
                <p>Show the unique codes to the respective store to claim your rewards!</p>
            </div>

            <div class="rewards-container">
                <?php foreach($user_vouchers_list as $row){ 
                    $is_expired = strtotime($row['expires_at']) < time();
                    $banner_text = $is_expired ? "EXPIRED" : "ACTIVE";
                    $banner_class = $is_expired ? "banner-expired" : "banner-active";
                    $card_opacity_class = $is_expired ? "is-expired" : "";
                ?>
                <div class="reward-card <?php echo $card_opacity_class ?>" data-id="<?php echo htmlspecialchars($row["voucher_id"]) ?>" data-title="<?php echo htmlspecialchars($row["title"]) ?>" data-points="<?php echo htmlspecialchars($row["points_cost"]) ?>">
                    <div class="status-ribbon <?php echo $banner_class ?>">
                        <?php echo $banner_text ?>
                    </div>
                    <div class="card-content">
                        <div class="card-header">
                            <?php if($row['badge']){ ?>
                                <div class="badge"><?php echo htmlspecialchars($row['badge']) ?></div>
                            <?php } else{ ?>
                                <div class="badge" style="visibility: hidden;">&nbsp;</div>
                            <?php } ?>
                        </div>
                        <div class="card-title">
                            <img src="<?php echo htmlspecialchars($row["voucher_image"])  ?>" alt="BilaBila Logo" />
                            <h2 class="title"><?php echo htmlspecialchars($row['title']) ?></h2>
                        </div>
                        <p class="description"><?php echo htmlspecialchars($row['description']) ?></p>
                    </div>
                    <div class="my-reward-card-footer">
                        <p class="expiry">Expired: <?php echo date("Y-m-d", strtotime($row['expires_at'])) ?></p>
                        <p class="unique-code-box"><?php echo htmlspecialchars($row['unique_code']) ?></p>
                    </div>
                </div>
                <?php } ?>
            </div>
        </section>
    </div>

    <div id="confirmationRedeemModal" class="blur-background">
        <div class="modal-card">
            <div class="modal-icon">
                <img src="../assets/images/voucher.png" alt="Voucher Icon" />
            </div>
            <h2>Confirm Redeem Voucher</h2>
            <p>Are you sure you want to redeem <strong id="confirmationVoucherTitle"></strong> with <strong id="confirmationVoucherPoints"></strong> points? The voucher will be valid till 
            <strong><?php 
                $date = new DateTime('now');
                $date->modify('+1 month');
                echo $date->format('Y-m-d');
            ?></strong>
            .</p>
            <div class="modal-actions">
                <button class="btn-secondary" onclick="closeConfirmationModal()">Cancel</button>
                <button class="btn-confirm" style="background-color: rgb(38 65 21);" onclick="redeemVoucher()">Redeem</button>
            </div>
        </div>
    </div>

    <div id="successRedeemModal" class="blur-background">
        <div class="modal-card">
            <div class="modal-icon">
                <img src="../assets/images/tick.png" alt="Success Icon" />
            </div>
            <h2>Voucher Redeem Successful!</h2>
            <p>You have successfully redeem <strong id="successVoucherTitle"></strong>. Enjoy!</p>
            <div class="modal-actions">
                <button class="btn-confirm" style="width: 100%; background-color: rgb(38 65 21);" onclick="closeSuccessModal()">OK</button>
            </div>
        </div>
    </div>

    <div id="errorRedeemModal" class="blur-background">
        <div class="modal-card">
            <div class="modal-icon">
                <img src="../assets/images/cross.png" alt="Error Icon" />
            </div>
            <h2>Voucher Redeem Insuccessful!</h2>
            <p>You failed to redeem <strong id="errorVoucherTitle"></strong> as we faced an error. Please try again later.</p>
            <div class="modal-actions">
                <button class="btn-confirm" style="width: 100%; background-color: rgb(200, 50, 50);" onclick="closeErrorModal()">OK</button>
            </div>
        </div>
    </div>
    <script src="header.js"></script>
    <script src="rewards.js"></script>
</body>
</html>