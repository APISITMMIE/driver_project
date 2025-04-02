<<<<<<< HEAD
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
=======
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
>>>>>>> e1c9b9236d4ed67f1dee5d6544511c0824532c22
