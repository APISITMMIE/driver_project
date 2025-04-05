<?php
session_start();
include('config.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['taskId']) && isset($_POST['latitude']) && isset($_POST['longitude'])) {
    $taskId = $_POST['taskId'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    $sql = "UPDATE dv_tasks SET des_latitude = ?, des_longitude = ? WHERE task_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ddi", $latitude, $longitude, $taskId);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "error";
}
?>
