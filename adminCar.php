<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include('config.php');

// Query ข้อมูลรถ
$sql = "SELECT * FROM dv_car";  // เปลี่ยนชื่อตารางให้ตรงกับฐานข้อมูลของคุณ
$result_show = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Management</title>
    <link rel="stylesheet" href="layout/adminCar.css">
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

    <!-- Main -->
    <div class="table-container">
        <h2>การจัดการข้อมูลรถ</h2>
        
        <!-- ปุ่มเพิ่มข้อมูลรถใหม่ -->
        <a href="addCar.php" class="btn-add">เพิ่มข้อมูลรถใหม่</a>

        <table style="user-select: none;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ชื่อรถ</th>
                    <th>เลขไมล์ล่าสุด</th>
                    <th>สถานะ</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody id="carBody">
                <?php
                    if ($result_show->num_rows > 0) {
                        while ($row = $result_show->fetch_assoc()) {
                            if ($row['carStatus'] == 1){
                                $statusText = "<span style='colr: green'>พร้อมใช้งาน</span>";
                            } else {
                                $statusText = "<span style='color: red'>ไม่พร้อมใช้งาน</span>";
                            }
                            echo "
                            <tr>
                                <td>" . ($row["carId"]) . "</td>
                                <td>" . ($row["carName"]) . "</td>
                                <td>" . ($row["carMileage"]) . "</td>
                                <td>" . $statusText . "</td>
                                <td>
                                    <a class='btn-edit' href='updateCar.php?id=" . ($row['carId']) . "'>Edit</a> &nbsp;
                                    <a class='btn-delete' href='deleteCar.php?id=" . ($row['carId']) . "' onclick='return confirmDelete()'>Delete</a>
                                </td>   
                            </tr>
                            ";
                        }
                    } else {
                        echo "<tr><td colspan='5'>ไม่มีข้อมูล</td></tr>";
                    }
                ?>
            </tbody>
        </table>
    </div>

    <script type="text/javascript">
        function confirmDelete() {
            return confirm("คุณต้องการลบรถคันนี้ใช่หรือไม่?");
        }
    </script>
</body>
</html>
