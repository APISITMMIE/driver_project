<?php
session_start();

if (isset($_GET['task_id'])) {
    $taskId = $_GET['task_id'];

    if (isset($_SESSION['authorized_task_ids']) && in_array($taskId, $_SESSION['authorized_task_ids'])) {
        echo 'authorized';
    } else {
        echo 'not_authorized';
    }
} else {
    echo 'not_authorized';
}
?>
