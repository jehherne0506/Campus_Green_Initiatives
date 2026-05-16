<?php 
include "../conn.php";
include("../auth.php");

header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => 'Unknown Error'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_SESSION['user_id'];

    if ($action === 'save') {
        $id = $_POST['eventId'] ?? '';
        $title = $_POST['title'];
        $desc = $_POST['description'];
        $loc = $_POST['location'];
        $date = $_POST['date'];
        $start = $_POST['start'];
        $end = $_POST['end'];
        $max = $_POST['max_participants'];
        $cat = $_POST['category'];
        $event_image = $_FILES['event_image'] ?? null;

        if (!empty($id)) {
            if($event_image && $event_image['error'] === 0){
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];
                $file_mime = mime_content_type($event_image["tmp_name"]);

                if(!in_array($file_mime, $allowed_types) || $event_image["error"] !== 0) {
                    echo json_encode(["status"=> "error"]);
                    exit();
                }

                $uploaded_dir = "../assets/uploads/";
                $file_name = time() . "_" . basename($event_image["name"]); /* Get only the file name */
                $target_file = $uploaded_dir . $file_name;

                if(!move_uploaded_file($event_image["tmp_name"], $target_file)){
                    echo json_encode(["status"=> "error"]);
                    exit();
                }

                $sql = "UPDATE events SET title=?, description=?, location=?, event_date=?, time_start=?, time_end=?, event_image=?, max_participants=?, category_id=? WHERE event_id=? AND organizer_id=?";
                $stmt = mysqli_prepare($db_connect, $sql);

                mysqli_stmt_bind_param($stmt, "sssssssiiii", $title, $desc, $loc, $date, $start, $end, $target_file, $max, $cat, $id, $user_id);
            } else{
                $sql = "UPDATE events SET title=?, description=?, location=?, event_date=?, time_start=?, time_end=?, max_participants=?, category_id=? WHERE event_id=? AND organizer_id=?";
                $stmt = mysqli_prepare($db_connect, $sql);

                mysqli_stmt_bind_param($stmt, "ssssssiiii", $title, $desc, $loc, $date, $start, $end, $max, $cat, $id, $user_id);
            }
        } else {
            if (!$event_image || $event_image['error'] !== 0) {
                $target_file = "../assets/uploads/default_event.avif";
            }

            else{
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];
                $file_mime = mime_content_type($event_image["tmp_name"]);

                if(!in_array($file_mime, $allowed_types) || $event_image["error"] !== 0) {
                    echo json_encode(["status"=> "error"]);
                    exit();
                }

                $uploaded_dir = "../assets/uploads/";
                $file_name = time() . "_" . basename($event_image["name"]); /* Get only the file name */
                $target_file = $uploaded_dir . $file_name;

                if(!move_uploaded_file($event_image["tmp_name"], $target_file)){
                    echo json_encode(["status"=> "error"]);
                    exit();
                }
            }

            $sql = "INSERT INTO events (title, description, location, event_date, time_start, time_end, event_image, max_participants, organizer_id, category_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDING')";
            $stmt = mysqli_prepare($db_connect, $sql);
            mysqli_stmt_bind_param($stmt, "sssssssiii", $title, $desc, $loc, $date, $start, $end, $target_file, $max, $user_id, $cat);
        }

        if (mysqli_stmt_execute($stmt)) {
            $response = ['status' => 'success', 'message' => 'Action successful'];
        } else {
            $response = ['status' => 'error', 'message' => mysqli_error($db_connect)];
        }
    }
    
    if ($action === 'delete') {
        $id = $_POST['eventId'];
        $user_id = $_SESSION['user_id'];

        // Secure delete: Only delete if ID matches AND current user is the owner
        $sql = "DELETE FROM events WHERE event_id = ? AND organizer_id = ?";
        $stmt = mysqli_prepare($db_connect, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                $response = ['status' => 'success'];
            } else {
                $response = [
                    'status' => 'error', 
                    'message' => "No matching event found. Check if Organizer ID in DB matches your User ID ($user_id)."
                ];
            }
        } else {
            $response = ['status' => 'error', 'message' => mysqli_error($db_connect)];
        }
    }

    if ($action === 'set_impact') {
        $id = $_POST['eventId'];
        $trees = (int)$_POST['trees_planted'];

        $waste = (float)$_POST['waste_collected']; 
        $user_id = $_SESSION['user_id'];

        $sql = "UPDATE events SET trees_planted = ?, waste_collected = ?, status = 'COMPLETED' WHERE event_id = ? AND organizer_id = ?";
        $stmt = mysqli_prepare($db_connect, $sql);

        mysqli_stmt_bind_param($stmt, "idii", $trees, $waste, $id, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            $response = ['status' => 'success', 'message' => 'Impact statistics updated!'];
        } else {
            $response = ['status' => 'error', 'message' => mysqli_error($db_connect)];
        }
    }
}

echo json_encode($response);