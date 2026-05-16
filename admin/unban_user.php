<?php
include "../conn.php";

if (isset($_GET['id'])) {

    $id = $_GET['id'];

    $query = "UPDATE users 
              SET account_status = 'ACTIVE' 
              WHERE user_id = '$id'";

    if (mysqli_query($db_connect, $query)) {
        header("Location: user.php");
        exit();
    } else {
        echo "Error updating user status";
    }

}
?>