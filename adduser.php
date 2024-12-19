<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

include('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = 'user';

    $sql = "INSERT INTO dv_users (username, password, role) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $password, $role);

    if ($stmt->execute()) {
        echo "<script>
                alert('บันทึกข้อมูลสำเร็จ Success');
                window.location.href = ('adminCar.php');
            </script>";
        header("Location: adminUser.php"); 
        exit;
    } else {
        echo "เกิดข้อผิดพลาดในการเพิ่มข้อมูล.";
    }
} else {
    echo "กรุณากรอกข้อมูลให้ครบถ้วน.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มข้อมูลรถ</title>
    <link rel="stylesheet" href="layout/adminUser.css">
    <!-- เชื่อมต่อ Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* ฟอร์มการเพิ่มข้อมูลรถ */
        .table-container {
            width: 80%;
            margin-left: 15%;
            margin-top: 30px;
            background-color: #f4f4f4;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            font-size: 28px;
            color: #333;
            margin-bottom: 20px;
        }

        form {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        label {
            font-size: 16px;
            color: #555;
            margin-bottom: 10px;
            display: block;
            font-weight: bold;
        }

        input[type="text"], input[type="password"], select {
            width: 100%;
            padding: 10px;
            margin: 5px 0 20px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box; 
        }

        input[type="text"]:focus, input[type="password"]:focus, select:focus {
            outline: none;
            border-color: #007bff;
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: transparent;
            color: #007bff;
            padding: 12px 20px;
            border: 1px solid #007bff ;
            border-radius: 30px;
            cursor: pointer;
            font-size: 18px;
        }

        input[type="submit"]:hover {
            background-color: #007bff;
            color: white;
        }

        form div {
            margin-bottom: 15px;
        }

        body {
            font-family: Arial, sans-serif;
            color: #333;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        /* ไอคอนตา */
        .eye-icon {
            position: absolute;
            right: 10px;
            top: 30%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            color: #555;
        }

        .password-container {
            position: relative;
            width: 100%;
        }

        .eye-icon:hover {
            color: #007bff;
        }

        /* responsive */
        @media (max-width: 600px) {
        .table-container {
            width: 90%;
        }

        form {
            width: 100%;
            padding: 15px;
        }

        input[type="submit"] {
            font-size: 16px;
        }
    }
    </style>
</head>
<body>
    <!-- Header Section -->
    <header>
        <div class="logoo">
            <img src="assets/bandologo.png" alt="Logo"> 
        </div>
        <div class="header_right">
            <span>Welcome, <?php echo $_SESSION['username']; ?></span>
            <a href="logout.php" class="logoutBt">Logout</a>
        </div>
    </header>

    <!-- Sidebar Section -->
    <div class="sidebar">
        <ul>
            <li><a href="admin.php">Dashboard</a></li>
            <li><a href="adminUser.php">Manage Users</a></li>
            <li><a href="adminCar.php">Manage Cars</a></li>
            <li><a href="report.php">Reports</a></li>
        </ul>
    </div>

    <!-- Main Section -->
    <div class="table-container">
        <h2>เพิ่มข้อมูลผู้ใช้ใหม่</h2>

        <!-- ฟอร์มเพิ่มข้อมูลรถ -->
        <form action="adduser.php" method="POST">
            <label for="username">ชื่อผู้ใช้</label>
            <input type="text" id="username" name="username"  required><br><br>

            <label for="password">รหัสผ่าน</label>
            <div class="password-container">
                <input type="password" id="password" name="password" required><br><br>
                <span class="eye-icon" id="togglePassword" onclick="togglePassword()">
                    <i class="fas fa-eye"></i> 
                </span>
            </div>

            <input type="submit" value="เพิ่มข้อมูล">
        </form>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('togglePassword').querySelector('i');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.classList.remove('fa-eye');  
                eyeIcon.classList.add('fa-eye-slash'); 
            } else {
                passwordField.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash'); 
                eyeIcon.classList.add('fa-eye'); 
            }
        }
    </script>
</body>
</html>


