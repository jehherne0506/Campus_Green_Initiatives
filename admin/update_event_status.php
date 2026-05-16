<?php
require_once "../conn.php"; 
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN') {
    $id = $_POST['event_id'];
    $status = $_POST['status'];

    $conn = $db_connect; 

    $stmt = $conn->prepare("UPDATE events SET status = ? WHERE event_id = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    $stmt->close();
    exit;
}
echo json_encode(['success' => false, 'message' => 'Unauthorized or Invalid Request']);