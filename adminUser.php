<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include('config.php');
$sql = "SELECT * FROM dv_users WHERE role = 'user'";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="layout/adminUser.css">
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
            <h2>การจัดการข้อมูลผู้ใช้</h2>

            <!-- ปุ่มเพิ่มข้อมูลรถใหม่ -->
            <a href="adduser.php" class="btn-add">เพิ่มข้อมูลผู้ใช้ใหม่</a>

            <table style="user-select: none;">
                <thead>
                    <tr>
                        <th style="width: 30%;">ชื่อผู้ใช้</th>
                        <th style="width: 30%;">รหัสผ่าน</th>
                        <th style="width: 20%;">PIN</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody id="carBody">
                <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "
                            <tr>
                                <td>" . ($row["username"]) . "</td>
                                <td>" . ($row["password"]) . "</td>
                                <td>" . ($row['pin']) . "</td>
                                <td>
                                    <a class='btn-edit' href='updateUser.php?id=" . ($row['user_id']) . "'>Edit</a> &nbsp;
                                    <a class='btn-delete' href='deleteUser.php?id=" . ($row['user_id']) . "' onclick='return confirmDelete()'>Delete</a>
                                </td>   
                            </tr>
                            ";
                        }
                    } else {
                        echo "<tr><td colspan='4'>ไม่มีข้อมูล</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    <script type="text/javascript">
        function confirmDelete() {
            return confirm("คุณต้องการลบใช่หรือไม่?");
        }
    </script>
</body>
</html>
