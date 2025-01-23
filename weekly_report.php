<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include('config.php');
include('config_ot.php');

$driverFilter = isset($_GET['driver_name']) ? $_GET['driver_name'] : '';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 20;
$offset = ($page - 1) * $items_per_page;

// เลือกวันที่ From To
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$driverFilter = isset($_GET['driver_name']) ? $_GET['driver_name'] : '';

$dateCondition = '';
if ($startDate && $endDate) {
    $dateCondition = " AND start_date BETWEEN '$startDate' AND '$endDate' ";
} elseif ($startDate) {
    $dateCondition = " AND start_date >= '$startDate' ";
} elseif ($endDate) {
    $dateCondition = " AND start_date <= '$endDate' ";
}

$sql = "SELECT * FROM dv_tasks WHERE driver_name LIKE '%$driverFilter%' $dateCondition ORDER BY start_date LIMIT $offset, $items_per_page";
$result = $conn->query($sql);

// นับจำนวนรวมของข้อมูล เพื่อแบ่งหน้า
$total_sql = "SELECT COUNT(*) AS total FROM dv_tasks WHERE driver_name LIKE '%$driverFilter%' $dateCondition";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $items_per_page);

// ดึงข้อมูลรถ
$sql_driver = "SELECT username FROM dv_users WHERE role = 'user'";
$result_driver = $conn->query($sql_driver);
$stmt2 = $conn2->prepare("SELECT * FROM TbDate");
$stmt2->execute();
$data2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Export to Excel
if (isset($_GET['export'])) {

    $exportSql = "SELECT * FROM dv_tasks WHERE driver_name LIKE '%$driverFilter%' $dateCondition ORDER BY start_date";
    $exportResult = $conn->query($exportSql);

    if ($exportResult->num_rows > 0) {
        
        $filename = 'driver weekly report ' . preg_replace('/[^A-Za-z0-9_]/', '_', $driverFilter) . ' ' . str_replace('/', '-', $startDate) . ' to ' . str_replace('/', '-', $endDate) . '.xls';
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename={$filename}");
        header("Pragma: no-cache");
        header("Expires: 0");

        $row = $exportResult->fetch_assoc();
        $driverName = $row['driver_name']; 

        echo "<table >";
        echo "<tr ";
        echo "       <td colspan='1' style='font-size: 18px; text-decoration: underline;'>Driver Weekly Report</td>";
        echo "       <td colspan='3'></td>";
        echo "       <td colspan='2'></td>";
        echo "    </tr>";
        echo "<tr ";
        echo "       <td colspan='1'></td>";
        echo "       <td colspan='3'></td>";
        echo "       <td colspan='2' style='text-decoration: underline; text-align: center;'>Name: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . htmlspecialchars($driverName) . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";
        echo "    </tr>";
        echo "<thead>";
        echo "    <tr ";
        echo "        <th scope='col' rowspan='2' style='border: 1px solid black; text-align:center'>Date</th>";
        echo "        <th scope='col' colspan='2' style='border: 1px solid black; text-align:center'>Time</th>";
        echo "        <th scope='col' colspan='3' style='border: 1px solid black; text-align:center'>Over Time</th>";
        echo "    </tr>";
        echo "    <tr ";
        echo "        <th scope='col' style='width: 80px; border: 1px solid black; text-align:center;'>From</th>";
        echo "        <th scope='col' style='width: 80px; border: 1px solid black; text-align:center;'>To</th>";
        echo "        <th scope='col' style='width: 80px; border: 1px solid black; text-align:center;'>x1.5</th>";
        echo "        <th scope='col' style='width: 80px; border: 1px solid black; text-align:center;'>Holiday</th>";
        echo "        <th scope='col' style='width: 130px; border: 1px solid black; text-align:center;'>Detail OT</th>";
        echo "    </tr>";
        echo " </thead>";
        echo "<tbody>";

        echo '<style>
            @page {
                size: A4;
                margin: 1cm;
            }
            table {
                width: 100%;
            }
            th, td {
                text-align: center;
                border-collapse: collapse;
            }
            th {
                background-color: #f2f2f2;
            }
        </style>';

        $result->data_seek(0);
        if ($result->num_rows > 0) {
            $previousDate = ''; 
            $overtime1Sum = 0; 
            $overtime3Sum = 0; 
            $totalOvertime1Sum = 0; 
            $totalOvertime3Sum = 0;
            $currentRow = 0; 
            $totalRows = $result->num_rows; 
            $allowanceTotal = 0;
            $riskAllowanceTotal =0;

            // Function ปัดนาที
            function adjustOvertime($overtime) {
                if ($overtime != '-') {
                    list($hours, $minutes) = explode(":", $overtime);
                    if ($minutes == 59) {
                        $hours++; 
                        $minutes = 0; 
                    }
                    return sprintf('%02d:%02d', $hours, $minutes);
                }
                return $overtime;
                }
    
            while ($row = $result->fetch_assoc()) {
                $currentRow++;
                
                $allowanceTotal += isset($row['allowance']) ? $row['allowance'] : 0;
                $riskAllowanceTotal += isset($row['risk_allowance']) ? $row['allowance'] : 0;

                $totalDistance = '-';
                if ($row['mileage_at_destination'] && $row['mileage']) {
                    $totalDistance = $row['mileage_at_destination'] - $row['mileage'];
                }
    
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

                // คำนวณเวลารวม
                $totalTime = '-';
                if (!empty($row['end_time']) && !empty($row['start_time'])) {
                    $timeDiff = $endTime - $startTime;
                    $hours = floor($timeDiff / 3600);
                    $minutes = floor(($timeDiff % 3600) / 60);
                    $totalTime = sprintf('%02d:%02d', $hours, $minutes);
                }
    
                // คำนวณ OT
                $overtime1 = '-';
                $overtime3 = '-';
                foreach ($data2 as $row2) {
                    if ($row['start_date'] == $row2['date_value']) {
                        $startWorkingTime = strtotime($row2['start_working_time']);
                        $endWorkingTime = strtotime($row2['end_working_time']);

                        // เพิ่ม 30 นาที ตอนเบรก
                        if ($endWorkingTime) {
                            $endWorkingTimeFormatted = date("Y-m-d H:i:s", $endWorkingTime);
                            $endWokingTimeFormat = date("H:i", strtotime("+30 minutes", strtotime($endWorkingTimeFormatted)));
                        } else {
                            $endWokingTimeFormat = '-';
                        }
                        
                        // วันหยุด
                        if ($row2['working_day'] != 1) { 

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

                // วันที่เดียวกัน หรือ บันทัดที่สองลงไป
                if ($previousDate == date("d-m-y", strtotime($row["start_date"]))) {
                    if ($overtime1 != '-') {
                        list($hours, $minutes) = explode(":", $overtime1);
                        $totalMinutes = $hours * 60 + $minutes;
                        $overtime1Sum += $totalMinutes;
                    }
                    if ($overtime3 != '-') {
                        list($hours, $minutes) = explode(":", $overtime3);
                        $totalMinutes = $hours * 60 + $minutes;
                        $overtime3Sum += $totalMinutes;
                    }
                    if ($overtime1 != '-') {
                        list($hours, $minutes) = explode(":", $overtime1);
                        $totalMinutes = $hours * 60 + $minutes;
                        $totalOvertime1Sum += $totalMinutes; 
                    }
                    if ($overtime3 != '-') {
                        list($hours, $minutes) = explode(":", $overtime3);
                        $totalMinutes = $hours * 60 + $minutes;
                        $totalOvertime3Sum += $totalMinutes; 
                    }

                    // เวลา 8 โมง
                    $startTimeWorking = '-'; 
                        foreach ($data2 as $row2) {
                            if ($row['start_date'] == $row2['date_value']) {
                                $startTimeWorking = $row2['start_working_time'];
                                break;
                            }
                        }

                    $endWorkingTime = '-'; 
                    foreach ($data2 as $row2) {
                        if ($row['start_date'] == $row2['date_value']) {
                            $endWorkingTime = $row2['end_working_time'];
                            break;
                        }
                    }

                // วันเดียวกัน หรือบันทัดสองลงไป 
                    if ($row2['working_day'] == 1) { // วันทำงานปกติ
                        if ($startTime >= $startTimeWorking && $endTimeFormatted <= $endWorkingTime) {
                            echo "<tr>
                                    <td style='border:none; border-left: 1px solid black;'></td>
                                    <td style='border:none; border-left: 0.8px solid black;'>" . ($startTime ? date("H:i", $startTime) : '-') . "</td>
                                    <td style='border:none; border-left: 0.8px solid black;'>" . ($endTimeFormatted ? $endTimeFormatted : '-') . "</td>
                                    <td style='border:none; border-left: 0.8px solid black;'>" . $overtime1 . "</td>
                                    <td style='border:none; border-left: 0.8px solid black; border-right: 0.8px solid black;'>" . $overtime3 . "</td>
                                    <td style='border: none; border-right: 1px solid black;''>" . '' . "</td>
                                </tr>";
                        } else { // เวลาอยู่นอกเวลาทำงาน <8-17>
                            echo "<tr>
                                    <td style='border:none; border-left: 1px solid black;'></td>
                                    <td style='border:none; border-left: 0.8px solid black;'>" . ($endWorkingTime ? date("H:i", strtotime($endWorkingTime)) : '-') . "</td>
                                    <td style='border:none; border-left: 0.8px solid black;'>" . ($endTimeFormatted ? $endTimeFormatted : '-') . "</td>
                                    <td style='border:none; border-left: 0.8px solid black;'>" . $overtime1 . "</td>
                                    <td style='border:none; border-left: 0.8px solid black; border-right: 0.8px solid black;'>" . $overtime3 . "</td>
                                    <td style='border:none; border-right: 1px solid black;'>" . '' . "</td>
                                </tr>";
                        }
                    } else { // วันหยุด
                        $overtime3 = '';
                            $overtimeSpecial = $overtime3;
                            $overtimeSpecial = $endTimePlus10 - $startTime;
                            $hours = floor($overtimeSpecial / 3600);
                            $minutes = floor(($overtimeSpecial % 3600) / 60);
                            $overtimeSpecial = sprintf('%02d:%02d', $hours, $minutes);
                            if ($overtimeSpecial != '-') {
                                list($hours, $minutes) = explode(":", $overtimeSpecial);
                                $totalMinutes = $hours * 60 + $minutes;
                                $overtime3Sum += $totalMinutes;
                            }
                            if ($overtimeSpecial != '-') {
                                list($hours, $minutes) = explode(":", $overtimeSpecial);
                                $totalMinutes = $hours * 60 + $minutes;
                                $totalOvertime3Sum += $totalMinutes; 
                            }
                            echo "<tr>
                                    <td style='border:none; border-left: 1px solid black;'></td>
                                    <td style='border:none; border-left: 0.8px solid black;'>" . ($startTime ? date("H:i", $startTime) : '-') . "</td>
                                    <td style='border:none; border-left: 0.8px solid black;'>" . ($endTimeFormatted ? $endTimeFormatted : '-') . "</td>
                                    <td style='border:none; border-left: 0.8px solid black;'>" . $overtime1 . "</td>
                                    <td style='border:none; border-left: 0.8px solid black; border-right: 0.8px solid black;'>" . $overtimeSpecial . "</td>
                                    <td style='border:none; border-right: 1px solid black;'>" . '' . "</td>
                                </tr>";
                            
                    }
                
                } else { // ไม่ใช่วันเดียวกัน หรือ ข้อมูลบันทัดแรก
                    if ($previousDate != '') {
                        $totalOvertime1Hours = floor($overtime1Sum / 60);
                        $totalOvertime1Minutes = $overtime1Sum % 60;
                        $overtime1Total = adjustOvertime(sprintf('%02d:%02d', $totalOvertime1Hours, $totalOvertime1Minutes));
    
                        $totalOvertime3Hours = floor($overtime3Sum / 60);
                        $totalOvertime3Minutes = $overtime3Sum % 60;
                        $overtime3Total = adjustOvertime(sprintf('%02d:%02d', $totalOvertime3Hours, $totalOvertime3Minutes));

                        list($hours, $minutes) = explode(":", $overtime3Total);
                        $overtimeAll = ($hours * 3600) + ($minutes * 60);
                        $overtime8 = 8 * 3600;
                        $overtimeHoliday = '';

                        if ($overtime1Total != '00:00' || $overtime3Total != '00:00') {
                            
                            if ($overtime3Total > '00:01' && $overtime3Total <= '05:00') {
                                $overtime3Total = '05:00';
                            }
                            if ($overtimeAll > $overtime8 && $overtime3Total > '00:01') {
                                $overtime3Total = '08:00';
                                $overtimeHoliday = $overtimeAll - $overtime8;
                                $Hours = floor($overtimeHoliday / 3600);
                                $Minutes = floor(($overtimeHoliday % 3600) / 60);
                                $overtimeHoliday = sprintf('%02d:%02d', $Hours, $Minutes); 
                            }
                        
                            echo "<tr>
                                    <td style='border: none;  border-bottom: 0.8px solid black; border-left: 1px solid black;'></td>
                                    <td style='border: none;  border-bottom: 0.8px solid black; border-left: 0.8px solid black;'> </td>
                                    <td style='border: none;  border-bottom: 0.8px solid black; border-left: 0.8px solid black;'> </td>
                                    <td style='border: none;  border-bottom: 0.8px solid black; border-left: 0.8px solid black;'> </td>
                                    <td style='border: none;  border-bottom: 0.8px solid black; border-left: 0.8px solid black;'> </td>
                                    <td style='border: none;  border-bottom: 0.8px solid black; border-left: 0.8px solid black; border-right: 1px solid black;'>" .
                                        ($overtime1Total != '00:00' ? "OT x 1.5 = " . $overtime1Total : '') .
                                        ($overtime3Total != '00:00' ? "OT x 1 = " . $overtime3Total : '') .
                                        (!empty($overtimeHoliday) && $overtimeHoliday !='08:00' ? "OT x 3 = " . $overtimeHoliday : '') .
                                    "</td>
                            </tr>";
                        }
                    }

                    // เวลา 8 โมง
                    $startTimeWorking = '-'; 
                    foreach ($data2 as $row2) {
                        if ($row['start_date'] == $row2['date_value']) {
                            $startTimeWorking = $row2['start_working_time'];
                            break;
                        }
                    }
                    $endWorkingTime = '-'; 
                    foreach ($data2 as $row2) {
                        if ($row['start_date'] == $row2['date_value']) {
                            $endWorkingTime = $row2['end_working_time'];
                            break;
                        }
                    }
                    // วันทำงาน
                    if ($row2['working_day'] == 1) {
                        // ถ้า เริ่ม > 8โมง , จบ มากกว่า 8 โมง
                        if ($startTime < strtotime($startTimeWorking) && $endTime > strtotime($startTimeWorking)) {
                            echo "<tr style='border: none;'>
                                    <td style='border:none; border-left: 1px solid black;'>" . date("l", strtotime($row["start_date"])) . "<br>" . ($row["start_date"] ? date("d-m-y", strtotime($row["start_date"])) : '-') . "</td>
                                    <td style='border:none; border-left: 0.8px solid black;'>" . ($startTime ? date("H:i", $startTime) : '-') . "</td>
                                    <td style='border:none; border-left: 0.8px solid black;'>" . ($endTimeFormatted ? $endTimeFormatted : '-') . "</td>
                                    <td style='border:none; border-left: 0.8px solid black;'>" . $overtime1 . "</td>
                                    <td style='border:none; border-left: 0.8px solid black; border-right: 0.8px solid black;'>" . $overtime3 . "</td>
                                    <td style='border-right: 1px solid black;'>" . ''. "</td>
                                </tr>";
                        // ถ้า เริ่ม > 8โมง , จบมากกว่า 8 โมง 
                        } elseif ($startTime > strtotime($startTimeWorking) && $endTime > strtotime($startTimeWorking)) {
                            echo "<tr style='border: none;'>
                                    <td style='border:none; border-left: 1px solid black;'>" . date("l", strtotime($row["start_date"])) . "<br>" . ($row["start_date"] ? date("d-m-y", strtotime($row["start_date"])) : '-') . "</td>
                                    <td style='border:none; border-left: 0.8px solid black;'>" . ($startTime ? date("H:i", $startTime) : '-') . "</td>
                                    <td style='border:none; border-left: 0.8px solid black;'>" . ($endTimeFormatted ? $endTimeFormatted : '-') . "</td>
                                    <td style='border:none; border-left: 0.8px solid black;'>" . $overtime1 . "</td>
                                    <td style='border:none; border-left: 0.8px solid black; border-right: 0.8px solid black;'>" . $overtime3 . "</td>
                                    <td style='border:none; border-right: 1px solid black;'>" . ''. "</td>
                                </tr>";
                            }
                        // เริ่มและจบ < 8โมง
                        else {
                            echo "<tr style='border: none;'>
                                    <td style='border: none; border-left: 1px solid black;'>" . date("l", strtotime($row["start_date"])) . "<br>" . ($row["start_date"] ? date("d-m-y", strtotime($row["start_date"])) : '-') . "</td>
                                    <td style='border:none; border-left: 0.8px solid black;'>" . ($startTime ? date("H:i", $startTime) : '-') . "</td>
                                    <td style='border:none; border-left: 0.8px solid black;'>" . ($startTimeWorking ? date("H:i", strtotime($startTimeWorking)) : '-') . "</td>
                                    <td style='border:none; border-left: 0.8px solid black;'>" . $overtime1 . "</td>
                                    <td style='border:none; border-left: 0.8px solid black; border-right: 0.8px solid black;'>" . $overtime3 . "</td>
                                    <td style='border:none; border-right: 1px solid black;'>" . ''. "</td>
                                </tr>";
                        }
                    } else { 
                        $overtime3 = 0;
                            $overtimeSpecial = $overtime3;
                            $overtimeSpecial = $endTimePlus10 - $startTime;
                            $hours = floor($overtimeSpecial / 3600);
                            $minutes = floor(($overtimeSpecial % 3600) / 60);
                            $overtimeSpecial = sprintf('%02d:%02d', $hours, $minutes);
                            if ($overtimeSpecial != '-') {
                                list($hours, $minutes) = explode(":", $overtimeSpecial);
                                $totalMinutes = $hours * 60 + $minutes;
                                $overtime3Sum += $totalMinutes;
                            }
                            if ($overtimeSpecial != '-') {
                                list($hours, $minutes) = explode(":", $overtimeSpecial);
                                $totalMinutes = $hours * 60 + $minutes;
                                $totalOvertime3Sum += $totalMinutes; 
                            }
                            echo "<tr>
                                    <td style='border: none; border-left: 1px solid black;'>" . date("l", strtotime($row["start_date"])) . "<br>" . ($row["start_date"] ? date("d-m-y", strtotime($row["start_date"])) : '-') . "</td>
                                    <td style='border:none; border-left: 0.8px solid black;'>" . ($startTime ? date("H:i", $startTime) : '-') . "</td>
                                    <td style='border:none; border-left: 0.8px solid black;'>" . ($endTimeFormatted ? $endTimeFormatted : '-') . "</td>
                                    <td style='border:none; border-left: 0.8px solid black;'>" . $overtime1 . "</td>
                                    <td style='border:none; border-left: 0.8px solid black; border-right: 0.8px solid black;'>" . $overtimeSpecial . "</td>
                                    <td style='border:none; border-right: 1px solid black;'>" . '' . "</td>
                                </tr>";
                    }
                        
                    $overtime1Sum = 0;
                    $overtime3Sum = 0;
                    if ($overtime1 != '-') {
                        list($hours, $minutes) = explode(":", $overtime1);
                        $totalMinutes = $hours * 60 + $minutes;
                        $overtime1Sum += $totalMinutes;
                    }
                    if ($overtime3 != '-') {
                        $overtime3Sum = $totalMinutes;
                    }
                    if ($overtime1 != '-') {
                        list($hours, $minutes) = explode(":", $overtime1);
                        $totalMinutes = $hours * 60 + $minutes;
                        $totalOvertime1Sum += $totalMinutes;
                    }
                    if ($overtime3 != '-') {
                        $totalOvertime3Sum = $totalMinutes;
                    }
                }
                // row สุดท้าย
                if ($currentRow == $totalRows) {
                    $totalOvertime1Hours = floor($overtime1Sum / 60);
                    $totalOvertime1Minutes = $overtime1Sum % 60;
                    $overtime1Total = adjustOvertime(sprintf('%02d:%02d', $totalOvertime1Hours, $totalOvertime1Minutes));
    
                    $totalOvertime3Hours = floor($overtime3Sum / 60);
                    $totalOvertime3Minutes = $overtime3Sum % 60;
                    $overtime3Total = adjustOvertime(sprintf('%02d:%02d', $totalOvertime3Hours, $totalOvertime3Minutes));
                    
                    list($hours, $minutes) = explode(":", $overtime3Total);
                    $overtimeAll = ($hours * 3600) + ($minutes * 60);
                    $overtime8 = 8 * 3600;
                    $overtimeHoliday = '';

                        if ($overtime3Total > '00:01' && $overtime3Total <= '05:00') {
                            $overtime3Total = '05:00';
                        }
                        if ($overtimeAll > $overtime8 && $overtime3Total > '00:01') {
                            $overtime3Total = '08:00';
                            $overtimeHoliday = $overtimeAll - $overtime8;
                            $Hours = floor($overtimeHoliday / 3600);
                            $Minutes = floor(($overtimeHoliday % 3600) / 60);
                            $overtimeHoliday = sprintf('%02d:%02d', $Hours, $Minutes);
                        }
                    echo "<tr>
                            <td style='border: none; border-bottom: 1px solid black; border-left: 1px solid black;'></td>
                            <td style='border: none; border-bottom: 1px solid black; border-left: 0.8px solid black;'> </td>
                            <td style='border: none; border-bottom: 1px solid black; border-left: 0.8px solid black;'> </td>
                            <td style='border: none; border-bottom: 1px solid black; border-left: 0.8px solid black;'> </td>
                            <td style='border: none; border-bottom: 1px solid black; border-left: 0.8px solid black;'> </td>
                            <td style='border: none; border-bottom: 1px solid black; border-left: 0.8px solid black; border-right: 1px solid black;'>" .
                                ($overtime1Total != '00:00' ? "OT x 1.5 = " . $overtime1Total . "<br>": '') .
                                ($overtime3Total != '00:00' ? "OT x 1 = " . $overtime3Total . "<br>": '') .
                                (!empty($overtimeHoliday) && $overtimeHoliday != '08:00' ? "OT x 3 = " . $overtimeHoliday : '') .
                          "</td>
                        </tr>";
                    // สรุป OT
                    $totalOvertime1Hours = floor($totalOvertime1Sum / 60);
                    $totalOvertime1Minutes = $totalOvertime1Sum % 60;
                    $overtime1Total = adjustOvertime(sprintf('%02d:%02d', $totalOvertime1Hours, $totalOvertime1Minutes));
    
                    $totalOvertime3Hours = floor($totalOvertime3Sum / 60);
                    $totalOvertime3Minutes = $totalOvertime3Sum % 60;
                    $overtime3Total = adjustOvertime(sprintf('%02d:%02d', $totalOvertime3Hours, $totalOvertime3Minutes));

                    list($hours, $minutes) = explode(":", $overtime3Total);
                    $overtimeAll = ($hours * 3600) + ($minutes * 60);
                    $overtime8 = 8 * 3600;
                    $overtimeHoliday = '';

                    if ($overtime3Total > '00:01' && $overtime3Total <= '05:00') {
                        $overtime3Total = '05:00';
                    }
                    if ($overtimeAll > $overtime8 && $overtime3Total > '00:01') {
                        $overtime3Total = '08:00';
                        $overtimeHoliday = $overtimeAll - $overtime8;
                        $Hours = floor($overtimeHoliday / 3600);
                        $Minutes = floor(($overtimeHoliday % 3600) / 60);
                        $overtimeHoliday = sprintf('%02d:%02d', $Hours, $Minutes);
                    }
                    echo "<tr></tr>",
                    "<tr>
                            <td colspan='1' style='border: 1px solid black; vertical-align: middle;'>Total Over Time</td>
                            <td colspan='3' style='border: 1px solid black';>" .
                                ($overtime1Total != '00:00' ? "OT x 1.5 = " . $overtime1Total . "<br>" : '') .
                            "</td>
                    </tr>",
                    "<tr>
                        <td colspan='1' style='border: 1px solid black'>Holiday Over Time</td>
                        <td colspan='3' style='border: 1px solid black'>" .
                            ($overtime3Total != '00:00' ? "OT x 1 =" . $overtime3Total . "<br>" : '') .
                            (!empty($overtimeHoliday) && $overtimeHoliday != '08:00' ? "OT x 3 = " . $overtimeHoliday : '') .
                        "</td>
                        <td colspan='2' style='border: none'></td>
                    </tr>",
                    "<tr>
                        <td colspan='1' style='border: 1px solid black'>Risk Allowance</td>
                        <td colspan='3' style='border: 1px solid black'>" .
                            (!empty($riskAllowanceTotal) ? $riskAllowanceTotal : '') .
                        "</td>
                        <td colspan='2' style='border: none'></td>
                    </tr>",
                    "<tr>
                        <td colspan='1' style='border: 1px solid black';>Allowance</td>
                        <td colspan='3' style='border: 1px solid black';>" .
                            (!empty($allowanceTotal) ? $allowanceTotal : '') .
                        "</td>
                        <td colspan='2'>_________________________</td>
                    </tr>",
                    "<tr>
                        <td colspan='1'></td>
                        <td colspan='3'></td>
                        <td colspan='2' style'text-align:center;'=>Approval</td>
                    </tr>";
                    }
    
                $previousDate = date("d-m-y", strtotime($row["start_date"]));
                }
            
            echo "</tbody>";
            echo "</table>";
            exit;
        } 
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
            margin-bottom: 10px;
            margin-top: 10px;
            padding: 6px 10px;
            background-color: green;
            color: white;
            border:1px solid green;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .export-data:hover {
            background-color: transparent;
            color: green;
        }
    </style>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <h1>Driver report</h1>
    
    <!-- Box car -->
    <div class="box-container">
        <?php
        if ($result_driver->num_rows > 0) {
            while ($row = $result_driver->fetch_assoc()) {
                $driver = trim($row['username']);
                $activeClass = ($driver === $driverFilter) ? 'active' : '';
                echo '<div class="box ' . $activeClass . '" onclick="filterTasks(\'' . addslashes(htmlspecialchars($driver)) . '\')">';
                echo '<p>' . htmlspecialchars($row['username']) . '</p>';
                echo '</div>';
            }
        } else {
            echo '<p>ยังไม่มีรายการ.</p>';
        }
        ?>
    </div>

    <div class="head">
    <p class="left">คนขับรถ: &nbsp;<strong><?php echo htmlspecialchars($driverFilter); ?></strong></p>
        <form method="get" action="" class="right">

            <input type="hidden" name="driver_name" value="<?php echo htmlspecialchars($driverFilter); ?>">

            <label for="start_date">From:</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
            
            <label for="end_date">To:</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
            
            <button type="submit">Apply</button>
        </form>
    </div>

    <!-- ปุ่ม Export -->
    <a href="?export=true&driver_name=<?php echo urlencode($driverFilter); ?>&start_date=<?php echo urlencode($startDate); 
        ?>&end_date=<?php echo urlencode($endDate); ?>" class="export-data">
        <span class="material-icons">file_upload</span>Export
    </a>

    <!-- Table -->
    <div class="table-container">
    <table>
        <thead>
            <tr>
                <th scope="col" rowspan="2">Date</th>
                <th scope="col" colspan="2">Time</th>
                <th scope="col" colspan="3">Over Time</th>
            </tr>
            <tr>
                <th scope="col">From</th>
                <th scope="col">To</th>
                <th scope="col">x1.5</th>
                <th scope="col">Holiday</th>
                <th scope="col">Detail OT</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if (empty($driverFilter)) {
            echo "<tr><td colspan='6' style='font-size: 20px;'>กรุณาเลือกคนขับรถเพื่อดูข้อมูล</td></tr>";
        } else {
            if ($result->num_rows > 0) {
                $previousDate = ''; 
                $overtime1Sum = 0; 
                $overtime3Sum = 0; 
                $totalOvertime1Sum = 0; 
                $totalOvertime3Sum = 0; 
                $currentRow = 0;
                $totalRows = $result->num_rows;
                $allowanceTotal = 0;
                $riskAllowanceTotal = 0;

                // Function ปัดนาที
                function adjustOvertime($overtime) {
                    if ($overtime != '-') {
                        list($hours, $minutes) = explode(":", $overtime);
                        if ($minutes == 59) {
                            $hours++; 
                            $minutes = 0; 
                        }
                        return sprintf('%02d:%02d', $hours, $minutes);
                    }
                    return $overtime;
                    }
        
                while ($row = $result->fetch_assoc()) {
                    $currentRow++;

                    $allowanceTotal += isset($row['allowance']) ? $row['allowance'] : 0;
                    $riskAllowanceTotal += isset($row['risk_allowance']) ? $row['risk_allowance'] : 0;
                    
                    $totalDistance = '-';
                    if ($row['mileage_at_destination'] && $row['mileage']) {
                        $totalDistance = $row['mileage_at_destination'] - $row['mileage'];
                    }
        
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

                    // คำนวณเวลารวม
                    $totalTime = '-';
                    if (!empty($row['end_time']) && !empty($row['start_time'])) {
                        $timeDiff = $endTime - $startTime;
                        $hours = floor($timeDiff / 3600);
                        $minutes = floor(($timeDiff % 3600) / 60);
                        $totalTime = sprintf('%02d:%02d', $hours, $minutes);
                    }
        
                    // คำนวณ OT
                    $overtime1 = '-';
                    $overtime3 = '-';
                    foreach ($data2 as $row2) {
                        if ($row['start_date'] == $row2['date_value']) {
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
                            if ($row2['working_day'] == 1) { // วันปกติ

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
                    $overtimeSpecial = 0;

                    // วันที่เดียวกัน หรือ บันทัดที่สองลงไป
                    if ($previousDate == date("d-m-y", strtotime($row["start_date"]))) {
                        if ($overtime1 != '-') {
                            list($hours, $minutes) = explode(":", $overtime1);
                            $totalMinutes = $hours * 60 + $minutes;
                            $overtime1Sum += $totalMinutes;
                        }
                        if ($overtime3 != '-') {
                            list($hours, $minutes) = explode(":", $overtime3);
                            $totalMinutes = $hours * 60 + $minutes;
                            $overtime3Sum += $totalMinutes;
                        }
                        if ($overtime1 != '-') {
                            list($hours, $minutes) = explode(":", $overtime1);
                            $totalMinutes = $hours * 60 + $minutes;
                            $totalOvertime1Sum += $totalMinutes; 
                        }
                        if ($overtime3 != '-') {
                            list($hours, $minutes) = explode(":", $overtime3);
                            $totalMinutes = $hours * 60 + $minutes;
                            $totalOvertime3Sum += $totalMinutes; 
                        }

                        // เวลา 8 โมง
                        $startTimeWorking = '-'; 
                            foreach ($data2 as $row2) {
                                if ($row['start_date'] == $row2['date_value']) {
                                    $startTimeWorking = $row2['start_working_time'];
                                    break;
                                }
                            }

                        $endWorkingTime = '-'; 
                        foreach ($data2 as $row2) {
                            if ($row['start_date'] == $row2['date_value']) {
                                $endWorkingTime = $row2['end_working_time'];
                                break;
                            }
                        }

                        if ($row2['working_day'] == 1) { //วันทำงานปกติ
                                //  เวลาอยู่ในระหว่างวัน >8-17< 
                                if ($startTime >= $startTimeWorking && $endTimeFormatted <= $endWorkingTime) {
                                    echo "<tr>
                                            <td></td>
                                            <td>" . ($startTime ? date("H:i", $startTime) : '-') . "</td>
                                            <td>" . ($endTimeFormatted ? $endTimeFormatted : '-') . "</td>
                                            <td>" . $overtime1 . "</td>
                                            <td>" . $overtime3 . "</td>
                                            <td>" . '' . "</td>
                                        </tr>";
                                } else { // เวลาอยู่นอกเวลาทำงาน <8-17>
                                    echo "<tr>
                                            <td></td>
                                            <td>" . ($endWorkingTime ? date("H:i", strtotime($endWorkingTime)) : '-') . "</td>
                                            <td>" . ($endTimeFormatted ? $endTimeFormatted : '-') . "</td>
                                            <td>" . $overtime1 . "</td>
                                            <td>" . $overtime3 . "</td>
                                            <td>" . '' . "</td>
                                        </tr>";
                                }
                            } else { // วันหยุด
                                $overtime3 = '';
                                $overtimeSpecial = $overtime3;
                                $overtimeSpecial = $endTimePlus10 - $startTime;
                                $hours = floor($overtimeSpecial / 3600);
                                $minutes = floor(($overtimeSpecial % 3600) / 60);
                                $overtimeSpecial = sprintf('%02d:%02d', $hours, $minutes);
                                if ($overtimeSpecial != '-') {
                                    list($hours, $minutes) = explode(":", $overtimeSpecial);
                                    $totalMinutes = $hours * 60 + $minutes;
                                    $overtime3Sum += $totalMinutes;
                                }
                                if ($overtimeSpecial != '-') {
                                    list($hours, $minutes) = explode(":", $overtimeSpecial);
                                    $totalMinutes = $hours * 60 + $minutes;
                                    $totalOvertime3Sum += $totalMinutes; 
                                }
                                echo "<tr>
                                        <td></td>
                                        <td>" . ($startTime ? date("H:i", $startTime) : '-') . "</td>
                                        <td>" . ($endTimeFormatted ? $endTimeFormatted : '-') . "</td>
                                        <td>" . $overtime1 . "</td>
                                        <td>" . $overtimeSpecial . "</td>
                                        <td>" . '' . "</td>
                                    </tr>";
                            }

                    // ไม่ใช่วันเดียวกัน หรือ ข้อมูลบันทัดแรก
                    } else { 
                        if ($previousDate != '') {
                            $totalOvertime1Hours = floor($overtime1Sum / 60);
                            $totalOvertime1Minutes = $overtime1Sum % 60;
                            $overtime1Total = adjustOvertime(sprintf('%02d:%02d', $totalOvertime1Hours, $totalOvertime1Minutes));
        
                            $totalOvertime3Hours = floor($overtime3Sum / 60);
                            $totalOvertime3Minutes = $overtime3Sum % 60;
                            $overtime3Total = adjustOvertime(sprintf('%02d:%02d', $totalOvertime3Hours, $totalOvertime3Minutes));

                            list($hours, $minutes) = explode(":", $overtime3Total);
                            $overtimeAll = ($hours * 3600) + ($minutes * 60);
                            $overtime8 = 8 * 3600;
                            $overtimeHoliday = '';

                            if ($overtime1Total != '00:00' || $overtime3Total != '00:00') {
                                
                                if ($overtime3Total > '00:01' && $overtime3Total <= '05:00') {
                                    $overtime3Total = '05:00';
                                }
                                if ($overtimeAll > $overtime8 && $overtime3Total > '00:01') {
                                    $overtime3Total = '08:00';
                                    $overtimeHoliday = $overtimeAll - $overtime8;
                                    $Hours = floor($overtimeHoliday / 3600);
                                    $Minutes = floor(($overtimeHoliday % 3600) / 60);
                                    $overtimeHoliday = sprintf('%02d:%02d', $Hours, $Minutes); 
                                }
                                echo "<tr>
                                    <td colspan='5'></td>
                                    <td colspan='1'>" .
                                        ($overtime1Total != '00:00' ? "OT x 1.5 = " . $overtime1Total . "<br>" : '') .
                                        ($overtime3Total != '00:00' ? "OT x 1 = " . $overtime3Total . "<br>" : '') .
                                        (!empty($overtimeHoliday) && $overtimeHoliday != '08:00' ? "OT x 3 = " . $overtimeHoliday : '') .
                                    "</td>
                                </tr>";
                            }
                        }

                        // เวลา 8 โมง
                        $startTimeWorking = '-'; 
                        foreach ($data2 as $row2) {
                            if ($row['start_date'] == $row2['date_value']) {
                                $startTimeWorking = $row2['start_working_time'];
                                break;
                            }
                        }
                        $endWorkingTime = '-'; 
                        foreach ($data2 as $row2) {
                            if ($row['start_date'] == $row2['date_value']) {
                                $endWorkingTime = $row2['end_working_time'];
                                break;
                            }
                        }

                            // วันทำงาน
                            if ($row2['working_day'] == 1) {    
                                
                                if ($startTime < strtotime($startTimeWorking) && $endTime > strtotime($startTimeWorking)) { // ถ้า เริ่ม < 8โมง , จบ มากกว่า 8 โมง
                                    echo "<tr>
                                            <td>" . date("l", strtotime($row["start_date"])) . "<br>" . ($row["start_date"] ? date("d-m-y", strtotime($row["start_date"])) : '-') . "</td>
                                            <td>" . ($startTime ? date("H:i", $startTime) : '-') . "</td>
                                            <td>" . ($endTimeFormatted ? $endTimeFormatted : '-') . "</td>
                                            <td>" . $overtime1 . "</td>
                                            <td>" . $overtime3 . "</td>
                                            <td>" . ''. "</td>
                                        </tr>";
                                
                                } elseif ($startTime > strtotime($startTimeWorking) && $endTime > strtotime($startTimeWorking)) { // ถ้า เริ่ม > 8โมง , จบมากกว่า 8 โมง 
                                    echo "<tr>
                                            <td>" . date("l", strtotime($row["start_date"])) . "<br>" . ($row["start_date"] ? date("d-m-y", strtotime($row["start_date"])) : '-') . "</td>
                                            <td>" . ($startTime ? date("H:i", $startTime) : '-') . "</td>
                                            <td>" . ($endTimeFormatted ? $endTimeFormatted : '-') . "</td>
                                            <td>" . $overtime1 . "</td>
                                            <td>" . $overtime3 . "</td>
                                            <td>" . ''. "</td>
                                        </tr>";
                                    }
                                
                                else { // เริ่มและจบ < 8โมง
                                    echo "<tr>
                                            <td>" . date("l", strtotime($row["start_date"])) . "<br>" . ($row["start_date"] ? date("d-m-y", strtotime($row["start_date"])) : '-') . "</td>
                                            <td>" . ($startTime ? date("H:i", $startTime) : '-') . "</td>
                                            <td>" . ($startTimeWorking ? date("H:i", strtotime($startTimeWorking)) : '-') . "</td>
                                            <td>" . $overtime1 . "</td>
                                            <td>" . $overtime3 . "</td>
                                            <td>" . ''. "</td>
                                        </tr>";
                                }
                            // วันหยุด
                            } else { 
                                $overtime3 = 0;
                                $overtimeSpecial = $overtime3;
                                $overtimeSpecial = $endTimePlus10 - $startTime;
                                $hours = floor($overtimeSpecial / 3600);
                                $minutes = floor(($overtimeSpecial % 3600) / 60);
                                $overtimeSpecial = sprintf('%02d:%02d', $hours, $minutes);
                                if ($overtimeSpecial != '-') {
                                    list($hours, $minutes) = explode(":", $overtimeSpecial);
                                    $totalMinutes = $hours * 60 + $minutes;
                                    $overtime3Sum += $totalMinutes;
                                }
                                if ($overtimeSpecial != '-') {
                                    list($hours, $minutes) = explode(":", $overtimeSpecial);
                                    $totalMinutes = $hours * 60 + $minutes;
                                    $totalOvertime3Sum += $totalMinutes; 
                                }
                                echo "<tr>
                                        <td>" . date("l", strtotime($row["start_date"])) . "<br>" . ($row["start_date"] ? date("d-m-y", strtotime($row["start_date"])) : '-') . "</td>
                                        <td>" . ($startTime ? date("H:i", $startTime) : '-') . "</td>
                                        <td>" . ($endTimeFormatted ? $endTimeFormatted : '-') . "</td>
                                        <td>" . $overtime1 . "</td>
                                        <td>" . $overtimeSpecial . "</td>
                                        <td>" . '' . "</td>
                                    </tr>";
                            }
                        
                        $overtime1Sum = 0;
                        $overtime3Sum = 0;
                        if ($overtime1 != '-') {
                            list($hours, $minutes) = explode(":", $overtime1);
                            $totalMinutes = $hours * 60 + $minutes;
                            $overtime1Sum += $totalMinutes;
                        }
                        if ($overtime3 != '-') {
                            $overtime3Sum = $totalMinutes;
                        }
                        if ($overtime1 != '-') {
                            list($hours, $minutes) = explode(":", $overtime1);
                            $totalMinutes = $hours * 60 + $minutes;
                            $totalOvertime1Sum += $totalMinutes;
                        }
                        if ($overtime3 != '-') {
                            $totalOvertime3Sum = $totalMinutes;
                        }
                    }
                    // row สุดท้าย แสดงผลรวม OT
                    if ($currentRow == $totalRows) {
                        $totalOvertime1Hours = floor($overtime1Sum / 60);
                        $totalOvertime1Minutes = $overtime1Sum % 60;
                        $overtime1Total = adjustOvertime(sprintf('%02d:%02d', $totalOvertime1Hours, $totalOvertime1Minutes));
        
                        $totalOvertime3Hours = floor($overtime3Sum / 60);
                        $totalOvertime3Minutes = $overtime3Sum % 60;
                        $overtime3Total = adjustOvertime(sprintf('%02d:%02d', $totalOvertime3Hours, $totalOvertime3Minutes));

                        list($hours, $minutes) = explode(":", $overtime3Total);
                        $overtimeAll = ($hours * 3600) + ($minutes * 60);
                        $overtime8 = 8 * 3600;

                        $overtimeHoliday = '';

                            if ($overtime3Total > '00:01' && $overtime3Total <= '05:00') {
                                $overtime3Total = '05:00';
                            }
                            if ($overtimeAll > $overtime8 && $overtime3Total > '00:01') {
                                    $overtime3Total = '08:00';
                                    $overtimeHoliday = $overtimeAll - $overtime8;
                                    $Hours = floor($overtimeHoliday / 3600);
                                    $Minutes = floor(($overtimeHoliday % 3600) / 60);
                                    $overtimeHoliday = sprintf('%02d:%02d', $Hours, $Minutes);
                            }
                            echo "<tr>
                                <td colspan='5'></td>
                                <td colspan='1'>" .
                                    ($overtime1Total != '00:00' ? "OT x 1.5 = " . $overtime1Total . "<br>" : '') .
                                    ($overtime3Total != '00:00' ? "OT x 1 = " . $overtime3Total . "<br>" : '') .
                                    (!empty($overtimeHoliday) && $overtimeHoliday != '08:00' ? "OT x 3 = " . $overtimeHoliday : '') .
                                "</td>
                            </tr>";


                        // ตารางสรุป OT ทั้ง week
                        $totalOvertime1Hours = floor($totalOvertime1Sum / 60);
                        $totalOvertime1Minutes = $totalOvertime1Sum % 60;
                        $overtime1Total = adjustOvertime(sprintf('%02d:%02d', $totalOvertime1Hours, $totalOvertime1Minutes));
        
                        $totalOvertime3Hours = floor($totalOvertime3Sum / 60);
                        $totalOvertime3Minutes = $totalOvertime3Sum % 60;
                        $overtime3Total = adjustOvertime(sprintf('%02d:%02d', $totalOvertime3Hours, $totalOvertime3Minutes));

                        list($hours, $minutes) = explode(":", $overtime3Total);
                        $overtimeAll = ($hours * 3600) + ($minutes * 60);
                        $overtime8 = 8 * 3600;
                        $overtimeHoliday = '';

                            if ($overtime3Total > '00:01' && $overtime3Total <= '05:00') {
                                $overtime3Total = '05:00';
                            }
                            if ($overtimeAll > $overtime8 && $overtime3Total > '00:01') {
                                $overtime3Total = '08:00';
                                $overtimeHoliday = $overtimeAll - $overtime8;
                                $Hours = floor($overtimeHoliday / 3600);
                                $Minutes = floor(($overtimeHoliday % 3600) / 60);
                                $overtimeHoliday += $overtimeHoliday;
                                $overtimeHoliday = sprintf('%02d:%02d', $Hours, $Minutes);
                            }
                        echo "<tr>
                                <td colspan='1'>Total</td>
                                <td colspan='4'>" .
                                    ($overtime1Total != '00:00' ? "OT x 1.5 = " . $overtime1Total . "<br>" : '') .
                                "</td>
                                <td colspan='1'></td>
                        </tr>",
                        "<tr>
                            <td colspan='1'>Holiday Over Time</td>
                            <td colspan='4'>" .
                                ($overtime3Total != '00:00' ? "OT x 1 = " . $overtime3Total . "<br>" : '') .
                                (!empty($overtimeHoliday) && $overtimeHoliday != '08:00' ? "OT x 3 = " . $overtimeHoliday : '') .
                            "</td>
                            </td>
                            <td colspan='1'></td>
                        </tr>",
                        "<tr>
                            <td colspan='1'>Risk Allowance</td>
                            <td colspan='4'>"
                                . (!empty($riskAllowanceTotal) ? $riskAllowanceTotal : '') .
                            "</td>
                            <td colspan='1'></td>
                        </tr>",
                        "<tr>
                            <td colspan='1'>Allowance</td>
                            <td colspan='4'>"
                                . (!empty($allowanceTotal) ? $allowanceTotal : '') .
                            "</td>
                            <td colspan='1'></td>
                        </tr>";
                        }
        
                    $previousDate = date("d-m-y", strtotime($row["start_date"]));
                }
            }
        }
    ?>
    </table>
        <!-- Pagination -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?driver_name=<?php echo urlencode($driverFilter); ?>&page=<?php echo $page - 1; ?>">Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?driver_name=<?php echo urlencode($driverFilter); ?>&page=<?php echo $i; ?>"
                   <?php echo ($i == $page) ? 'style="font-weight: bold;"' : ''; ?>>
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?driver_name=<?php echo urlencode($driverFilter); ?>&page=<?php echo $page + 1; ?>">Next</a>
            <?php endif; ?>

            <!-- Back button -->
            <a href="admin.php" class="back-btn">Back</a>
        </div>
    </div>

    <script>
        function filterTasks(driver) {
            console.log("Selected driver:", driver);
            window.location.href = "?driver_name=" + encodeURIComponent(driver.trim());
            document.querySelector('.left').textContent = "คนขับรถ: " + driver;
        }

        function exportData() {
            window.location.href = "export_driver.php?driver_name=<?php echo urlencode($driverFilter); ?>";
        }

    </script>
</body>
</html>