<?php
    session_start();
    include "conn.php";

    if(isset($_SESSION['user_id'])){
        if(!isset($_SESSION['username'], $_SESSION['role'], $_SESSION['avatar'])){
            header("Location: ../login/login.php");
            exit();
        }

        $user_id = $_SESSION['user_id'];
        $username = $_SESSION['username'];
        $role = $_SESSION['role'];
        $avatar = $_SESSION['avatar'];
    } else if(isset($_COOKIE['user_id'])){
        $user_id = $_COOKIE['user_id'];

        /* Ensure cookie is a number before querying the database */
        if(!filter_var($user_id, FILTER_VALIDATE_INT)){
            header("Location: ../login/login.php");
            exit();
        }

        try{
            // Use prepared statements to prevent SQL injection
            $stmt = $db_connect->prepare("SELECT user_id, username, role, avatar FROM Users WHERE user_id = ?");
            
            // Bind the user_id to the ? and ensure it's an integer
            $stmt->bind_param("i", $user_id);

            // Execute the query
            $stmt->execute();

            // Get the result
            $result = $stmt->get_result();

            if($result && $result->num_rows > 0){
                $user = mysqli_fetch_assoc($result);
                if($user){
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['avatar'] = $user['avatar'];

                    $user_id = $user['user_id'];
                    $username = $user['username'];
                    $role = $user['role'];
                    $avatar = $user['avatar'];
                } else{
                    header("Location: ../login/login.php");
                    exit();
                }
            } else{
                header("Location: ../login/login.php");
                exit();
            }

            $stmt->close();
        } catch(Exception $e){
            // Log the error in the server
            error_log("Authentication Error: " . $e->getMessage());
            header("Location: ../login/login.php");
            exit();
        }

    } else{
        header("Location: ../login/login.php");
        exit();
    }
?>