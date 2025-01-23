<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include('config.php');
include('config_ot.php');

$carUserFilter = isset($_GET['carUser']) ? $_GET['carUser'] : '';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 20;
$offset = ($page - 1) * $items_per_page;

// เลือกวันที่ From To
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$carUserFilter = isset($_GET['carUser']) ? $_GET['carUser'] : '';

$dateCondition = '';
if ($startDate && $endDate) {
    $dateCondition = " AND start_date BETWEEN '$startDate' AND '$endDate' ";
} elseif ($startDate) {
    $dateCondition = " AND start_date >= '$startDate' ";
} elseif ($endDate) {
    $dateCondition = " AND start_date <= '$endDate' ";
}

$sql = "SELECT * FROM dv_tasks WHERE carUser LIKE '%$carUserFilter%' $dateCondition ORDER BY start_date DESC LIMIT $offset, $items_per_page";
$result = $conn->query($sql);

// นับจำนวนรวมของข้อมูล เพื่อแบ่งหน้า
$total_sql = "SELECT COUNT(*) AS total FROM dv_tasks WHERE carUser LIKE '%$carUserFilter%' $dateCondition";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $items_per_page);

// ดึงข้อมูลนาย
$sql_boss = "SELECT boss_name FROM dv_boss";
$result_boss = $conn->query($sql_boss);

// ดึงข้อมูลรถ
$sql_driver = "SELECT username FROM dv_users WHERE role = 'user'";
$result_driver = $conn->query($sql_driver);
$stmt2 = $conn2->prepare("SELECT * FROM TbDate");
$stmt2->execute();
$data2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Export to Excel
if (isset($_GET['export'])) {

    $exportSql = "SELECT * FROM dv_tasks WHERE driver_name LIKE '%$carUserFilter%' $dateCondition ORDER BY start_date DESC";
    $exportResult = $conn->query($exportSql);

    if ($exportResult->num_rows > 0) {
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=driver_report.xls");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo "<table border='1'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>วันที่</th>";
        echo "<th>สถานที่ต้นทาง</th>";
        echo "<th>เวลาเริ่มเดินทาง</th>";
        echo "<th>เลขไมล์ก่อนเดินทาง</th>";
        echo "<th>สถานที่ปลายทาง</th>";
        echo "<th>เวลาปลายทาง</th>";
        echo "<th>เลขไมล์เมื่อถึงที่หมาย</th>";
        echo "<th>ระยะทางรวม (Km)</th>";
        echo "<th>ระยะเวลารวม</th>";
        echo "<th>Driver</th>";
        echo "<th>Day</th>";
        
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        $result->data_seek(0); // รีเซ็ต pointer ของ result
        while ($row = $result->fetch_assoc()) {
            $totalDistance = '-';
            if ($row['mileage_at_destination'] && $row['mileage']) {
                $totalDistance = $row['mileage_at_destination'] - $row['mileage'];
            }

            $driverName = $row['driver_name'];

            $startTime = strtotime($row['start_time']);
            $endTime = strtotime($row['end_time']);

            if (date('H:i', $startTime) > '05:30' && date('H:i', $startTime) < '06:00') {
                $startTime = strtotime(date('Y-m-d', $startTime) . ' 06:00');
            }

            if (date('H:i', $startTime) > '04:45' && date('H:i', $startTime) <= '05:30') {
                $startTime = strtotime(date('Y-m-d', $startTime) . ' 05:00');
            }

            if ($endTime) {
                $endTimePlus10 = strtotime('+10 minutes', $endTime);
                $endTimeFormatted = date('H:i', $endTimePlus10);
            } else {
                $endTimeFormatted = '-';
            }

            $totalTime = '-';
            if (!empty($row['end_time']) && !empty($row['start_time'])) {
                $endTime = strtotime($row['end_time']);
                $startTime = strtotime($row['start_time']);
                $timeDiff = $endTime - $startTime;

                $hours = floor($timeDiff / 3600);
                $minutes = floor(($timeDiff % 3600) / 60);
                $totalTime = sprintf('%02d:%02d', $hours, $minutes);
            }

            $holidayText = '-';
            $holidayColor = '-';
            $overtime1 = '-';
            $overtime3 = '-';

            foreach ($data2 as $row2) {
                if ($row['start_date'] == $row2['date_value']) {
                    $holidayText = ($row2['working_day'] == 1) ? 'Working day' : 'Holiday';
                    $holidayColor = ($row2['working_day'] == 1) ? 'green' : 'orange';
                    $workingTime = $row2['start_working_time'] && $row2['end_working_time']
                        ? date("H:i", strtotime($row2['start_working_time'])) . '-' . date("H:i", strtotime($row2['end_working_time'])) 
                        : '-';

                    $startWorkingTime = strtotime($row2['start_working_time']);
                    $endWorkingTime = strtotime($row2['end_working_time']);

                    if ($row2['working_day'] != 1) {
                        if ($startTime < $startWorkingTime) {
                            $beforeWorkOvertime = $startWorkingTime - $startTime; 
                            $hours = floor($beforeWorkOvertime / 3600);
                            $minutes = floor(($beforeWorkOvertime % 3600) / 60);
                            $overtime1 = sprintf('%02d:%02d', $hours, $minutes); 
                        }

                        elseif ($startTime >= $endWorkingTime) {
                            $afterWorkOvertime = $endTimePlus10 - $endWorkingTime; 
                            $hours = floor($afterWorkOvertime / 3600);
                            $minutes = floor(($afterWorkOvertime % 3600) / 60);
                            $overtime3 = sprintf('%02d:%02d', $hours, $minutes); 
                        }
                        elseif ($startTime >= $startWorkingTime && $endTime <= $endWorkingTime) {
                            $overtime1 = '-'; 
                            $overtime3 = '-';
                        }
                        elseif ($startTime > $startWorkingTime && $endTime > $endWorkingTime) {
                            $overtime1 = '00:00'; 
                            $overtime3 = '00:00';
                        }

                    } else { 

                        if ($startTime < $startWorkingTime) {
                            $beforeWorkOvertime = $startWorkingTime - $startTime; 
                            $hours = floor($beforeWorkOvertime / 3600);
                            $minutes = floor(($beforeWorkOvertime % 3600) / 60);
                            $overtime1 = sprintf('%02d:%02d', $hours, $minutes);
                        }
                        elseif ($startTime >= $endWorkingTime) {
                            $afterWorkOvertime = $endTimePlus10 - $endWorkingTime; 
                            $hours = floor($afterWorkOvertime / 3600);
                            $minutes = floor(($afterWorkOvertime % 3600) / 60);
                            $overtime1 = sprintf('%02d:%02d', $hours, $minutes); 
                        }
                        
                    }
                }
            }
        
                
            echo "<tr>";
            echo "<td>" . ($row["start_date"] ? date("d-m-y", strtotime($row["start_date"])) : '-') . "</td>";
            echo "<td>" . $row["location"] . "</td>";
            echo "<td>" . ($startTime ? date("H:i", $startTime) : '-') . "</td>";
            echo "<td>" . $row["mileage"] . "</td>";
            echo "<td>" . $row["destination_location"] . "</td>";
            echo "<td>" . ($endTime ? date("H:i", $endTime) : '-') . "</td>";
            echo "<td>" . $row["mileage_at_destination"] . "</td>";
            echo "<td>" . $totalDistance . "</td>";
            echo "<td>" . $totalTime . "</td>";
            echo "<td>" . $driverName . "</td>";
            echo "<td><span color: $holidayColor>  $holidayText </span></td>";
            
            echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
        exit;
    }
}
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
            margin-right: 5%;
        }
        .box-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            margin-bottom: 20px;
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
            border-color: #007bff; 
            background-color: transparent; 
        }
        .box.active {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.15);
            border-color: #007bff;
            background-color: #007bff;
            color: white;
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

        .export-data {
            display: flex;
            float: right;
            border: 1px solid green;
            padding: 6px 10px;
            margin-top: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            color: white;
            background-color: green;
            text-decoration: none;
            cursor: pointer;
        }

        .export-data:hover {
            background-color: transparent;
            color: green;
        }

    </style>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <h1>Boss report</h1>
    
    <!-- Box car -->
    <div class="box-container">
        <?php
        if ($result_boss->num_rows > 0) {
            while ($row = $result_boss->fetch_assoc()) {
                $carUser = trim($row['boss_name']);
                $activeClass = ($carUser === $carUserFilter) ? 'active' : '';
                echo '<div class="box ' .  $activeClass . '" onclick="filterTasks(\'' . addslashes(htmlspecialchars($carUser)) . '\')">';
                echo '<p>' . htmlspecialchars($row['boss_name']) . '</p>';
                echo '</div>';
            }
        } else {
            echo '<p>No boss found.</p>';
        }
        ?>
    </div>

    <div class="head">
    <p class="left">Boss name: &nbsp;<strong><?php echo htmlspecialchars($carUserFilter); ?></strong></p>
        <form method="get" action="" class="right">

            <input type="hidden" name="carUser" value="<?php echo htmlspecialchars($carUserFilter); ?>">

            <label for="start_date">From:</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
            
            <label for="end_date">To:</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
            
            <button type="submit">Apply</button>
        </form>
    </div>

    <!-- ปุ่ม Export -->
    <a href="?export=true&dcarUser=<?php echo urlencode($carUserFilter); ?>&start_date=<?php echo urlencode($startDate); 
        ?>&end_date=<?php echo urlencode($endDate); ?>" class="export-data">
        <span class="material-icons">file_upload</span>Export
    </a>

    <!-- Table Showing the filtered tasks -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Start Location</th>
                    <th>Start Time</th>
                    <th>Mileage Before</th>
                    <th>Destination</th>
                    <th>Destination Time</th>
                    <th>Mileage at destination</th>
                    <th>Total Diatance (Km)</th>
                    <th>Period</th>
                    <th>Driver</th>
                    <th>Day</th>
                    <th>OT x 1.5</th>
                    <th>OT Holiday</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (empty($carUserFilter)) {
                    echo "<tr><td colspan='13' style='font-size: 20px;'>กรุณาเลือกหัวหน้างานเพื่อดูข้อมูล</td></tr>";
                } else {
                    if ($result->num_rows > 0) {

                        // Function ปัดนาที
                        function adjustOvertime($overtime) {
                            if ($overtime != '-') {
                                list($hours, $minutes) = explode(":", $overtime);
                                if ($minutes == 59) {
                                    $hours++; // Increase the hour
                                    $minutes = 0; // Set minutes to 00
                                }
                                return sprintf('%02d:%02d', $hours, $minutes);
                            }
                            return $overtime;
                            }

                        while ($row = $result->fetch_assoc()) {
                            $totalDistance = '-';
                            if ($row['mileage_at_destination'] && $row['mileage']) {
                                $totalDistance = $row['mileage_at_destination'] - $row['mileage'];
                            }
                             // ปัดเวลา
                             $startTime = strtotime($row['start_time']);
                             $endTime = strtotime($row['end_time']);
     
                             // ปรับเวลา
                            if (date('H:i', $startTime) > '05:30' && date('H:i', $startTime) < '06:00') {
                                $startTime = strtotime(date('Y-m-d', $startTime) . ' 06:00');
                            }
                
                            if (date('H:i', $startTime) > '04:45' && date('H:i', $startTime) <= '05:30') {
                                $startTime = strtotime(date('Y-m-d', $startTime) . ' 05:00');
                            }

                            if (date('H:i', $startTime) > '06:00' && date('H:i', $startTime) <= '06:30') {
                                $startTime = strtotime(date('Y-m-d', $startTime) . ' 06:00');
                            }

                            if (date('H:i', $endTime) > '07:50' && date('H:i', $endTime) <= '07:59') {
                                $endtime = strtotime(date('Y-m-d', $endTime) . ' 07:50');
                            }

                             // +10 นาที end time 
                             if ($endTime) {
                                 $endTimePlus10 = strtotime('+10 minutes', $endTime);
                                 $endTimeFormatted = date('H:i', $endTimePlus10);
                             } else {
                                 $endTimeFormatted = '-';
                             }
 
                             // เวลารวม
                             $totalTime = '-';
                             if (!empty($row['end_time']) && !empty($row['start_time'])) {
                                 $endTime = strtotime($row['end_time']);
                                 $timeDiff = $endTime - $startTime;
 
                                 // cal time 
                                 $hours = floor($timeDiff / 3600);
                                 $minutes = floor(($timeDiff % 3600) / 60);
                                 $totalTime = sprintf('%02d:%02d', $hours, $minutes);
                             }
 
                             $matchfound = '-';
                             $holiday = '-';
                             $workingTime = '-';
                             $overtime1 = '-';
                             $overtime3 = '-';
 
                             foreach ($data2 as $row2) {
                                 if ($row['start_date'] == $row2['date_value']) {
                                     $matchfound = "Match found";
                                     $holidayText = ($row2['working_day'] == 1) ? 'Working day' : 'Holiday';
                                     $holidayColor = ($row2['working_day'] == 1) ? 'green' : 'orange';
     
                                     $workingTime = $row2['start_working_time'] && $row2['end_working_time']
                                         ? date("H:i", strtotime($row2['start_working_time'])) . '-' . date("H:i", strtotime($row2['end_working_time'])) 
                                         : '-';
     
                                     $startWorkingTime = strtotime($row2['start_working_time']);
                                     $endWorkingTime = strtotime($row2['end_working_time']);
     
                                     // เพิ่ม 30 นาที ตอนเบรก
                                    if ($endWorkingTime) {
                                        $endWorkingTimeFormatted = date("Y-m-d H:i:s", $endWorkingTime);
                                        $endWokingTimeFormat = date("H:i", strtotime("+30 minutes", strtotime($endWorkingTimeFormatted)));
                                    } else {
                                        $endWokingTimeFormat = '-';
                                    }
                                    
                                    // คำนวณ OT
                                    if ($row2['working_day'] != 1) { // วันหยุด
                                        $timeDiff = $endTime - $startTime;
                                        if ($timeDiff < 14400) { 
                                            $overtime3 = '05:00';
                                        } elseif ($timeDiff > 18000) { 
                                            $afterWorkOvertime = $endTimePlus10 - $startTime;
                                            $hours = floor($afterWorkOvertime / 3600);
                                            $minutes = floor(($afterWorkOvertime % 3600) / 60);
                                            $overtime3 = sprintf('%02d:%02d', $hours, $minutes);
                                        }
                                            // อยู่ในเวลางาน
                                            elseif ($startTime >= $startWorkingTime && $endTime <= $endWorkingTime) {
                                                $overtime1 = '-'; 
                                                $overtime3 = '-';
                                            }
                                            elseif ($startTime > $startWorkingTime && $endTime > $endWorkingTime) {
                                                $overtime1 = '00:00'; 
                                                $overtime3 = '00:00';
                                            }
                                    } else { // วันปกติ
                                        if ($startTime < $startWorkingTime) {
                                            $beforeWorkOvertime = $startWorkingTime - $startTime;
                                            $hours = floor($beforeWorkOvertime / 3600);
                                            $minutes = floor(($beforeWorkOvertime % 3600) / 60);
                                            $overtime1 = sprintf('%02d:%02d', $hours, $minutes);
                                        } elseif ($startTime >= $endWorkingTime) {
                                            $afterWorkOvertime = $endTimePlus10 - strtotime($endWokingTimeFormat);
                                            $hours = floor($afterWorkOvertime / 3600);
                                            $minutes = floor(($afterWorkOvertime % 3600) / 60);
                                            $overtime1 = sprintf('%02d:%02d', $hours, $minutes);
                                        }
                                    }
                                }
                            }

                            $overtime1 = adjustOvertime($overtime1);
                            $overtime3 = adjustOvertime($overtime3);

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
                                <td>" . $totalTime . "</td>
                                <td>" . $row["driver_name"] . "</td>
                                <td><span style='color: $holidayColor ;'> $holidayText </span></td>
                                <td>" . $overtime1 . "</td>
                                <td>" . $overtime3 . "</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='12'>ไม่มีข้อมูล</td></tr>";
                    }
                }
                ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?carUser=<?php echo urlencode($carUserFilter); ?>&page=<?php echo $page - 1; ?>">Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?carUser=<?php echo urlencode($carUserFilter); ?>&page=<?php echo $i; ?>"
                   <?php echo ($i == $page) ? 'style="font-weight: bold;"' : ''; ?>>
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?carUser=<?php echo urlencode($carUserFilter); ?>&page=<?php echo $page + 1; ?>">Next</a>
            <?php endif; ?>

            <!-- Back button -->
            <a href="admin.php" class="back-btn">Back</a>
        </div>
    </div>

    <script>
        function filterTasks(carUser) {
            console.log("Selected boss:", carUser);
            window.location.href = "?carUser=" + encodeURIComponent(carUser.trim());
            document.querySelector('.left').textContent = "Boss name: " + carUser;
        }
    </script>
</body>
</html>
