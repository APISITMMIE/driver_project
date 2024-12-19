<?php
require_once 'config.php';

$admin_username = "admin";
$admin_password = "adminpassword";
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $admin_username, $hashed_password);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Admin user created successfully.<br>";
} else {
    echo "Error creating admin user.<br>";
}

$users = [
    "user1" => "userpassword1",
    "user2" => "userpassword2",
    "user3" => "userpassword3",
    "user4" => "userpassword4",
    "user5" => "userpassword5",
    "user6" => "userpassword6",
    "user7" => "userpassword7",
    "user8" => "userpassword8",
    "user9" => "userpassword9",
];

foreach ($users as $username => $password) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, 'user')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $hashed_password);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "User $username created successfully.<br>";
    } else {
        echo "Error creating user $username.<br>";
    }
}
?>
