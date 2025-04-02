<?php
session_start();
include('config.php');

if (isset($_GET['task_id'])) {
    $taskId = $_GET['task_id'];

    $sql = "SELECT
                t.start_date,
                t.location,
                t.start_time,
                t.mileage,
                t.destination_location,
                t.end_time,
                t.mileage_at_destination,
                t.driver_name,
                t.driver_image,
                t.destination_image,
                t.accessories,
                t.trip_types,
                t.allowance,
                t.risk_allowance,
                u.username,
                u.profile_image
            FROM dv_tasks t
            LEFT JOIN dv_users u ON t.driver_name = u.username  
            WHERE t.task_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $taskId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $status = "Waiting Approve";
        $statusClass = "status-wait-approve";
        if (!empty($row['destination_location']) && !empty($row['end_time'])) {
            $status = "Approved";
            $statusClass = "status-approved";
        }

        $startTime = date("H:i", strtotime($row["start_time"]));
        $endTime = date("H:i", strtotime($row["end_time"]));

        $totalDistance = '-';
        if ($row['mileage_at_destination'] && $row['mileage']) {
            $totalDistance = $row['mileage_at_destination'] - $row['mileage'];
        }
        $username = $row['username'] ? $row['username'] : 'Unknown User';   
        $profileImage = $row['profile_image'] ? '' . $row['profile_image'] : 'img/default.png'; // รูปโปรไฟล์


        $output = "
     
            <div class='headtext'> 
                <div class='user-profile'>
                    <img src='$profileImage' alt='Profile Image' width='60' height='60'>
                        <p>$username</p>
                        <h6>Driver</h6>
                </div>
                <div class='headinside'>
                    <p>From</p>
                    <p> > </p>
                    <p>To</p>
                </div>
            </div>
            <div class='container'>
                <div class='left'>
                    <p><i class='fas fa-map-marker-alt'></i>&nbsp;" . $row["location"] . "</p>
                    <p class='mileage'><i class='fas fa-tachometer-alt'></i>&nbsp;" . number_format($row["mileage"]) . "</p>
                    <p><i class='fas fa-clock'></i>&nbsp;" . $startTime . "</p><br>
                    <div><img src='" . $row["driver_image"] . "' alt='Driver Image' width='200' height='150'></div>
                </div>
                <div class='right'>
                    <p><i class='fas fa-map-marker-alt'></i>&nbsp;" . ($row["destination_location"] ? $row["destination_location"] : '-') . "</p>
                    <p class='mileage'><i class='fas fa-tachometer-alt'></i>&nbsp;" . ($row["mileage_at_destination"] ? number_format($row["mileage_at_destination"]) : '-') . "</p>
                    <p><i class='fas fa-clock'></i>&nbsp;" . $endTime . "</p><br>
                    <div><img src='" . $row["destination_image"] . "' alt='Destination Image' width='200' height='150'></div>
                </div>
            </div>
            
            <div class='other'>
                <p><strong>" . ($row["allowance"] ? "Allowance : " . $row["allowance"] : '') . "</strong></p>
            </div>
            <div class='other'>
                <p><strong>" . ($row["risk_allowance"] ? "Risk Allowance : " . $row["risk_allowance"] : '') . "</strong></p>
            </div>

            <div class='total-distance'>
                <h6>Total Distance</h6> <p><strong>" . ($totalDistance === '-' ? $totalDistance : number_format($totalDistance)) . " km</strong></p>
            </div>
            <div class='status $statusClass'>
                <h6>Status</h6> <p><strong>$status</strong><p>
            </div>
        ";
        echo $output;
    } else {
        echo "ไม่พบข้อมูล";
    }
    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


<style>
        .container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin: 8px;
    }

    .container .right {
        margin-top: 0;
    }

    .left, .right {
        padding: 10px;
        display: flex;
        flex-direction: column;
        text-align: center;
        align-items: center;
    }

    .left img, .right img {
        max-width: 100%;
        height: 80px;
    }

    .left p:nth-child(1), .right p:nth-child(1) {
        margin-bottom: 10px;
        white-space: nowrap;         
        overflow: hidden;            
        text-overflow: ellipsis;     
        max-width: 160px;           
    }

    .left i, .right i {
        margin-right: 8px; 
        color: gray; 
    }

    p.mileage {
        font-size: 2.5rem;
        white-space: nowrap; 
    }

    p.mileage i {
        font-size: 1.3rem;
    }

    .left p.mileage {
        color: orange;
    }

    .right p.mileage {
        color: green;
    }

    .status h6 {
        color: gray;
        font-size: 16px;
    }
    .status-approved {
        color: green;
        text-align: center;
        font-size: 2rem;
        margin-bottom: 20px;
    }

    .status-wait-approve {
        color: orange;
        text-align: center;
        font-size: 2rem;
        margin-bottom: 20px;
    }

    .total-distance {
        margin-top: 20px;
        text-align: center;
        font-size: 1.5rem;
        color: #333;
        margin-bottom: 20px;
    }

    .total-distance h6 {
        font-size: 16px;
        color: gray;
    }

    .total-distance p {
        font-size: 2rem;
        font-weight: bold;
    }

    .headtext {
        display: flex;
        font-size: 30px;
        font-weight: bold;
    }

    .headinside {
        display: flex;
        justify-content: space-around;
        align-items: center;
        width: 80%;
        margin-top: 40px;
        margin-left: 40px;
        margin-bottom: 40px;
    }

    /* แสดงรูปภาพ */
    .user-profile {
        text-align: center;
        position: absolute;
        margin-bottom: 0;
    }

    .user-profile img {
        border-radius: 50%;
        margin-bottom: 0;
    }

    .user-profile p {

        font-size: 0.8rem;
        font-weight: bold;
    }

    .user-profile h6 {
        font-size: 12px;
    }
 
    .other{
        margin-top: 8px;
        text-align: center;
        font-size: 1.2rem;
        color: #333;
        margin-bottom: 8px;
    }

    .total-distance h6 {
        font-size: 16px;
        color: gray;
    }

    .total-distance p {
        font-size: 2rem;
        font-weight: bold;
    }
 
</style>
</head>
<body>
</body>
</html>