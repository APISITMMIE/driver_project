<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include('config.php');
$sql_user = "SELECT * FROM dv_users WHERE role = 'user'";
$result_user = $conn->query($sql_user);

$sql_car = "SELECT * FROM dv_car";
$result_car = $conn->query($sql_car);

    // อัพเดตสถานะของรถ
    $sql_update_status = "
        UPDATE dv_car c
        SET c.carStatus = CASE 
            WHEN EXISTS (
                SELECT 1 FROM dv_driver_car dc WHERE dc.car_id = c.carId
            ) THEN 0
            ELSE 1  
        END
    ";
    if ($conn->query($sql_update_status) === TRUE) {
    } else {
        echo "ต้องมีอะไรผิดพลาดตอนไหน";
    }
    $show = "SELECT * FROM dv_car";
    $result_show = $conn->query($show);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <link rel="stylesheet" href="layout/admin.css">
</head>
<body>
    <!-- Header Section -->
    <header>
        <div class="logo">
            <img src="assets/bandologo.png" alt="Logo"> 
        </div>
        <div class="header-right">
            <span>Welcome, <?php echo $_SESSION['username']; ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <!-- Sidebar Section -->
    <aside class="sidebar">
        <ul>
            <li><a href="admin.php">Dashboard</a></li>
            <li><a href="adminUser.php">Manage Users</a></li>
            <li><a href="adminCar.php">Manage Cars</a></li>
            <li><a href="adminBoss.php">Manage Boss</a></li>
            <li><a href="report.php">Reports</a></li>
        </ul>
    </aside>

    <!-- Main -->
    <div class="box">
        <div class="container">
            <form action="connect_action.php" method="POST">
                <h1>ฟอร์มเลือกรถให้พนักงานขับรถ</h1>

                <!-- ส่วนเลือกคนขับ -->
                <label for="driver_name">ชื่อคนขับรถ</label>
                <select name="driver_name" id="driver_name">
                    <?php
                    if ($result_user->num_rows > 0) {
                        while($row = $result_user->fetch_assoc()) {
                            echo "<option value='" . $row['username'] . "'>" . $row['username'] . "</option>";
                        }
                    } else {
                        echo "<option value=''>ไม่มีข้อมูล</option>";
                    }
                    ?>
                </select>

                <!-- ส่วนเลือกรถ -->
                <label for="car_id">เลือกรถ</label>
                <select name="car_id" id="car_id">
                    <?php
                    if ($result_car->num_rows > 0) {
                        while($row = $result_car->fetch_assoc()) {
                            echo "<option value='" . $row['carId'] . "'>" . $row['carId'] . " (" . $row['carName'] . ")</option>";
                        }
                    } else {
                        echo "<option value=''>ไม่มีข้อมูล</option>";
                    }
                    ?>
                </select>
            <button type="submit">ส่งข้อมูล</button>
        </form>
    </div>

    <!-- Show Connection -->
     <div class="showConnect">
            <h2>ประวัติการใช้งานรถ</h2>
            <table style="user-select: none;">
                <thead>
                    <tr>
                        <th>ชื่อคนขับรถ</th>
                        <th>ทะเบียนรถ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $sql_show_linked = "
                        SELECT u.username, c.carName
                        FROM dv_driver_car dc
                        JOIN dv_users u ON dc.driver_id = u.user_id
                        JOIN dv_car c ON dc.car_id = c.carId
                    ";
                    $result_linked = $conn->query($sql_show_linked);
                    
                    if ($result_linked->num_rows > 0) {
                        while ($row = $result_linked->fetch_assoc()) {
                            echo "
                            <tr>
                                <td>" . $row['username'] . "</td>
                                <td>" . $row['carName'] . "</td>
                            </tr>    
                            ";
                        }
                    } else {
                        echo "<tr><td colspan='2>ไม่มีข้อมูล</td></tr>";
                    }
                    
                    ?>
                </tbody>
            </table>
        </div>         
</div>
<!-- end box -->
        <div class="table-container">
            <h2>ข้อมูลรถทั้งหมด</h2>
            <table style="user-select: none;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ยี่ห้อ + เลขทะเบียน</th>
                        <th>เลขไมล์ล่าสุด</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody id="carBody">
                    <?php
                    if ($result_show->num_rows > 0) {
                        while ($row = $result_show->fetch_assoc()) {
                            $statusText = $row['carStatus'] == 1 ? "พร้อมใช้งาน" : "ไม่พร้อมใช้งาน";
                            echo "
                            <tr>
                                <td>" . ($row["carId"]) . "</td>
                                <td>" . ($row["carName"]) . "</td>
                                <td>" . ($row["carMileage"]) . "</td>
                                <td>" . $statusText . "</td>
                            </tr>
                            ";
                        }
                    } else {
                        echo "<tr><td colspan='4>ไม่มีข้อมูล</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    
</body>
</html>
