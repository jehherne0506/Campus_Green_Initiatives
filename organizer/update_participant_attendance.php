<?php
    include "../conn.php";
    include("../auth.php");

    if(!isset($_SESSION['role']) || $_SESSION['role'] !== "ORGANIZER"){
        echo json_encode(["status"=> "error"]);
        exit();
    }

    $data = json_decode(file_get_contents("php://input"), true);

    if(!isset($data["user_id"], $data['event_id'], $data['action']) || !filter_var($data['user_id'], FILTER_VALIDATE_INT) || !filter_var($data['event_id'], FILTER_VALIDATE_INT)){
        echo json_encode(["status"=> "error"]);
        exit();
    }

    $user_id = $data["user_id"];
    $event_id = $data['event_id'];
    $action = strtolower(trim($data['action']));

    $status = "";
    if($action === "approve"){
        $status = "PRESENT";
    } else if($action === "reject"){
        $status = "ABSENT";
    } else{
        echo json_encode(["status"=> "error"]);
        exit();
    }

    try{
        $sql_attendance = "UPDATE Event_Participants SET attendance_status = ? WHERE user_id = ? AND event_id = ?";
        $stmt1 = $db_connect->prepare($sql_attendance);
        $stmt1->bind_param("sii", $status, $user_id, $event_id );
        if(!($stmt1->execute())){
            throw new Exception("Failed to update participant attendance.");
        }

        $stmt1->close();

        if($status === "PRESENT"){
            $sql_user = "SELECT current_points, total_volunteer_hours FROM Users WHERE user_id = ?";
            $stmt2 = $db_connect->prepare($sql_user);
            $stmt2->bind_param("i", $user_id);
            $stmt2->execute();
            $user_result = $stmt2->get_result();
            if(!$user_result){
            throw new Exception("Failed to fetch user data."); 
            }

            $user_data = $user_result->fetch_assoc();
            $stmt2->close();

            $sql_hours = "SELECT time_start, time_end FROM Events WHERE event_id = ?";
            $stmt3 = $db_connect->prepare($sql_hours);
            $stmt3->bind_param("i", $event_id);
            $stmt3->execute();
            $hours_result = $stmt3->get_result();
            if(!$hours_result){
            throw new Exception("Failed to fetch user data."); 
            }

            $hours_data = $hours_result->fetch_assoc();
            $stmt3->close();

            $user_points = $user_data["current_points"];
            $user_volunteer_hours = $user_data["total_volunteer_hours"];
            $start = strtotime($hours_data['time_start']);
            $end = strtotime($hours_data['time_end']);
            $hours_diff = ($end - $start) / 3600;
            $updated_points = $user_points + ($hours_diff * 100);
            $updated_volunteer_hours = $user_volunteer_hours + $hours_diff;

            $sql_points = "UPDATE Users SET current_points = ?, total_volunteer_hours = ? WHERE user_id = ?";
            $stmt4 = $db_connect->prepare($sql_points);
            $stmt4->bind_param("idi", $updated_points, $updated_volunteer_hours, $user_id);
            $stmt4->execute();
        }

        echo json_encode(["status" => "success"]);
    } catch(Exception $e){
        error_log("Attendance Update Error: ". $e->getMessage());
        echo json_encode(["status"=> "error"]);
    }
?>