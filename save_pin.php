<?php
session_start();

if (isset($_POST['pin'])) {
    $pin = $_POST['pin'];

    $_SESSION['pin'] = $pin;

    echo "success";
} else {
    echo "error";
}
?>
