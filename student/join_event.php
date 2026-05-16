<?php
    include "../conn.php";
    include("../auth.php");

    if(!isset($_SESSION['role']) || $_SESSION['role'] !== "STUDENT"){
        echo json_encode(["status"=> "error"]);
        exit(); 
    }

    $user_id = $_SESSION["user_id"];

    $data = json_decode(file_get_contents("php://input"), true);

    if(!isset($data['event_id']) || !filter_var($data['event_id'], FILTER_VALIDATE_INT)){
        echo json_encode(["status" => "error"]);
        exit();
    }

    $event_id = $data['event_id'];

    try{
        $sql = "INSERT INTO Event_Participants (event_id, user_id) VALUES (?,?)";
        $stmt = $db_connect->prepare($sql);
        $stmt->bind_param("ii", $event_id, $user_id);
        $result = $stmt->execute();

        if($result){
            echo json_encode(["status" => "success"]);
        } else{
            throw new Exception("Failed to insert data into Events databases.");
        }

        $stmt->close();
    } catch(Exception $e){
        error_log("Failed to join event" . $e->getMessage());
        echo json_encode(["status" => "error"]);
    }
?>