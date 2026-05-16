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
        echo "You cannot delete your own account!";
        exit();
    }

    $query = "DELETE FROM users WHERE user_id=?";
    $stmt = mysqli_prepare($db_connect, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);

    header("Location: user.php?deleted=1");
    exit();
}

// Show confirmation page (GET)
if(isset($_GET['id'])){
    $user_id = intval($_GET['id']);

    // Fetch user info
    $query = "SELECT username, email FROM users WHERE user_id=?";
    $stmt = mysqli_prepare($db_connect, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if(!$user){
        header("Location: user.php");
        exit();
    }
} else {
    header("Location: user.php");
    exit();
}
?>
