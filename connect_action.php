<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

include('config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $driver_name = $_POST['driver_name'];  
    $car_id = $_POST['car_id'];  

    if (empty($driver_name) || empty($car_id)) {
        echo "กรุณากรอกข้อมูลให้ครบถ้วน!";
        exit;
    }

    $sql_user_id = "SELECT user_id FROM dv_users WHERE username = ?";
    $stmt_user = $conn->prepare($sql_user_id);
    $stmt_user->bind_param("s", $driver_name);
    $stmt_user->execute();
    $stmt_user->store_result();

    if ($stmt_user->num_rows > 0) {
        $stmt_user->bind_result($user_id);
        $stmt_user->fetch();
        $sql_insert_update = "
            INSERT INTO dv_driver_car (driver_id, car_id) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE car_id = ?";
        $stmt_insert_update = $conn->prepare($sql_insert_update);
        $stmt_insert_update->bind_param("iii", $user_id, $car_id, $car_id); 

        if ($stmt_insert_update->execute()) {
            header("Location: admin.php"); 
            exit;
        } else {
            echo "เกิดข้อผิดพลาดในการบันทึกข้อมูล!";
        }

        $stmt_insert_update->close();
    } else {
        echo "ไม่พบข้อมูลผู้ขับขี่!";
    }

    $stmt_user->close();
    $conn->close();
}
?>
