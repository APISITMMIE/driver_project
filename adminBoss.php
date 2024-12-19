<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include('config.php');
$sql = "SELECT * FROM dv_boss ";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boss Management</title>
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
        </ul>
    </div>

    <!-- Main -->
        <div class="table-container">
            <h2>การจัดการข้อมูลหัวหน้า</h2>

            <!-- ปุ่มเพิ่มข้อมูลรถใหม่ -->
            <a href="addBoss.php" class="btn-add">เพิ่มบัญชีหัวหน้า</a>

            <table style="user-select: none;">
                <thead>
                    <tr>
                        <th style="width: 40%;">ชื่อหัวหน้า</th>
                        <th style="width: 40%;">PIN</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody id="carBody">
                <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "
                            <tr>
                                <td>" . ($row["boss_name"]) . "</td>
                                <td>" . ($row["pin"]) . "</td>
                                <td>
                                    <a class='btn-edit' href='updateBoss.php?id=" . ($row['boss_id']) . "'>Edit</a> &nbsp;
                                    <a class='btn-delete' href='deleteBoss.php?id=" . ($row['boss_id']) . "' onclick='return confirmDelete()'>Delete</a>
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
