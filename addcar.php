<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

include('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carName = $_POST['carName'];
    $carMileage = $_POST['carMileage'];
    $carStatus = $_POST['carStatus'];

    if (!empty($carName) && !empty($carMileage)) {
        $sql = "INSERT INTO dv_car (carName, carMileage, carStatus) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $carName, $carMileage, $carStatus);

        if ($stmt->execute()) {
            echo "<script>
                    alert('บันทึกข้อมูลสำเร็จ Success');
                    window.location.href = ('adminCar.php');
                </script>";
            header("Location: adminCar.php"); 
            exit;
        } else {
            echo "เกิดข้อผิดพลาดในการเพิ่มข้อมูล.";
        }
    } else {
        echo "กรุณากรอกข้อมูลให้ครบถ้วน.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มข้อมูลรถ</title>
    <link rel="stylesheet" href="layout/adminUser.css">
    <style>
        /* ฟอร์มการเพิ่มข้อมูลรถ */
        .table-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
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
            width: 35%;
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

        input[type="text"], select {
            width: 100%;
            padding: 10px;
            margin: 5px 0 20px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }

        input[type="text"]:focus, select:focus {
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

        input[type="text"], select {
            box-sizing: border-box;
        }

        .back-btn {
            margin-top: 20px;
            width: 35%;
            padding: 12px;
            background-color: transparent;
            color: #929191;
            padding: 12px 20px;
            border: 1px solid #929191 ;
            border-radius: 30px;
            cursor: pointer;
            font-size: 18px;
        }

        .back-btn:hover {
            background-color: #929191;
            color: white;
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
            <li><a href="adminBoss.php">Manage Boss</a></li>
            <li><a href="report.php">Reports</a></li>
            <li><a href="report_boss.php">Report Boss</a></li>
            <li><a href="report_driver.php">Report Diver</a></li>
            <li><a href="weekly_report.php">Weekly Report</a></li>
        </ul>
    </div>

    <!-- Main Section -->
    <div class="table-container">
        <h2>เพิ่มข้อมูลรถใหม่</h2>

        <!-- ฟอร์มเพิ่มข้อมูลรถ -->
        <form action="addCar.php" method="POST">
            <label for="carName">ข้อมูลรถ(ยี่ห้อ + เลขทะเบียน):</label>
            <input type="text" id="carName" name="carName" placeholder="เช่น Honda กค4365" required><br><br>

            <label for="carMileage">เลขไมล์ล่าสุด:</label>
            <input type="text" id="carMileage" name="carMileage" required><br><br>

            <label for="carStatus">สถานะ:</label>
            <select name="carStatus" id="carStatus">
                <option value="1">พร้อมใช้งาน</option>
                <option value="0">ไม่พร้อมใช้งาน</option>
            </select><br><br>

            <input type="submit" value="เพิ่มข้อมูล">
        </form>
        <button onclick="history.back()" class="back-btn">Go Back</button>
    </div>
</body>
</html>
