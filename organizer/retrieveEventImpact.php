<?php
    include("../conn.php");
    include("../auth.php");

    if(!isset($_SESSION['role']) || $_SESSION['role'] !== "ORGANIZER"){
        header("Location: ../login/login.php");
        exit();
    };

    $data = json_decode(file_get_contents("php://input"), true);

    if(!isset($data["event_id"]) || !filter_var($data['event_id'], FILTER_VALIDATE_INT)){
        echo json_encode(["status"=> "error"]);
        exit();
    }

    $event_id = $data['event_id'];
    $user_id = $_SESSION["user_id"];

    $events_stats = [];
    $total_student_registered = 0;
    $total_attendance = 0;
    $total_attendance_rate = 0;
    $total_volunteer_hours = 0;

    $events_basic_data = [];

    $target_trees = 100;
    $target_waste = 500;
    $actual_trees = 0;
    $actual_waste = 0;
    $total_impact_score = 0;
    
    try{
        $events_sql = "SELECT ep.*, e.time_start, e.time_end FROM Event_Participants ep JOIN Events e ON ep.event_id = e.event_id WHERE ep.event_id = ?";
        $stmt1 = $db_connect->prepare($events_sql);
        $stmt1->bind_param("i", $event_id);
        $stmt1->execute();
        $events_result = $stmt1->get_result();
        
        while($row = $events_result->fetch_assoc()){
            $events_stats[] = $row;
        }

        if(count($events_stats) > 0){
            $total_student_registered = count($events_stats);
            foreach($events_stats as $row){
                if($row["attendance_status"] === "PRESENT"){
                    $total_attendance ++;

                    $start = strtotime($row['time_start']);
                    $end = strtotime($row['time_end']);
                    $hours_diff = ($end - $start) / 3600;
                    $total_volunteer_hours += $hours_diff;
                    
                }
            }
            $total_attendance_rate = round($total_attendance / $total_student_registered * 100);
         }
         
         $stmt1->close();

         $sql_events_goals = "SELECT title, event_id, COALESCE(trees_planted, 0) AS trees_planted, COALESCE(waste_collected, 0) AS waste_collected FROM Events WHERE event_id = ?";
         $stmt2 = $db_connect->prepare($sql_events_goals);
         $stmt2->bind_param("i", $event_id);
         $stmt2->execute();
         $goals_result = $stmt2->get_result();
         while($row = $goals_result->fetch_assoc()){
            $actual_trees += $row["trees_planted"];
            $actual_waste += $row["waste_collected"];

            $events_basic_data[] = $row;
        }

        $tree_score = $actual_trees / $target_trees * 5;
        $waste_score = $actual_waste / $target_waste * 5;
        $total_impact_score = min(10, round($tree_score + $waste_score, 1));

        $participant_sql = "SELECT u.username, ep.date_joined, ep.attendance_status, e.time_start, e.time_end FROM Event_Participants ep JOIN Users u ON ep.user_id = u.user_id JOIN Events e ON ep.event_id = e.event_id WHERE e.event_id = ? ORDER BY ep.date_joined DESC";
        $stmt3 = $db_connect->prepare($participant_sql);
        $stmt3->bind_param("i", $event_id);
        $stmt3->execute();
        $participant_result = $stmt3->get_result();
        if(!$participant_result){
            throw new Exception("Failed to fetch participant data.");
        }
        $participants = $participant_result->fetch_all(MYSQLI_ASSOC);

        echo json_encode([
            "status" => "success", 
            "registered" => $total_student_registered,
            "attendanceRate" => $total_attendance_rate,
            "volunteerHours" => $total_volunteer_hours,
            "impactScore" => $total_impact_score,
            "treesPlanted" => $actual_trees,
            "wasteCollected" => $actual_waste,
            "participantResult" => $participants
        ]);

    } catch(Exception $e){
        error_log("Failed to fetch all organizer's events data: " . $e->getMessage());
        echo json_encode(["status"=> "error"]);
    }
?>