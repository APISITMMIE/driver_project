<<<<<<< HEAD
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
=======
<?php
$servername = localhost;
$username = "root";
$password = ;
$dbname =;

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
>>>>>>> e1c9b9236d4ed67f1dee5d6544511c0824532c22
