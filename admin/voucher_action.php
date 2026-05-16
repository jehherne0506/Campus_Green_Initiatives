<?php
    include("../conn.php");
    include("../auth.php");

    if(!isset($_SESSION['role']) || $_SESSION['role'] !== "ADMIN"){
        echo json_encode(["status"=> "error"]);
        exit();
    };

    if($_SERVER["REQUEST_METHOD"] !== "POST"){
        echo json_encode(["status"=> "error"]);
        exit();
    }

    $action = $_POST["action"] ?? "";

    try{
        if($action === "edit"){
            if(empty($_POST["editId"]) || empty($_POST["editTitle"]) || empty($_POST["editPoints"]) || empty($_POST["editDescription"])) {
                throw new Exception("Missing required fields.");
            }

            $id = filter_var($_POST["editId"], FILTER_VALIDATE_INT);
            $points_cost = filter_var($_POST["editPoints"], FILTER_VALIDATE_INT);
            if(!$id || $points_cost === false){ // Put !$id so id with 0 still works
                throw new Exception("Invalid ID or Points.");
            } 

            $title = trim($_POST["editTitle"]);
            $description = trim($_POST["editDescription"]);
            $badge = !empty($_POST["editBadge"]) ? trim($_POST["editBadge"]) : null;

            $target_file = null;
            if(isset($_FILES["editLogo"]) && $_FILES["editLogo"]["error"] === 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];
                $file_mime = mime_content_type($_FILES["editLogo"]["tmp_name"]);
                
                if (!in_array($file_mime, $allowed_types)) {
                    throw new Exception("Invalid image format.");
                }

                $uploaded_dir = "../assets/uploads/";
                $file_name = time() . "_" . basename($_FILES["editLogo"]["name"]);
                $target_file = $uploaded_dir . $file_name;

                if (!move_uploaded_file($_FILES["editLogo"]["tmp_name"], $target_file)) {
                    throw new Exception("Failed to save uploaded image.");
                }
            }

            if($target_file){
                $sql = "UPDATE Vouchers SET title=?, description=?, badge=?, points_cost=?, voucher_image=? WHERE voucher_id=?";
                $stmt = $db_connect->prepare($sql);
                $stmt->bind_param("sssisi", $title, $description, $badge, $points_cost, $target_file, $id);
            } else{
                $sql = "UPDATE Vouchers SET title=?, description=?, badge=?, points_cost=? WHERE voucher_id=?";
                $stmt = $db_connect->prepare($sql);
                $stmt->bind_param("sssii", $title, $description, $badge, $points_cost, $id);
            }

            $stmt->execute();
            $stmt->close();
        } else if($action === "toggle_status"){
            $id = filter_var($_POST["voucher_id"], FILTER_VALIDATE_INT);
            $status = $_POST["status"] ?? "INACTIVE";
            if (!$id || empty($status)){
                throw new Exception("Invalid toggle data.");
            } 

            $sql = "UPDATE Vouchers SET status = ? WHERE voucher_id=?";

            $stmt = $db_connect->prepare($sql);
            $stmt->bind_param("si", $status, $id);
            $stmt->execute();
            $stmt->close();

        } else if($action === "delete"){
            $id = filter_var($_POST["voucher_id"], FILTER_VALIDATE_INT);
            if(!$id){
                throw new Exception("Invalid Voucher ID to delete.");
            }

            $sql = "DELETE FROM Vouchers WHERE voucher_id=?";
            $stmt = $db_connect->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        } else{
            echo json_encode(["status"=> "error"]);
            exit();
        }
        echo json_encode(["status" => "success"]);
    } catch(Exception $e) {
        error_log("Failed to take action on voucher: " . $e->getMessage());
        echo json_encode(["status"=> "error"]);
    }
?>