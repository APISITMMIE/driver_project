<<<<<<< HEAD
<?php
session_start(); 
include('config.php');
if (isset($_SESSION['username'])) {
    header("Location: tasklist.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="mobile-web-app-capable" content="yes">
    <title>Login</title>
    <link rel="stylesheet" href="layout/stylelogin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <img src="assets/bandologo.png" alt="Logo" class="logo">
            <h2 id="login-title">เข้าสู่ระบบ</h2>
            
            <form action="login_action.php" method="POST">
                <div class="input-group">
                    <label for="username" id="username-label"><i class="fas fa-user"></i></label>
                    <input type="text" id="username" name="username" placeholder="กรุณากรอกชื่อผู้ใช้" required>
                </div>
                <div class="input-group">
                    <label for="password" id="password-label"><i class="fas fa-lock"></i></label>
                    <input type="password" id="password" name="password" placeholder="กรุณากรอกรหัสผ่าน" required>
                </div>

                <?php
                if (isset($_GET['error'])) {
                    $error_message = $_GET['error'];
                    echo "<div class='error-message'>$error_message</div>";
                }
                ?>

                <button type="submit" class="btn-submit" id="login-button">เข้าสู่ระบบ</button>
            </form>
        
            <button class="language-toggle" id="language-toggle">English</button>
        </div>
    </div>
    <script src="switchlang.js"></script>
</body>
=======
<?php
session_start(); 
include('config.php');
if (isset($_SESSION['username'])) {
    header("Location: tasklist.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="mobile-web-app-capable" content="yes">
    <title>Login</title>
    <link rel="stylesheet" href="layout/stylelogin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <img src="assets/bandologo.png" alt="Logo" class="logo">
            <h2 id="login-title">เข้าสู่ระบบ</h2>
            
            <form action="login_action.php" method="POST">
                <div class="input-group">
                    <label for="username" id="username-label"><i class="fas fa-user"></i></label>
                    <input type="text" id="username" name="username" placeholder="กรุณากรอกชื่อผู้ใช้" required>
                </div>
                <div class="input-group">
                    <label for="password" id="password-label"><i class="fas fa-lock"></i></label>
                    <input type="password" id="password" name="password" placeholder="กรุณากรอกรหัสผ่าน" required>
                </div>

                <?php
                if (isset($_GET['error'])) {
                    $error_message = $_GET['error'];
                    echo "<div class='error-message'>$error_message</div>";
                }
                ?>

                <button type="submit" class="btn-submit" id="login-button">เข้าสู่ระบบ</button>
            </form>
        
            <button class="language-toggle" id="language-toggle">English</button>
        </div>
    </div>
    <script src="switchlang.js"></script>
</body>
>>>>>>> e1c9b9236d4ed67f1dee5d6544511c0824532c22
</html>