<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

include('config.php');

if (isset($_GET['id'])) {
    $userId = $_GET['id'];
    $sql = $conn->prepare("SELECT * FROM dv_users WHERE user_id = ? AND role = 'user'");
    $sql->bind_param("i", $userId);  // Assuming user_id is an integer
    $sql->execute();
    $result = $sql->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        header("Location: adminUser.php");
        exit;
    }
} else {
    header("Location: adminUser.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $targetDir = "img/";
    
    if (isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] === UPLOAD_ERR_OK) {
        $imageName = basename($_FILES["profile_image"]["name"]);
        $imageExtension = pathinfo($imageName, PATHINFO_EXTENSION);
        $timestamp = date('Y-m-d_H-i-s');
        $profileImage = $targetDir . $timestamp . "_" . $imageName;
        
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $profileImage)) {
        } else {
            echo "Error uploading the image.";
            exit;
        }
    } else {
        $profileImage = $user['profile_image'];
    }

    $updateSql = $conn->prepare("UPDATE dv_users SET username = ?, password = ?, profile_image = ? WHERE user_id = ?");
    $updateSql->bind_param("sssi", $username, $password, $profileImage, $userId);
    
    if ($updateSql->execute()) {
        echo "<script>
                alert('Update Success');
                window.location.href = 'adminUser.php';
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
    <link rel="stylesheet" href="layout/adminUser.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        form {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            width: 400px;
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
            margin-top: 20px;
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
        .headna {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
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
        </ul>
    </div>

    <!-- Main Section -->
    <div class="table-container">
        <div class="headna">
            <h2>แก้ไขข้อมูลผู้ใช้</h2>
            <img src="<?php echo htmlspecialchars($user['profile_image'] ?: '/Driver/img/default.png'); ?>" 
     		alt="Profile Image" 
     		style="width: 120px; height: 120px; margin-bottom: 20px; border-radius: 50%; object-fit: cover;">


        </div>

        <form method="POST" enctype="multipart/form-data">
            <label for="username">ชื่อผู้ใช้</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

            <label for="password">รหัสผ่าน</label>
            <div class="password-container">
                <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($user['password']); ?>" required>
                <span class="eye-icon" id="eye-icon" onclick="togglePassword()">
                    <i class="fas fa-eye"></i> 
                </span>
            </div>

            <label for="profile_image">รูปภาพโปรไฟล์</label>
            <input type="file" id="profile_image" name="profile_image" accept="image/*">
           
            <div class="image-preview" id="imagePreview"></div>

            <button type="submit">Update</button>
        </form>
    </div>

    <script>
        document.getElementById('profile_image').addEventListener('change', function(event) {
            const file = event.target.files[0]; 
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imgElement = document.createElement('img'); 
                    imgElement.src = e.target.result; 
                    imgElement.style.maxWidth = '200px'; 
                    imgElement.style.borderRadius = '10px'; 

                    const previewContainer = document.getElementById('imagePreview');
                    previewContainer.innerHTML = ''; 
                    previewContainer.appendChild(imgElement); 
                };
                reader.readAsDataURL(file); 
            }
        });

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = '<i class="fas fa-eye-slash"></i>'; 
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = '<i class="fas fa-eye"></i>'; 
            }
        }
    </script>
</body>
</html>
