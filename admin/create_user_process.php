<?php
include "../conn.php";

if(isset($_POST['create_user'])){

    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Set account_status to ACTIVE by default
    $status = "ACTIVE";

    $sql = "INSERT INTO users (username, email, password_hash, role, account_status) 
            VALUES ('$username', '$email', '$password', '$role', '$status')";

    mysqli_query($db_connect, $sql);

    header("Location: user.php");
    exit();
}
?>