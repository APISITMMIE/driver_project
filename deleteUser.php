<<<<<<< HEAD
<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include('config.php');

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $sql = "DELETE FROM dv_users WHERE user_id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            header("Location: adminUser.php");
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error: " . $conn->error;
    }
}
$conn->close();
?>
=======
<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include('config.php');

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $sql = "DELETE FROM dv_users WHERE user_id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            header("Location: adminUser.php");
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error: " . $conn->error;
    }
}
$conn->close();
?>
>>>>>>> e1c9b9236d4ed67f1dee5d6544511c0824532c22
