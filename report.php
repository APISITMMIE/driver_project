<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include('config.php');

// ตรวจสอบ carName ที่ส่งมาจาก JavaScript
$carNameFilter = isset($_GET['carName']) ? $_GET['carName'] : '';

// Get the current page number, default to page 1 if not set
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 20;
$offset = ($page - 1) * $items_per_page;

// SQL for filtering tasks
$sql = "SELECT * FROM dv_tasks WHERE carName LIKE '%$carNameFilter%' ORDER BY start_date DESC LIMIT $offset, $items_per_page";
$result = $conn->query($sql);

// SQL for counting total records
$total_sql = "SELECT COUNT(*) AS total FROM dv_tasks WHERE carName LIKE '%$carNameFilter%'";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $items_per_page);

// ดึงข้อมูลรถ
$sql_car = "SELECT * FROM dv_car";
$result_car = $conn->query($sql_car);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Page</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Noto Sans Thai', sans-serif;
            margin: 20px;
            background-color: #f4f4f9;
        }
        h1 {
            text-align: center;
        }
        .head {
            display: flex;
            justify-content: space-between;
        }
        .right {
            margin-right: 20%;
        }
        .box-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
        .box {
            margin-top: 20px;
            background-color: #fff;
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }
        .box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.15);
        }
        .table-container {
            width: 100%;
            max-width: 100%;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ccc;
            font-size: 14px;
        }
        th, td {
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #f9f9f9;
        }
        table td {
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 150px;
        }
        h1 {
            font-size: 36px;
        }
        .head {
            font-size: 22px;
            user-select: none;
        }
        .pagination {
            margin-top: 20px;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .pagination a {
            margin: 0 5px;
            text-decoration: none;
            color: #007bff;
        }

        .pagination .back-btn {
            margin-left: auto;
            padding: 8px 16px;
            background-color: transparent;
            color: #007bff;
            border:1px solid #007bff;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .pagination .back-btn:hover {
            background-color: #0056b3;
            color: white;
        }
    </style>
</head>
<body>
    <h1>Used company's car log</h1>
    
    <!-- Box car -->
    <div class="box-container">
        <?php
        if ($result_car->num_rows > 0) {
            while ($row = $result_car->fetch_assoc()) {
                echo '<div class="box" onclick="filterTasks(\'' . htmlspecialchars($row['carName']) . '\')">';
                echo '<p>' . htmlspecialchars($row['carName']) . '</p>';
                echo '</div>';
            }
        } else {
            echo '<p>No cars found.</p>';
        }
        ?>
    </div>

    <div class="head">
        <p class="left">Car number: &nbsp;<strong><?php echo htmlspecialchars($carNameFilter); ?></strong></p>
        <p class="right">Period:  From</p>
    </div>

    <!-- Table Showing the filtered tasks -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>วันที่</th>
                    <th>สถานที่ต้นทาง</th>
                    <th>เวลาเริ่มเดินทาง</th>
                    <th>เลขไมล์ก่อนเดินทาง</th>
                    <th>สถานที่ปลายทาง</th>
                    <th>เวลาปลายทาง</th>
                    <th>เลขไมล์เมื่อถึงที่หมาย</th>
                    <th>ระยะทางรวม (Km)</th>
                    <th>Private/Official</th>
                    <th>จุดประสงค์ในการเดินทาง</th>
                    <th>คนขับรถ</th>
                    <th>ทะเบียนรถ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $totalDistance = '-';
                        if ($row['mileage_at_destination'] && $row['mileage']) {
                            $totalDistance = $row['mileage_at_destination'] - $row['mileage'];
                        }

                        echo "
                        <tr>
                            <td>" . ($row["start_date"] ? date("d-m-y", strtotime($row["start_date"])) : '-') . "</td>
                            <td>" . $row["location"] . "</td>
                            <td>" . ($row["start_time"] ? date("H:i", strtotime($row["start_time"])) : '-') . "</td>
                            <td>" . $row["mileage"] . "</td>
                            <td>" . $row["destination_location"] . "</td>
                            <td>" . ($row["end_time"] ? date("H:i", strtotime($row["end_time"])) : '-') . "</td>
                            <td>" . $row["mileage_at_destination"] . "</td>
                            <td>" . $totalDistance . "</td>
                            <td>" . $row["accessories"] . "</td>
                            <td>" . $row["trip_types"] . "</td>
                            <td>" . $row["driver_name"] . "</td>
                            <td>" . $row["carName"] . "</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='12'>ไม่มีข้อมูล</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?carName=<?php echo urlencode($carNameFilter); ?>&page=<?php echo $page - 1; ?>">Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?carName=<?php echo urlencode($carNameFilter); ?>&page=<?php echo $i; ?>"
                   <?php echo ($i == $page) ? 'style="font-weight: bold;"' : ''; ?>>
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?carName=<?php echo urlencode($carNameFilter); ?>&page=<?php echo $page + 1; ?>">Next</a>
            <?php endif; ?>

            <!-- Back button -->
            <a href="admin.php" class="back-btn">Back</a>
        </div>
    </div>

    <script>
        function filterTasks(carName) {
            window.location.href = "?carName=" + encodeURIComponent(carName);
            document.querySelector('.left').textContent = "Car number: " + carName;
        }
    </script>
</body>
</html>
