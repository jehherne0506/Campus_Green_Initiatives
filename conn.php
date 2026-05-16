<?php
    // Tell mysqli to throw mysqli_sql_exception Exceptions instead of silent errors
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $localhost = "localhost";
    $user = "root";
    $password = "";
    $db_name = "ecoriseapu";
    
    try{
        $db_connect = mysqli_connect($localhost, $user, $password, $db_name);
    } catch(mysqli_sql_exception $e) {
        // Log the error in server
        error_log("Database Connection Error: " . $e->getMessage());

        die("System Error: Unable to connect to database, Please try again later.");
    }
?>