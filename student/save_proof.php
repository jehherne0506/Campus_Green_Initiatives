<?php
    include "../conn.php";
    include "../auth.php";

    if(!isset($_SESSION['role']) || $_SESSION['role'] !== "STUDENT"){
        header("Location: ../login/login.php");
        exit();
    }

    $user_id = $_SESSION["user_id"];

    if($_SERVER["REQUEST_METHOD"] === "POST"){
        if(!isset($_POST["event_id"]) || !isset($_POST["description"]) || !isset($_FILES["proof_image"])){
           echo json_encode(["status"=> "error"]);
           exit(); 
        }

        $event_id = filter_var($_POST["event_id"], FILTER_VALIDATE_INT);
        $description = trim($_POST["description"]);

        if(!$event_id){
            echo json_encode(["status"=> "error"]);
            exit();
        }

        $proof_image = $_FILES["proof_image"];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];
        $file_mime = mime_content_type($proof_image["tmp_name"]);

        if(!in_array($file_mime, $allowed_types) || $proof_image["error"] !== 0) {
            echo json_encode(["status"=> "error"]);
            exit();
        }

        $uploaded_dir = "../assets/uploads/";
        $file_name = time() . "_" . basename($proof_image["name"]); /* Get only the file name */
        $target_file = $uploaded_dir . $file_name;

        try{
            if(!move_uploaded_file($proof_image["tmp_name"], $target_file)){
                throw new Exception("Failed to save uploaded file to directory");
            }

            $sql = "UPDATE Event_Participants SET description = ?, proof_image = ?, attendance_status = 'PENDING' WHERE event_id = ? AND user_id = ?";
            $stmt = $db_connect->prepare($sql);
            $stmt->bind_param("ssii", $description, $target_file, $event_id, $user_id);

            if($stmt->execute()){
                echo json_encode(["status" => "success"]);
            } else {
                // Delete the file from directory is face error when creating record in database
                unlink($target_file);
                throw new Exception("Fail to update record in Event Participants database");
            }

            $stmt->close();
        } catch(Exception $e) {
            error_log($e->getMessage());    
            echo json_encode(["status"=> "error"]);
        }
    }
?>