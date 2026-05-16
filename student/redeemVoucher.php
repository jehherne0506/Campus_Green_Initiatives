<?php
    include "../conn.php";
    include("../auth.php");

    if(!isset($_SESSION['role']) || $_SESSION['role'] !== "STUDENT"){
        echo json_encode(["status"=> "error"]);
        exit();
    }

    $user_id = $_SESSION["user_id"];

    $data = json_decode(file_get_contents("php://input"), true);

    if(!isset($data["voucher_id"]) || !filter_var($data['voucher_id'], FILTER_VALIDATE_INT)){
        echo json_encode(["status"=> "error"]);
        exit();
    }

    try{
        $db_connect->begin_transaction();
        $user_sql = "SELECT current_points FROM Users WHERE user_id = ?";
        $stmt1 = $db_connect->prepare($user_sql);
        $stmt1->bind_param("i", $user_id);
        $stmt1->execute();
        $user_result = $stmt1->get_result();

        $stmt1->close();
        if($user_result && $user_result->num_rows > 0){
            $user_row = $user_result->fetch_assoc();
            $user_points = $user_row["current_points"];
        } else{
            throw new Exception("Fail to fetch user points.");
        }

        
        $voucher_sql = "SELECT points_cost FROM Vouchers WHERE voucher_id = ?";
        $stmt2 = $db_connect->prepare($voucher_sql);
        $stmt2->bind_param("i", $data["voucher_id"]);
        $stmt2->execute();
        $voucher_result = $stmt2->get_result();

        $stmt2->close();
        if($voucher_result && $voucher_result->num_rows > 0){
            $voucher_row = $voucher_result->fetch_assoc();
            $voucher_cost = $voucher_row["points_cost"];
        } else{
            throw new Exception("Fail to fetch voucher points cost.");
        }

        if($user_points < $voucher_cost){
            throw new Exception("Insufficient points to redeem voucher.");
        }

        $updated_points = $user_points - $voucher_cost;
        $deduct_points_sql = "UPDATE Users SET current_points = ? WHERE user_id = ?";
        $stmt2 = $db_connect->prepare($deduct_points_sql);
        $stmt2->bind_param("ii", $updated_points, $user_id);
        $stmt2->execute();
        $stmt2->close();

        $voucher_unique_code = strtoupper(uniqid('ECO-'));
        $redeem_voucher_sql = "INSERT INTO Voucher_Claims (voucher_id, user_id, unique_code, expires_at) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 MONTH))";
        $stmt3 = $db_connect->prepare($redeem_voucher_sql);
        $stmt3->bind_param("iis", $data['voucher_id'], $user_id, $voucher_unique_code);
        $stmt3->execute();
        $stmt3->close();

        // Only save in database if all steps work
        $db_connect->commit();

        echo json_encode(["status"=> "success"]);
    } catch(Exception $e){
        $db_connect->rollBack();
        error_log("Fail to redeem voucher: " . $e->getMessage());
        echo json_encode(["status"=> "error"]);
        exit();
    }
?>