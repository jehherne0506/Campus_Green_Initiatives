<?php
    session_start();

    $_SESSION = [];

    session_destroy();

    if(isset($_COOKIE['user_id'])){
        /* Set cookie time in the past to force browser to delete it */
        setcookie('user_id', "", time() - 3600, "/");
    }

    header("Location: ./login/login.php");
    exit();
?>