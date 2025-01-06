<?php
session_start();
include('config.php');
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['taskId'])) {
    $taskId = $_GET['taskId'];

    if (isset($_SESSION['pin'])) {
        $pin = $_SESSION['pin'];
    } else {
        echo "PIN not found in session!";
        exit();
    }

    $end_time = isset($_SESSION['end_time']) ? $_SESSION['end_time'] : date("H:i:s");

} else {
    echo "ไม่พบข้อมูลใน URL";
    exit();
}

// echo "Task ID: $taskId <br> Pin: $pin <br> End Time: $end_time";

// echo "Task ID: " . $taskId . "<br>";
// echo "Pin: " . $pin . "<br>";
// echo "End Time: " . $end_time . "<br>";

    $sql = "SELECT * FROM dv_tasks WHERE task_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $taskId);
    $stmt->execute();
    $result = $stmt->get_result();
    $task = $result->fetch_assoc();

    if ($task) {
        $driverName = $task['driver_name'];
        $mileage = $task['mileage'];
        $location = $task['location'];
        $startDate = $task['start_date'];
        $startTime = $task['start_time'];
        $driverImage = $task['driver_image'];
        $accessories = explode(",", $task['accessories']);
        $tripTypes = explode(",", $task['trip_types']);
    } else {
        echo "ไม่พบข้อมูลงาน";
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $destinationLocation = $_POST['destinationLocation'];
        $mileageAtDestination = $_POST['mileageAtDestination'];
    
        if ($mileageAtDestination < $mileage) {
            $error_message = "เลขไมล์เมื่อถึงที่หมายไม่ควรน้อยกว่าเลขไมล์ก่อนเดินทาง";
        } else {
            if (empty($end_time)) {
                $end_time = date("Y-m-d H:i:s");
            }
    
            $sql_check_pin = "SELECT boss_name FROM dv_boss WHERE pin = ?";
            $stmt_check = $conn->prepare($sql_check_pin);
            $stmt_check->bind_param("s", $pin);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                $row = $result_check->fetch_assoc();

                $carUser = $row['boss_name']; 
            } else {
                $carUser = $_SESSION['username'];
            }

            if (empty($carUser)) {

                $carUser = $pin;
            }

    
            if (isset($_FILES["destinationImage"]) && $_FILES["destinationImage"]["error"] == 0) {
                $targetDir = "uploads/";
                $fileExtension = pathinfo($_FILES["destinationImage"]["name"], PATHINFO_EXTENSION);
                $newFileName = date('Y-m-d_H-i-s') . "." . $fileExtension;
                $destinationImage = $targetDir . $newFileName;
    
                if (move_uploaded_file($_FILES["destinationImage"]["tmp_name"], $destinationImage)) {
                    $sql_update = "UPDATE dv_tasks SET 
                                    destination_location = ?, 
                                    mileage_at_destination = ?, 
                                    destination_image = ?, 
                                    end_time = ?,
                                    pin = ? 
                                    WHERE task_id = ?";
                    $stmt_update = $conn->prepare($sql_update);
                    $stmt_update->bind_param("ssssii", $destinationLocation, $mileageAtDestination, $destinationImage, $end_time, $pin, $taskId);
    
                    if ($stmt_update->execute()) {
                        echo "<script>
                                alert('บันทึกข้อมูลสำเร็จ');
                                window.location.href = 'tasklist.php';
                              </script>";
                    } else {
                        echo "เกิดข้อผิดพลาดในการอัปเดตข้อมูล: " . $stmt_update->error;
                    }
                } else {
                    echo "ไม่สามารถอัปโหลดไฟล์ภาพได้.";
                }
            }
        }
    }

    // ดึงข้อมูลชื่อคนขับและทะเบียนรถ
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        $sql_driver = "
            SELECT u.username, c.carName, c.carId 
            FROM dv_driver_car dc
            JOIN dv_users u ON dc.driver_id = u.user_id
            JOIN dv_car c ON dc.car_id = c.carId
            WHERE u.username = ?";
        $stmt_driver = $conn->prepare($sql_driver);
        $stmt_driver->bind_param("s", $username);
        $stmt_driver->execute();
        $stmt_driver->bind_result($driverName, $carName, $carId);
        $stmt_driver->fetch();

    } else {
        $driverName = ''; 
        $carName = '';
        $carId = '';
    }
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ฟอร์มข้อมูลจุดหมาย</title>
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
            color: #333;
        }

        .form-container {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            margin-top: 20px;
            background-color: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            font-weight: bold;
            margin-bottom: 8px;
            display: block;
        }

        input[type="text"], input[type="number"], input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 50%;
        }

        button:hover {
            background-color: #45a049;
        }

        .form-container button {
            width: auto;
            margin: 0 auto;
            display: block;
        }

        .image-preview {
            margin-top: 20px;
            text-align: center;
        }

        .image-preview img {
            width: 100%;
            max-width: 300px;
            border-radius: 10px;
        }

        /* Box color */
        #driverName , #carName, #mileage, #location {
            background-color: #b6bbbb;
        }
    </style>
</head>
<body>

<h1>ฟอร์มข้อมูลจุดหมาย</h1>

<div class="form-container">
    <form id="destinationForm" method="POST" enctype="multipart/form-data">
        <?php echo "<strong>วันที่: $startDate</strong>" ?>
        <label for="driverName">ชื่อคนขับรถ</label>
        <input type="text" id="driverName" name="driverName" value="<?php echo $driverName; ?>" readonly>
        
        <label for="carName">ชื่อรถ</label>
        <input type="text" id="carName" name="carName" value="<?php echo htmlspecialchars($carName); ?>" readonly>

        <label for="mileage">เลขไมล์ก่อนเดินทาง</label>
        <input type="number" id="mileage" name="mileage" value="<?php echo $mileage; ?>" readonly>

        <label for="location">สถานที่ต้นทาง</label>
        <input type="text" id="location" name="location" value="<?php echo $location; ?>" readonly>

        <label for="driverImage">รูปถ่ายเลขไมล์รถ (ก่อน)</label>
        <div class="image-preview" id="imagePreview">
            <img src="<?php echo $driverImage; ?>" alt="Driver Image" style="max-width: 300px; height: auto;">
        </div>

        <label>Private/Official:</label>
        <div>
            <label><input type="checkbox" name="accessory[]" value="Private" <?php echo in_array('Private', $accessories) ? 'checked' : ''; ?> disabled> Private</label>
            <label><input type="checkbox" name="accessory[]" value="Official" <?php echo in_array('Official', $accessories) ? 'checked' : ''; ?> disabled> Official</label>
        </div>

        <label>จุดประสงค์ในการเดินทาง:</label>
        <div>
            <label><input type="checkbox" name="tripType[]" value="Go to work" <?php echo in_array('Go to work', $tripTypes) ? 'checked' : ''; ?> disabled> Go to work</label>
            <label><input type="checkbox" name="tripType[]" value="Comeback Apart" <?php echo in_array('Comeback Apart', $tripTypes) ? 'checked' : ''; ?> disabled> Comeback Apart</label>
            <label><input type="checkbox" name="tripType[]" value="Other" <?php echo in_array('Other', $tripTypes) ? 'checked' : ''; ?> disabled> Other</label>
        </div>
        
        <span id="mileageError" style="color: red; display: none;">เลขไมล์เมื่อถึงที่หมายไม่ควรน้อยกว่าเลขไมล์ก่อนเดินทาง</span>
        <label for="mileageAtDestination">เลขไมล์เมื่อถึงที่หมาย</label>
        <input type="number" id="mileageAtDestination" name="mileageAtDestination" max="999999" placeholder="กรุณากรอกเลขไมล์" required>

        <label for="destinationLocation">สถานที่ปลายทาง</label>
        <input type="text" id="destinationLocation" name="destinationLocation" placeholder="กรุณากรอกสถานที่ปลายทาง" required>

        <label for="destinationImage">ถ่ายรูปเลขไมล์เมื่อถึงที่หมาย</label>
        <input type="file" id="destinationImage" name="destinationImage" accept="image/*" capture="camera" required>
        
        
        <div class="image-preview" id="imagePreview2"></div>
        <input type="hidden" name="taskId" value="<?php echo isset($taskId) ? $taskId : ''; ?>"><br>

        <button type="submit">บันทึกข้อมูล</button><br>
        <button type="button" class="home-button" onclick="window.location.href='tasklist.php'">Home</button>
    </form>
</div>

<script>
    document.getElementById("destinationImage").addEventListener("change", function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imagePreview = document.getElementById("imagePreview2");
                imagePreview.innerHTML = `<img src="${e.target.result}" alt="Preview Image">`;
            };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById("destinationForm").onsubmit = function(event) {
    const mileageAtDestination = document.getElementById("mileageAtDestination").value;
    const mileage = <?php echo $mileage; ?>;  

    // ตรวจสอบว่าเลขไมล์ปลายทางมากกว่าหรือเท่ากับเลขไมล์ก่อนเดินทาง
        if (mileageAtDestination < mileage) {
            event.preventDefault(); 
            document.getElementById("mileageError").style.display = "inline";  
        } else {
            document.getElementById("mileageError").style.display = "none";  
        }
    };
</script>

</body>
</html>
