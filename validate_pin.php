<?php
session_start();
include('config.php');

if (isset($_POST['pin']) && isset($_POST['task_id']) ) {
    $pin = $_POST['pin'];
    $taskId = $_POST['task_id'];

    $sql = "SELECT * FROM dv_boss WHERE pin = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s" , $pin);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "success"; 
    } else {
        echo "error"; 
    }
}
?>
