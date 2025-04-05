<?php
$servername = "192.168.0.13";
$username = "root";
$password = "Bando*3hfdY";
$dbname = "bmt";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
