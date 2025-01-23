<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include('config.php');
include('config_ot.php');


$rowsPerPage = 25;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $rowsPerPage;

$username = $conn->real_escape_string($_SESSION['username']);

$sql = "SELECT dv_tasks.* 
        FROM dv_tasks 
        JOIN dv_users ON dv_tasks.driver_name = dv_users.username 
        WHERE dv_users.username = '$username'
        ORDER BY 
            (dv_tasks.destination_location IS NULL OR dv_tasks.end_time IS NULL) DESC, 
            dv_tasks.start_date DESC,  
            dv_tasks.start_time DESC   
        LIMIT $offset, $rowsPerPage";

$result = $conn->query($sql);

$sqlCount = "SELECT COUNT(*) AS total 
             FROM dv_tasks 
             JOIN dv_users ON dv_tasks.driver_name = dv_users.username 
             WHERE dv_users.username = '$username'";
$countResult = $conn->query($sqlCount);
$row = $countResult->fetch_assoc();
$totalRows = $row['total'];
$totalPages = ceil($totalRows / $rowsPerPage);


// เขื่อม BMT03
$stmt1 = "SELECT dv_tasks.* 
        FROM dv_tasks 
        JOIN dv_users ON dv_tasks.driver_name = dv_users.username 
        WHERE dv_users.username = '$username'
        ORDER BY 
            (dv_tasks.destination_location IS NULL OR dv_tasks.end_time IS NULL) DESC, 
            dv_tasks.start_date DESC,  
            dv_tasks.start_time DESC";
$data1 = $conn->query($stmt1);

$stmt2 = $conn2->prepare("SELECT * FROM TbDate");
$stmt2->execute();
$data2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
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

        /* Table */
        .table-container {
            width: 100%;
            max-width: 100%;
            margin: 20px auto;
            background-color: #fff;
            padding: 5px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: auto;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #e0e0e0; 
            font-size: 14px;
            font-weight: 600;
        }

        th, td {
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #3498db; 
            color: white;

        }

        td {
            background-color: #ecf6fd;
            font-size: 18px;
            font-weight: 400;
        }

        tr:nth-child(even) td {
            background-color: #f4f9fd; 
        }

        tr:hover {
            cursor: pointer;
            background-color: #e6f2ff; 
        }

        table td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 110px;
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
            justify-content: space-around;
            align-items: center;
        }
        .pagination a {
            margin: 0 5px;
            text-decoration: none;
            color: #007bff;
        }

        .back-btn {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            padding: 6px 14px;
            background-color: transparent;
            color: #007bff;
            border:1px solid #007bff;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .back-btn:hover {
            background-color: #0056b3;
            color: white;
        }
    </style>
</head>
<body>
    <h1>ค่าแรง OT</h1>
    
    <!-- Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>วันที่</th>
                    <th>เวลาเริ่มเดินทาง</th>
                    <th>เวลาปลายทาง</th>
                    <th>เวลาเดินทางรวม</th>
                    <th>Day</th>
                    <th>Working Time</th>
                    <th>OT x 1.5</th>
                    <th>OT x 1</th>
                </tr>
            </thead>
             <tbody id="taskListBody">
             <?php
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
                        $status = 'Wait Approve'; 
                        if (!empty($row['destination_location']) && !empty($row['end_time'])) {
                            $status = 'Approved';
                        }

                        $startTime = strtotime($row['start_time']);
                        $endTime = strtotime($row['end_time']);

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
            

                        // +10 นาทีให้กับ end time
                        if ($endTime) {
                            $endTimePlus10 = strtotime('+10 minutes', $endTime);
                            $endTimeFormatted = date("H:i", $endTimePlus10);
                        } else {
                            $endTimeFormatted = '-'; 
                        }

                        $totalTime = '-';
                        if (!empty($row['end_time']) && !empty($row['start_time'])) {  
                            $endTimePlus10 = strtotime('+10 minutes', $endTime);
                            $timeDiff = $endTimePlus10 - $startTime;
                            $hours = floor($timeDiff / 3600);
                            $minutes = floor(($timeDiff % 3600) / 60);
                            $totalTime = sprintf('%02d:%02d', $hours, $minutes);
                        }

                        $matchfound = '-';
                        $holiday = '-';
                        $workingTime = '-';
                        $overtime1 = '-';
                        $overtime2 = '-';
                        $overtime3 = '-';

                        foreach ($data2 as $row2) {
                            if ($row['start_date'] == $row2['date_value']) {
                                $matchfound = "Match found";
                                $holidayText = ($row2['working_day'] == 1) ? 'วันปกติ' : 'วันหยุด';
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
                        <tr class='task-row' data-task-id='" . $row["task_id"] . "' data-status='$status'>
                            <td>" . ($row["start_date"] ? date("d-m-y", strtotime($row["start_date"])) : '-') . "</td>
                            <td>" . ($startTime ? date("H:i", $startTime) : '-') . "</td>
                            <td>" . ($endTimeFormatted ? $endTimeFormatted : '-') . "</td>
                            <td>" . $totalTime . "</td>
                            <td><span style='color: $holidayColor ;'> $holidayText </span></td>
                            <td>" . $workingTime . "</td>
                            <td>" . $overtime1 . "</td>
                            <td>" . $overtime3 . "</td>
                        </tr>
                        ";
                    }
                } else {
                    echo "<tr><td colspan='9'>ไม่มีข้อมูล</td></tr>";
                }
                ?>

            </tbody>
        </table>

        <!-- Pagination -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>" class="pagination-button">Previous</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" class="pagination-button <?php echo ($i == $page) ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>" class="pagination-button">Next</a>
        <?php endif; ?>
    </div>
</div>

    <!-- Back button -->
    <a href="tasklist.php" class="back-btn">Back</a>

    <script>
        function filterTasks(driver) {
            console.log("Selected driver:", driver);
            window.location.href = "?driver_name=" + encodeURIComponent(driver.trim());
            document.querySelector('.left').textContent = "คนขับรถ: " + driver;
        }

    </script>
</body>
</html>
