<<<<<<< HEAD
<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

include('config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['carId'])) {
        $carId = $_POST['carId'];
        $carName = trim(preg_replace('/\s+/', ' ', $_POST['carName']));
        $carMileage = $_POST['carMileage'];
        $carStatus = $_POST['carStatus'];
        $sql = "UPDATE dv_car SET carName = ?, carMileage = ?, carStatus = ? WHERE carId = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssii", $carName, $carMileage, $carStatus, $carId);
            if ($stmt->execute()) {
                echo "<script>
                        alert('บันทึกข้อมูลสำเร็จ Success');
                        window.location.href = ('adminCar.php');
                    </script>";
                exit;
            } else {
                echo "เกิดข้อผิดพลาด: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "เกิดข้อผิดพลาด: " . $conn->error;
        }
    }
}

if (isset($_GET['id'])) {
    $carId = $_GET['id'];
    $sql = "SELECT * FROM dv_car WHERE carId = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $carId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $carName = $row['carName'];
            $carMileage = $row['carMileage'];
            $carStatus = $row['carStatus'];
        } else {
            echo "ไม่พบข้อมูลรถ";
            exit;
        }
        $stmt->close();
    } else {
        echo "Error: " . $conn->error;
        exit;
    }
} else {
    echo "ไม่พบ ID ของรถ";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลรถ</title>
    <link rel="stylesheet" href="layout/adminCar.css">
    <style>
        .table-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 80%;
            margin: 30px auto;
            background-color: #f4f4f4;
            padding: 20px;
            border-radius: 10px;
            margin-left: 15%;
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

        select {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #fff;
            width: 100%;
        }

        form div {
            margin-bottom: 15px;
        }

        input[type="text"], select, input[type="submit"] {
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
        <h2>แก้ไขข้อมูลรถ</h2>
        
        <!-- ฟอร์มแก้ไขข้อมูลรถ -->
        <form action="updateCar.php" method="POST">
            <input type="hidden" name="carId" value="<?php echo $carId; ?>">

            <label for="carName">ชื่อรถ</label>
            <input type="text" id="carName" name="carName" value="<?php echo $carName; ?>" required><br><br>

            <label for="carMileage">เลขไมล์ล่าสุด</label>
            <input type="text" id="carMileage" name="carMileage" value="<?php echo $carMileage; ?>" required><br><br>

            <label for="carStatus">สถานะ</label>
            <select name="carStatus" id="carStatus">
                <option value="1" <?php echo ($carStatus == 1) ? 'selected' : ''; ?>>พร้อมใช้งาน</option>
                <option value="0" <?php echo ($carStatus == 0) ? 'selected' : ''; ?>>ไม่พร้อมใช้งาน</option>
            </select><br><br>

            <input type="submit" value="อัปเดตข้อมูล">
        </form>
        <button onclick="history.back()" class="back-btn">Back</button>
    </div>

</body>
</html>
=======
<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

include('config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['carId'])) {
        $carId = $_POST['carId'];
        $carName = trim(preg_replace('/\s+/', ' ', $_POST['carName']));
        $carMileage = $_POST['carMileage'];
        $carStatus = $_POST['carStatus'];
        $sql = "UPDATE dv_car SET carName = ?, carMileage = ?, carStatus = ? WHERE carId = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssii", $carName, $carMileage, $carStatus, $carId);
            if ($stmt->execute()) {
                echo "<script>
                        alert('บันทึกข้อมูลสำเร็จ Success');
                        window.location.href = ('adminCar.php');
                    </script>";
                exit;
            } else {
                echo "เกิดข้อผิดพลาด: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "เกิดข้อผิดพลาด: " . $conn->error;
        }
    }
}

if (isset($_GET['id'])) {
    $carId = $_GET['id'];
    $sql = "SELECT * FROM dv_car WHERE carId = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $carId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $carName = $row['carName'];
            $carMileage = $row['carMileage'];
            $carStatus = $row['carStatus'];
        } else {
            echo "ไม่พบข้อมูลรถ";
            exit;
        }
        $stmt->close();
    } else {
        echo "Error: " . $conn->error;
        exit;
    }
} else {
    echo "ไม่พบ ID ของรถ";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลรถ</title>
    <link rel="stylesheet" href="layout/adminCar.css">
    <style>
        .table-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 80%;
            margin: 30px auto;
            background-color: #f4f4f4;
            padding: 20px;
            border-radius: 10px;
            margin-left: 15%;
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

        select {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #fff;
            width: 100%;
        }

        form div {
            margin-bottom: 15px;
        }

        input[type="text"], select, input[type="submit"] {
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
        <h2>แก้ไขข้อมูลรถ</h2>
        
        <!-- ฟอร์มแก้ไขข้อมูลรถ -->
        <form action="updateCar.php" method="POST">
            <input type="hidden" name="carId" value="<?php echo $carId; ?>">

            <label for="carName">ชื่อรถ</label>
            <input type="text" id="carName" name="carName" value="<?php echo $carName; ?>" required><br><br>

            <label for="carMileage">เลขไมล์ล่าสุด</label>
            <input type="text" id="carMileage" name="carMileage" value="<?php echo $carMileage; ?>" required><br><br>

            <label for="carStatus">สถานะ</label>
            <select name="carStatus" id="carStatus">
                <option value="1" <?php echo ($carStatus == 1) ? 'selected' : ''; ?>>พร้อมใช้งาน</option>
                <option value="0" <?php echo ($carStatus == 0) ? 'selected' : ''; ?>>ไม่พร้อมใช้งาน</option>
            </select><br><br>

            <input type="submit" value="อัปเดตข้อมูล">
        </form>
        <button onclick="history.back()" class="back-btn">Back</button>
    </div>

</body>
</html>
>>>>>>> e1c9b9236d4ed67f1dee5d6544511c0824532c22
