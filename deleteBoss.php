<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include('config.php');

if (isset($_GET['id'])) {
    $boss_id = $_GET['id'];
    $sql = "DELETE FROM dv_boss WHERE boss_id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $boss_id);
        if ($stmt->execute()) {
            header("Location: adminBoss.php");
            exit;
        } else {
            echo "Eror: " . $stmt->error;
        }
    } else {
        echo "Error: " . $conn->error;
    }
}
$conn->close();
?>