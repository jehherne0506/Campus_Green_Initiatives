<?php
    include "../conn.php";
    include("../auth.php");

    if(!isset($_SESSION['role']) || $_SESSION['role'] !== "ORGANIZER"){
        echo json_encode(["status"=> "error"]);
        exit();
    }

    $data = json_decode(file_get_contents("php://input"), true);

    if(!isset($data['event_id']) || !filter_var($data['event_id'], FILTER_VALIDATE_INT)){
        echo json_encode(["status" => "error"]);
        exit();
    }

    $event_id = $data['event_id'];

    try{
        /* COALESCE replace null value with another value, 0 in this case */
        $stats_sql = "SELECT COUNT(*) AS total_count, COALESCE(SUM(CASE WHEN attendance_status = 'PENDING' THEN 1 ELSE 0 END), 0) as pending_count, COALESCE(SUM(CASE WHEN attendance_status = 'PRESENT' OR attendance_status = 'ABSENT' THEN 1 ELSE 0 END), 0) as completed_count FROM Event_Participants WHERE event_id = ?";
        $stmt1 = $db_connect->prepare($stats_sql);
        $stmt1->bind_param("i", $event_id);
        $stmt1->execute();
        $stats_result = $stmt1->get_result();

        if(!$stats_result){
            throw new Exception("Failed to get attendance status.");
        }

        $stats_row = $stats_result->fetch_assoc();
        $stmt1->close();

        $pending_approval_sql = "SELECT Event_Participants.*, Users.user_id, Users.username, Users.email, Users.avatar FROM Event_Participants JOIN Users ON Event_Participants.user_id = Users.user_id WHERE event_id = ? AND attendance_status = 'PENDING' AND proof_image IS NOT NULL AND description IS NOT NULL";
        $stmt2 = $db_connect->prepare($pending_approval_sql);
        $stmt2->bind_param("i", $event_id);
        $stmt2->execute();
        $pending_approval_result = $stmt2->get_result();

        if(!$pending_approval_result){
            throw new Exception("Failed to get pending approvals.");
        }

        $pending_approval_list = $pending_approval_result->fetch_all(MYSQLI_ASSOC);
        $stmt2->close();

        $all_approval_sql = "SELECT Event_Participants.*, Users.username, Users.email FROM Event_Participants JOIN Users ON Event_Participants.user_id = Users.user_id WHERE event_id = ?";
        $stmt3 = $db_connect->prepare($all_approval_sql);
        $stmt3->bind_param("i", $event_id);
        $stmt3->execute();
        $all_approval_result = $stmt3->get_result();

        if(!$all_approval_result){
            throw new Exception("Failed to get all approval's result.");
        }

        $all_approval_list = $all_approval_result->fetch_all(MYSQLI_ASSOC);
        $stmt3->close();

        echo json_encode(["status" => "success", 
            "stats" => ["total" => $stats_row["total_count"], "pending" => $stats_row["pending_count"], "completed" => $stats_row["completed_count"]],
            "pending_approval" => $pending_approval_list,
            "all_approval" => $all_approval_list
        ]);
    } catch(Exception $e) {
        error_log("Failed to get event data: ". $e->getMessage());
    }
?>