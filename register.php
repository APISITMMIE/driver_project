<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include('config.php');

    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        echo "<script>alert('รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน!');</script>";
    } else {
        $sql = "SELECT * FROM dv_users WHERE username='$username'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            echo "ชื่อผู้ใช้มีอยู่แล้ว";
        } else {
            $sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
            if ($conn->query($sql) === TRUE) {
                echo "<script>
                        alert('ลงทะเบียนสำเร็จ!');
                        window.location.href = 'login.php';
                      </script>";
            } else {
                echo "เกิดข้อผิดพลาด: " . $conn->error;
            }
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียน</title>
    <link rel="stylesheet" href="layout/styleregister.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <img src="assets/bandologo.png" alt="Logo" class="logo">
            <h2>ลงทะเบียนผู้ใช้ใหม่</h2>
            <form method="POST" action="register.php">
                <div class="input-group">
                    <label for="username"><i class="fas fa-user"></i></label>
                    <input type="text" name="username" placeholder="กรุณากรอกชื่อผู้ใช้" required>
                </div>
                <div class="input-group">
                    <label for="password"><i class="fas fa-lock"></i></label>
                    <input type="password" name="password" id="password" placeholder="กรุณากรอกรหัสผ่าน" required>
                    <i class="fas fa-eye" id="toggle-password" onclick="togglePassword()"></i>
                </div>
                <div class="input-group">
                    <label for="confirm_password"><i class="fas fa-lock"></i></label>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="กรุณายืนยันรหัสผ่าน" required>
                    <i class="fas fa-eye" id="toggle-confirm-password" onclick="toggleConfirmPassword()"></i>
                </div>
                <button type="submit" class="btn-submit">ลงทะเบียน</button>
            </form>
            <div class="register-link">
                <p>มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
            </div>
        </div>
    </div>

    <script>
        // ฟังก์ชันเปิด/ปิดการแสดงรหัสผ่าน
        function togglePassword() {
            var passwordField = document.getElementById("password");
            var passwordIcon = document.getElementById("toggle-password");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                passwordIcon.classList.remove("fa-eye");
                passwordIcon.classList.add("fa-eye-slash");
            } else {
                passwordField.type = "password";
                passwordIcon.classList.remove("fa-eye-slash");
                passwordIcon.classList.add("fa-eye");
            }
        }
        // ฟังก์ชันตรวจสอบรหัสผ่าน
        function toggleConfirmPassword() {
            var confirmPasswordField = document.getElementById("confirm_password");
            var confirmPasswordIcon = document.getElementById("toggle-confirm-password");
            if (confirmPasswordField.type === "password") {
                confirmPasswordField.type = "text";
                confirmPasswordIcon.classList.remove("fa-eye");
                confirmPasswordIcon.classList.add("fa-eye-slash");
            } else {
                confirmPasswordField.type = "password";
                confirmPasswordIcon.classList.remove("fa-eye-slash");
                confirmPasswordIcon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html>
