<?php
session_start();
include 'config.php';
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}


if (isset($_GET['taskId'])) {
    $taskId = $_GET['taskId'];
} else {
    echo "Task ID is missing.";
    exit;
}

if (isset($_GET['bossId'])) {
    $_SESSION['selected_boss_id'] = $_GET['bossId'];
    header("Location: pin.php?taskId=" . $_GET['taskId']);
    exit();
}

if (isset($_SESSION['authorized_task_ids']) && in_array($taskId, $_SESSION['authorized_task_ids'])) {
    header("Location: destination.php?taskId=" . $taskId . "&bossId=" . $bossId . "&end_time=" . $end_time);
    exit();
}


// ดึงข้อมูลหัวหน้างาน
$sql = "SELECT * FROM dv_boss";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Boss</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap');
        body {
            font-family: 'Noto Sans Thai', sans-serif;
            background: #f1f1f1;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;  
            height: 100vh;
            flex-direction: column; 
            box-sizing: border-box;
        }

        h1 {
            color: #333;
            text-align: center;
            margin-top: 40px;
            margin-bottom: 20px;
            font-size: 2rem;
            font-weight: 600;
        }

        .box-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr); 
            gap: 30px; 
            padding: 20px;
            max-width: 1200px;
            width: 90%;
            margin-top: 20px;
        }

        .box {
            background-color: #fff;
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 50px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.15);
        }

        .box p {
            font-size: 1.1rem;
            color: #333;
            font-weight: 500;
            margin: 0;
        }

        .no-data {
            text-align: center;
            font-size: 1.2rem;
            color: #888;
        }

        .back-button {
            background-color: transparent;
            color: #4CAF50;
            border: 1px solid #4CAF50;
            padding: 10px 20px;
            font-size: 1rem;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #45a049;
            color: white;
        }

        @media (max-width: 768px) {
            .box-container {
                display: grid;
                grid-template-columns: repeat(2, 1fr); 
                gap: 20px; 
                padding: 10px;
                max-width: 500px;
                width: 90%;
                margin-top: 20px;
            }
            .box {
                padding: 35px;
            }

        }

    </style>
</head>
<body>
    <h1>เลือกหัวหน้างาน</h1>

    <!-- Box -->
    <div class="box-container">
    <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<form action='pin.php' method='GET' class='boss-form'>";
                echo "<div class='box' onclick='submitForm(this)'>"; 
                echo "<p>" . $row['boss_name'] . "</p>"; 
                echo "<input type='hidden' name='boss_id' value='" . $row['boss_id'] . "' />";
                echo "<input type='hidden' name='task_id' value='" . $_GET['taskId'] . "' />";
                echo "</div>";
                echo "</form>";
            }
        } else {
            echo "<div class='no-data'>ไม่พบข้อมูลหัวหน้างาน</div>";
        }
        ?>
    </div>

    <button class="back-button" onclick="window.location.href = 'tasklist.php'">ย้อนกลับ</button>
    
    <script>
        function submitForm(boxElement) {
            var form = boxElement.closest('form'); 
            form.submit(); 
        }
    </script>
</body>
</html>
