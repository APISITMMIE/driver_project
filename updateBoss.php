<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

include('config.php');

if (isset($_GET['id'])) {
    $boss_id = $_GET['id'];
    $sql = "SELECT * FROM dv_boss WHERE boss_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $boss_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        header("Location: adminBoss.php");
        exit;
    }
} else {
    header("Location: adminBoss.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bossName = $_POST['boss_name'];
    $pin = $_POST['pin'];
    $updateSql = $conn->prepare("UPDATE dv_boss SET boss_name = ?, pin = ? WHERE boss_id = ?");
    $updateSql->bind_param("ssi", $bossName, $pin, $boss_id);

    if ($updateSql->execute()) {
        echo "<script>
                alert('อัพเดตข้อมูลสำเร็จ Success');
                window.location.href = 'adminBoss.php';
            </script>";
            exit;
        } else {
            echo "Error: " . $updateSql->error;
        }
        $updateSql->close();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User</title>
    <link rel="stylesheet" href="layout/adminBoss.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .table-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        form {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            width: 35%;
            margin: auto;
        }
        form label {
            font-size: 16px;
            color: #555;
            display: block;
            margin-bottom: 8px;
        }
        form input[type="text"], form input[type="password"] {
            width: 95%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        form input[type="text"]:focus, form input[type="password"]:focus {
            outline: none;
            border-color: #007bff;
        }
        form button {
            width: 100%;
            padding: 10px;
            background-color: transparent;
            color: #007bff;
            padding: 12px 20px;
            border: 1px solid #007bff ;
            border-radius: 30px;
            cursor: pointer;
            font-size: 18px;
        }
        form button:hover {
            background-color: #007bff;
            color: white;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }

        /* Eye Icon */
        .password-container {
            position: relative;
        }

        .eye-icon {
            position: absolute;
            right: 10px;
            top: 35%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
        }
        
        .eye-icon:hover {
            color: #007bff;
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
        <h2>แก้ไขข้อมูลผู้ใช้</h2>
        <form method="POST">
            <label for="boss_name">ชื่อนายจ้าง</label>
            <input type="text" id="boss_name" name="boss_name" value="<?php echo htmlspecialchars($user['boss_name']); ?>" required>

            <label for="pin">PIN</label>
            <input type="text" id="pin" name="pin" value="<?php echo htmlspecialchars($user['pin']); ?>" required>

            <button type="submit">Update</button>
        </form>
        <button onclick="history.back()" class="back-btn">Go Back</button>
    </div>

</body>
</html>
