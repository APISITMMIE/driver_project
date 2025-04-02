<<<<<<< HEAD
<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include('config.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['task_id'] = $_POST['task_id'];

    $driverName = $_POST['driverName'];
    $carName = $_POST['carName'];
    $mileage = $_POST['mileage'];
    $location = $_POST['location'];
    $accessories = isset($_POST['accessory']) ? $_POST['accessory'] : "";
    $tripTypes = isset($_POST['tripType']) ? $_POST['tripType'] : "";

    $start_date = date('Y-m-d'); 
    $start_time = date('H:i:s'); 

    $targetDir = "uploads/";
    $imageName = basename($_FILES["driverImage"]["name"]);
    $imageExtension = pathinfo($imageName, PATHINFO_EXTENSION);
    $timestamp = date('Y-m-d_H-i-s');
    $driverImage = $targetDir . $timestamp . "." . $imageExtension;

    $latitude = $_POST['latitude']; // รับค่า latitude
    $longitude = $_POST['longitude']; // รับค่า longitude

    if (move_uploaded_file($_FILES["driverImage"]["tmp_name"], $driverImage)) {
        $sql = "INSERT INTO dv_tasks (driver_name, carName, mileage, location, start_date, start_time, driver_image, accessories, trip_types, latitude, longitude)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssdd", $driverName, $carName, $mileage, $location, $start_date, $start_time, $driverImage, $accessories, $tripTypes, $latitude, $longitude);

        if ($stmt->execute()) {
            $taskId = $stmt->insert_id;
            header("Location: tasklist.php?taskId=$taskId");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error uploading the image.";
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

$conn->close();
?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ฟอร์มกรอกข้อมูลคนขับรถ</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap');
        *{
            margin:0;
            padding:0;
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
            margin: auto;
            margin-top: 20px;
            background-color: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        label {
            font-weight: bold;
            margin-bottom: 8px;
            display: block;
        }
        input[type="text"], input[type="file"], input[type="number"], input[type="checkbox"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            font-size: 15px;
        }

        button {
            background-color: transparent;
            color: #4CAF50;
            padding: 12px 20px;
            border: 2px solid #4CAF50;
            border-radius: 5px;
            cursor: pointer;
            width: 50%;
            margin-top: 20px;
        }

        button.home-button {
            background-color: transparent;
            color: #333;
            border: 2px solid #333;
            margin-top: 20px;
        }

        .form-buttons {
            display: flex;
            flex-direction: column; 
            justify-content: center; 
            align-items: center; 
            width: 100%;
            height: 100%; 
        }

        .form-container button {
            width: 40%; 
            margin-top: 10px; 
        }

        button:hover {
            background-color: #45a049;
            color: white;
        }

        .home-button:hover {
            background-color: #333;
        }

        #driverName {
            background-color: #b6bbbb;
        }

        #carName {
            background-color: #b6bbbb;
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

        .radio-group {
            margin-bottom: 10px;
            display: flex; 
            flex-wrap: wrap; 
            gap: 15px; 
            align-items: baseline; 
        }

        .radio-group label {
            font-weight: normal;
            display: flex; 
            align-items: baseline;
            margin-left: 0;
        }

        .radio-group input[type="radio"] {
            margin-right: 10px; 
            width: auto;
        }

        .radio-group div {
            
            display: flex;
            gap: 30px; 
            
        } 

    </style>
</head>
<body>

    <h1>ฟอร์มข้อมูลคนขับรถ</h1>

    <div class="form-container">
        <form  method="POST" action="addnewlist.php"  enctype="multipart/form-data">
            <label for="driverName">ชื่อคนขับรถ</label>
            <input type="text" id="driverName" name="driverName" value="<?php echo $_SESSION['username']; ?>" readonly>

            <label for="carName">ชื่อรถ</label>
            <input type="text" id="carName" name="carName" value="<?php echo htmlspecialchars($carName); ?>" readonly>

            <label for="mileage">เลขไมล์รถก่อนเดินทาง</label>
            <input type="number" id="mileage" name="mileage" max="999999" placeholder="กรุณากรอกเลขไมล์ก่อนเดินทาง" required>

            <label for="driverImage">รูปถ่ายเลขไมล์รถ (ก่อน)</label>
            <input type="file" id="driverImage" name="driverImage" accept="image/*" capture="camera" required>

            <div class="image-preview" id="imagePreview"></div>

            <label for="location">สถานที่ต้นทาง</label>
            <input type="text" id="location" name="location" placeholder="กรุณารอกสถานที่ต้นทาง" required>

            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">

            <div class="radio-group">
                <label>Private/Official:</label>
                <div>
                    <label><input type="radio" name="accessory" value="Private"> Private</label>
                    <label><input type="radio" name="accessory" value="Official"> Official</label>
                </div>
            </div>

            <div class="radio-group">
                <label>จุดประสงค์ในการเดินทาง:</label>
                <div>
                    <label><input type="radio" name="tripType" value="Go to work"> Go to work</label>
                    <label><input type="radio" name="tripType" value="Comeback Apart"> Comeback Apart (eat dinner out)</label>
                    <label><input type="radio" name="tripType" value="Other"> Other</label>
                    <label><input type="radio" name="tripType" value="Other Employee"> อื่น ๆ (สำหรับพนักงาน)</label>
                </div>
            </div>
            <div class="form-buttons">
                <button type="submit">บันทึกข้อมูล</button>
                <button type="button" class="home-button" onclick="window.location.href='tasklist.php'">ย้อนกลับ</button>
            </div>
        </form>
    </div>

    <script>
        // เปิดรูปภาพ
        document.getElementById('driverImage').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imgElement = document.createElement('img');
                    imgElement.src = e.target.result;
                    const previewContainer = document.getElementById('imagePreview');
                    previewContainer.innerHTML = ''; 
                    previewContainer.appendChild(imgElement);
                }
                reader.readAsDataURL(file); 
            }
        });

        // เก็บค่า Latitude และ Longitude
        document.querySelector("form").addEventListener("submit", function (e) {
            e.preventDefault();
            getLocation();
        });

        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition, showError);
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }

        function showPosition(position) {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;

            document.getElementById("latitude").value = latitude;
            document.getElementById("longitude").value = longitude;

            document.querySelector("form").submit();
        }

        function showError(error) {
            let errorMessage = "";
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage = "User denied the request for Geolocation.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage = "Location information is unavailable.";
                    break;
                case error.TIMEOUT:
                    errorMessage = "The request to get user location timed out.";
                    break;
                case error.UNKNOWN_ERROR:
                    errorMessage = "An unknown error occurred.";
                    break;
            }
            alert(errorMessage);
        }

    </script>
</body>
</html>
=======
<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include('config.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['task_id'] = $_POST['task_id'];

    $driverName = $_POST['driverName'];
    $carName = $_POST['carName'];
    $mileage = $_POST['mileage'];
    $location = $_POST['location'];
    $accessories = isset($_POST['accessory']) ? $_POST['accessory'] : "";
    $tripTypes = isset($_POST['tripType']) ? $_POST['tripType'] : "";

    $start_date = date('Y-m-d'); 
    $start_time = date('H:i:s'); 

    $targetDir = "uploads/";
    $imageName = basename($_FILES["driverImage"]["name"]);
    $imageExtension = pathinfo($imageName, PATHINFO_EXTENSION);
    $timestamp = date('Y-m-d_H-i-s');
    $driverImage = $targetDir . $timestamp . "." . $imageExtension;

    $latitude = $_POST['latitude']; // รับค่า latitude
    $longitude = $_POST['longitude']; // รับค่า longitude

    if (move_uploaded_file($_FILES["driverImage"]["tmp_name"], $driverImage)) {
        $sql = "INSERT INTO dv_tasks (driver_name, carName, mileage, location, start_date, start_time, driver_image, accessories, trip_types, latitude, longitude)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssdd", $driverName, $carName, $mileage, $location, $start_date, $start_time, $driverImage, $accessories, $tripTypes, $latitude, $longitude);

        if ($stmt->execute()) {
            $taskId = $stmt->insert_id;
            header("Location: tasklist.php?taskId=$taskId");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error uploading the image.";
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

$conn->close();
?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ฟอร์มกรอกข้อมูลคนขับรถ</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap');
        *{
            margin:0;
            padding:0;
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
            margin: auto;
            margin-top: 20px;
            background-color: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        label {
            font-weight: bold;
            margin-bottom: 8px;
            display: block;
        }
        input[type="text"], input[type="file"], input[type="number"], input[type="checkbox"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            font-size: 15px;
        }

        button {
            background-color: transparent;
            color: #4CAF50;
            padding: 12px 20px;
            border: 2px solid #4CAF50;
            border-radius: 5px;
            cursor: pointer;
            width: 50%;
            margin-top: 20px;
        }

        button.home-button {
            background-color: transparent;
            color: #333;
            border: 2px solid #333;
            margin-top: 20px;
        }

        .form-buttons {
            display: flex;
            flex-direction: column; 
            justify-content: center; 
            align-items: center; 
            width: 100%;
            height: 100%; 
        }

        .form-container button {
            width: 40%; 
            margin-top: 10px; 
        }

        button:hover {
            background-color: #45a049;
            color: white;
        }

        .home-button:hover {
            background-color: #333;
        }

        #driverName {
            background-color: #b6bbbb;
        }

        #carName {
            background-color: #b6bbbb;
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

        .radio-group {
            margin-bottom: 10px;
            display: flex; 
            flex-wrap: wrap; 
            gap: 15px; 
            align-items: baseline; 
        }

        .radio-group label {
            font-weight: normal;
            display: flex; 
            align-items: baseline;
            margin-left: 0;
        }

        .radio-group input[type="radio"] {
            margin-right: 10px; 
            width: auto;
        }

        .radio-group div {
            
            display: flex;
            gap: 30px; 
            
        } 

    </style>
</head>
<body>

    <h1>ฟอร์มข้อมูลคนขับรถ</h1>

    <div class="form-container">
        <form  method="POST" action="addnewlist.php"  enctype="multipart/form-data">
            <label for="driverName">ชื่อคนขับรถ</label>
            <input type="text" id="driverName" name="driverName" value="<?php echo $_SESSION['username']; ?>" readonly>

            <label for="carName">ชื่อรถ</label>
            <input type="text" id="carName" name="carName" value="<?php echo htmlspecialchars($carName); ?>" readonly>

            <label for="mileage">เลขไมล์รถก่อนเดินทาง</label>
            <input type="number" id="mileage" name="mileage" max="999999" placeholder="กรุณากรอกเลขไมล์ก่อนเดินทาง" required>

            <label for="driverImage">รูปถ่ายเลขไมล์รถ (ก่อน)</label>
            <input type="file" id="driverImage" name="driverImage" accept="image/*" capture="camera" required>

            <div class="image-preview" id="imagePreview"></div>

            <label for="location">สถานที่ต้นทาง</label>
            <input type="text" id="location" name="location" placeholder="กรุณารอกสถานที่ต้นทาง" required>

            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">

            <div class="radio-group">
                <label>Private/Official:</label>
                <div>
                    <label><input type="radio" name="accessory" value="Private"> Private</label>
                    <label><input type="radio" name="accessory" value="Official"> Official</label>
                </div>
            </div>

            <div class="radio-group">
                <label>จุดประสงค์ในการเดินทาง:</label>
                <div>
                    <label><input type="radio" name="tripType" value="Go to work"> Go to work</label>
                    <label><input type="radio" name="tripType" value="Comeback Apart"> Comeback Apart (eat dinner out)</label>
                    <label><input type="radio" name="tripType" value="Other"> Other</label>
                    <label><input type="radio" name="tripType" value="Other Employee"> อื่น ๆ (สำหรับพนักงาน)</label>
                </div>
            </div>
            <div class="form-buttons">
                <button type="submit">บันทึกข้อมูล</button>
                <button type="button" class="home-button" onclick="window.location.href='tasklist.php'">ย้อนกลับ</button>
            </div>
        </form>
    </div>

    <script>
        // เปิดรูปภาพ
        document.getElementById('driverImage').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imgElement = document.createElement('img');
                    imgElement.src = e.target.result;
                    const previewContainer = document.getElementById('imagePreview');
                    previewContainer.innerHTML = ''; 
                    previewContainer.appendChild(imgElement);
                }
                reader.readAsDataURL(file); 
            }
        });

        // เก็บค่า Latitude และ Longitude
        document.querySelector("form").addEventListener("submit", function (e) {
            e.preventDefault();
            getLocation();
        });

        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition, showError);
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }

        function showPosition(position) {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;

            document.getElementById("latitude").value = latitude;
            document.getElementById("longitude").value = longitude;

            document.querySelector("form").submit();
        }

        function showError(error) {
            let errorMessage = "";
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage = "User denied the request for Geolocation.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage = "Location information is unavailable.";
                    break;
                case error.TIMEOUT:
                    errorMessage = "The request to get user location timed out.";
                    break;
                case error.UNKNOWN_ERROR:
                    errorMessage = "An unknown error occurred.";
                    break;
            }
            alert(errorMessage);
        }

    </script>
</body>
</html>
>>>>>>> e1c9b9236d4ed67f1dee5d6544511c0824532c22
