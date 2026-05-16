<?php
    include("./conn.php");
    include("./auth.php");

    $user_id = $_SESSION['user_id'];

    if(!isset($_POST["username"]) || !isset($_POST["email"])){
        echo json_encode(["status"=> "error"]);
        exit(); 
    }

    $username = $_POST['username'];
    $email = $_POST['email'];

    if(isset($_POST['remove_avatar']) && $_POST['remove_avatar'] == "1"){
        $avatar = "../assets/uploads/default_user_icon.webp";

        $stmt_remove = $db_connect->prepare("UPDATE users SET avatar=? WHERE user_id=?");
        $stmt_remove->bind_param("si", $avatar, $user_id);
        $stmt_remove->execute();
    }

    $sql_validate = "SELECT user_id FROM Users WHERE email = ?";
    $stmt_validate = $db_connect->prepare($sql_validate);
    $stmt_validate->bind_param("s", $email);
    $stmt_validate->execute();
    $validate_result = $stmt_validate->get_result();
    $stmt_validate->close();

    if($validate_result->num_rows > 0){
        $row = $validate_result->fetch_assoc();
        if($row["user_id"] != $user_id){
            echo json_encode(["status"=> "duplicate"]);
            exit(); 
        }
    }

    if(isset($_FILES["avatar_input"]) && $_FILES["avatar_input"]["error"] === 0){
        $avatar = $_FILES["avatar_input"];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];
        $file_mime = mime_content_type($avatar["tmp_name"]);

        if(!in_array($file_mime, $allowed_types) || $avatar["error"] !== 0) {
            echo json_encode(["status"=> "error"]);
            exit();
        }

        $upload_dir = "./assets/uploads/";
        $file_name = time() . "_" . basename($avatar["name"]);

        $target_path = $upload_dir . $file_name;
        $full_path = "." . $upload_dir . $file_name;

        try{
            if(!move_uploaded_file($avatar["tmp_name"], $target_path)){
                throw new Exception("Failed to save uploaded file to directory");
            }

            $sql_update = "UPDATE Users SET username = ?, email = ?, avatar = ? WHERE user_id = ?";
            $stmt_update = $db_connect->prepare($sql_update);
            $stmt_update->bind_param("sssi", $username, $email, $full_path, $user_id);

            if($stmt_update->execute()){
                echo json_encode(["status" => "success"]);
            } else {
                // Delete the file from directory is face error when creating record in database
                unlink($target_path);
                throw new Exception("Fail to update record in Users database");
            }

            $stmt_update->close();
        } catch(Exception $e) {
            error_log("Fail to update profile: " . $e->getMessage());
            echo json_encode(["status"=> "error"]);
        }
    } else{
        try{
            $sql_update = "UPDATE Users SET username = ?, email = ? WHERE user_id = ?";
            $stmt_update = $db_connect->prepare($sql_update);
            $stmt_update->bind_param("ssi", $username, $email, $user_id);

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
    }
?>