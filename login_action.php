<?php
session_start();

include('config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM dv_users WHERE username='$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();  
        if ($password == $row['password']) {
            $_SESSION['username'] = $row['username'];

            if ($row['role'] == 'admin') {
                header("Location: admin.php"); 
            } else {
                header("Location: tasklist.php"); 
            }
            exit();
        } else {
            header("Location: login.php?error=รหัสผ่านไม่ถูกต้อง");
            exit();
        }
    } else {
        header("Location: login.php?error=ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง");
        exit();
    }
    $conn->close();
}
?>
