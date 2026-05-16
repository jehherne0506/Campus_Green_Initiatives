<?php
include "../conn.php";

if (isset($_POST['update_user'])) {

    $user_id  = $_POST['user_id'];
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET username=?, email=?, password_hash=? WHERE user_id=?";
        $stmt = mysqli_prepare($db_connect, $query);
        mysqli_stmt_bind_param($stmt, "sssi", $username, $email, $hashed_password, $user_id);
    } else {
        $query = "UPDATE users SET username=?, email=? WHERE user_id=?";
        $stmt = mysqli_prepare($db_connect, $query);
        mysqli_stmt_bind_param($stmt, "ssi", $username, $email, $user_id);
    }

    if (mysqli_stmt_execute($stmt)) {
        header("Location: user.php?updated=1");
        exit();
    } else {
        error_log("Update Error: " . mysqli_error($db_connect));
        echo "Error updating user details.";
    }
    
    mysqli_stmt_close($stmt);

}
?>