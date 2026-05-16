<?php
    include("./conn.php");
    include("./auth.php");


    $user_id = $_SESSION['user_id'];

    $data = json_decode(file_get_contents("php://input"), true);

    if(!isset($data["current_password"]) || !isset($data["new_password"])){
        echo json_encode(["status"=> "error"]);
        exit();
    }

    $current_password = $data['current_password'];
    $new_password = $data['new_password'];
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

    try{
        $sql_validate = "SELECT password_hash FROM Users WHERE user_id = ?";
        $stmt_validate = $db_connect->prepare($sql_validate);
        $stmt_validate->bind_param("i", $user_id);
        $stmt_validate->execute();
        $validate_result = $stmt_validate->get_result();
        $stmt_validate->close();

        if($validate_result->num_rows > 0){
            $row = $validate_result->fetch_assoc();
            if(!password_verify($current_password, $row["password_hash"])){
                echo json_encode(["status"=> "validate"]);
                exit(); 
            }
        }

        $sql_update = "UPDATE Users SET password_hash = ? WHERE user_id = ?";
        $stmt_update = $db_connect->prepare($sql_update);
        $stmt_update->bind_param("si", $password_hash, $user_id);

        if($stmt_update->execute()){
            echo json_encode(["status" => "success"]);
        } else {
            throw new Exception("Fail to update record in Users database");
        }

        $stmt_update->close();
    } catch(Exception $e) {
        error_log("Fail to update profile: " . $e->getMessage());
        echo json_encode(["status"=> "error"]);
    }
?>