<?php
session_start();

if (isset($_SESSION['pin'])) {
    echo 'has_pin';
} else {
    echo 'no_pin';
}
?>
