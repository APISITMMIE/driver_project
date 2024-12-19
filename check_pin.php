<?php
session_start();
include('config.php');

if (isset($_POST['pin'])) {
    $pin = $_POST['pin'];

    $sql = "SELECT boss_name FROM dv_boss WHERE pin = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $pin);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $bossName = $row['boss_name'];
        $taskId = $_POST['taskId'];

        $updateSql = "UPDATE dv_tasks SET carUser = ?, pin = ? WHERE task_id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ssi", $bossName, $pin, $taskId);
        $updateStmt->execute();

        echo "boss|$bossName";
    } else {
        $taskId = $_POST['taskId'];
        $updateSql = "UPDATE dv_tasks SET carUser = ?, pin = ? WHERE task_id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ssi", $pin, $pin, $taskId);
        $updateStmt->execute();

        echo "pin|$pin";
    }
} else {
    echo "fail";
}
?>
