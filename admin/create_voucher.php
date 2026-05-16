<?php
    include("../conn.php");
    include("../auth.php");

    if(!isset($_SESSION['role']) || $_SESSION['role'] !== "ADMIN"){
        echo json_encode(["status"=> "error"]);
        exit();
    };

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        if(!isset($_POST["title"]) || !isset($_POST["points"]) || !isset($_POST["badge"]) || !isset($_POST["description"]) || !isset($_FILES["logo"])){
           echo json_encode(["status"=> "error"]);
           exit(); 
        }

        $title = trim($_POST["title"]);
        $badge = !empty($_POST["badge"]) ? trim($_POST["badge"]) : null;
        $description = trim($_POST["description"]);

        $points_cost = filter_var($_POST["points"], FILTER_VALIDATE_INT);
        if(!$points_cost){
            echo json_encode(["status"=> "error"]);
            exit();
        }

        $logo = $_FILES["logo"];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];
        $file_mime = mime_content_type($logo["tmp_name"]);

        if(!in_array($file_mime, $allowed_types) || $logo["error"] !== 0) {
            echo json_encode(["status"=> "error"]);
            exit();
        }

        $uploaded_dir = "../assets/uploads/";
        $file_name = time() . "_" . basename($logo["name"]); /* Get only the file name */
        $target_file = $uploaded_dir . $file_name;

        try{
            if(!move_uploaded_file($logo["tmp_name"], $target_file)){
                throw new Exception("Failed to save uploaded file to directory");
            }

            $sql = "INSERT INTO Vouchers (title, description, badge, voucher_image, points_cost) VALUES (?, ?, ?, ?, ?)";
            $stmt = $db_connect->prepare($sql);
            $stmt->bind_param("ssssi", $title, $description, $badge, $target_file, $points_cost);

            if($stmt->execute()){
                echo json_encode(["status" => "success"]);
            } else {
                // Delete the file from directory is face error when creating record in database
                unlink($target_file);
                throw new Exception("Fail to create record in Vouchers database");
            }

            $stmt->close();
        } catch(Exception $e) {
            error_log("Fail to create voucher: " . $e->getMessage());
            echo json_encode(["status"=> "error"]);
        }
    }
?>