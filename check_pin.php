<<<<<<< HEAD
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

        $_SESSION['pin'] = $pin;
        echo "boss|$bossName";
    } else {
        $sqlUser = "SELECT username FROM dv_users WHERE pin = ?";
        $stmtUser = $conn->prepare($sqlUser);
        $stmtUser->bind_param("s", $pin);
        $stmtUser->execute();
        $resultUser = $stmtUser->get_result();

        if ($resultUser->num_rows > 0) {
            $rowUser = $resultUser->fetch_assoc();
            $userName = $rowUser['username'];
            $taskId = $_POST['taskId'];

            $updateSql = "UPDATE dv_tasks SET carUser = ?, pin = ? WHERE task_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ssi", $userName, $pin, $taskId);
            $updateStmt->execute();

            $_SESSION['pin'] = $pin;
            echo "user|$userName";
        } else {

            $taskId = $_POST['taskId'];
            $updateSql = "UPDATE dv_tasks SET carUser = ?, pin = ? WHERE task_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ssi", $pin, $pin, $taskId);
            $updateStmt->execute();

            $_SESSION['pin'] = $pin;
            echo "pin|$pin";
        }
    }
} else {
    echo "fail";
}
?>
=======
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

        $_SESSION['pin'] = $pin;
        echo "boss|$bossName";
    } else {
        $sqlUser = "SELECT username FROM dv_users WHERE pin = ?";
        $stmtUser = $conn->prepare($sqlUser);
        $stmtUser->bind_param("s", $pin);
        $stmtUser->execute();
        $resultUser = $stmtUser->get_result();

        if ($resultUser->num_rows > 0) {
            $rowUser = $resultUser->fetch_assoc();
            $userName = $rowUser['username'];
            $taskId = $_POST['taskId'];

            $updateSql = "UPDATE dv_tasks SET carUser = ?, pin = ? WHERE task_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ssi", $userName, $pin, $taskId);
            $updateStmt->execute();

            $_SESSION['pin'] = $pin;
            echo "user|$userName";
        } else {

            $taskId = $_POST['taskId'];
            $updateSql = "UPDATE dv_tasks SET carUser = ?, pin = ? WHERE task_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ssi", $pin, $pin, $taskId);
            $updateStmt->execute();

            $_SESSION['pin'] = $pin;
            echo "pin|$pin";
        }
    }
} else {
    echo "fail";
}
?>
>>>>>>> e1c9b9236d4ed67f1dee5d6544511c0824532c22
