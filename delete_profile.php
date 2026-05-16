<?php
    include("./conn.php");
    include("./auth.php");

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(["status" => "error"]);
        exit();
    }

    $user_id = $_SESSION['user_id'];

    try{
        $sql = "DELETE FROM Users WHERE user_id = ?";
        $stmt = $db_connect->prepare($sql);
        $stmt->bind_param("i", $user_id);
        if($stmt->execute()){
            session_unset();
            session_destroy();
        } else{
            throw new Exception("Fail to execute delete profile SQL.");
        }
        $stmt->close();
        echo json_encode(["status" => "success"]);
    } catch(Exception $e){
        error_log("Fail to delete user profile: " . $e->getMessage());
        echo json_encode(["status" => "error"]);
    }
?>