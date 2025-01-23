<?php
session_start();
include('config.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['taskId'])) {
    $taskId = $_GET['taskId'];
}

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PIN</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .text-head {
            text-align: center;
            margin-top: 100px;
        }

        .container {
            width: 100%;
        }

        .card {
            border: 1px solid black;
            border-radius: 5px;
            width: 40vw;
            margin-right: auto;
            margin-left: auto;
            display: flex;
            flex-direction: column;
        }

        .card-header {
            display: flex;
            justify-content: center;
            margin: 10px;
            text-align: center;
        }

        .row {
            display: flex;
            flex-direction: row;
            justify-content: center;
        }

        .pinpad-btn {
            width: 25%;
            height: 75px;
            margin: 5px;
            padding: 5px;
            border: 1px solid black;
            border-radius: 20%;
            font-size: 2em;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            background-color: white;
        }

        .pinpad-btn:hover {
            background-color: lightgray;
        }

        #pinpad-input {
            border-radius: 10px;
            height: 3em;
            font-size: 2em;
            text-align: center;
            width: 80%;
        }

        .home-button {
            width: 39.5vw;
            height: 50px;
            background-color: transparent;
            color: #4CAF50;
            border: 2px solid #4CAF50;
            font-size: 1.5em;
            cursor: pointer;
            text-align: center;
            margin-top: 20px;
            margin-left: 29.5vw;
            border-radius: 8px;
        }

        .home-button:hover {
            background-color: #45a049;
            color: white;
        }

        /* responsive tablet & mobile*/
        @media (max-width: 768px) {
            .card  {
                width: 60vw;
            }
            .home-button {
                width: 60vw;
                margin-left: 19vw;
            }
        }

    </style>
</head>

<body>
    <h2 class="text-head">Please Enter PIN</h2>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <input type="password" id="pinpad-input" readonly />
            </div>
            <div>
                <div class="row">
                    <button type="button" class="pinpad-btn" value="1">1</button>
                    <button type="button" class="pinpad-btn" value="2">2</button>
                    <button type="button" class="pinpad-btn" value="3">3</button>
                </div>
                <div class="row">
                    <button type="button" class="pinpad-btn" value="4">4</button>
                    <button type="button" class="pinpad-btn" value="5">5</button>
                    <button type="button" class="pinpad-btn" value="6">6</button>
                </div>
                <div class="row">
                    <button type="button" class="pinpad-btn" value="7">7</button>
                    <button type="button" class="pinpad-btn" value="8">8</button>
                    <button type="button" class="pinpad-btn" value="9">9</button>
                </div>
                <div class="row">
                    <button type="button" class="pinpad-btn" value="del" id="delete-btn">Del</button>
                    <button type="button" class="pinpad-btn" value="0">0</button>
                    <button type="button" class="pinpad-btn" value="ok" id="submit-btn">Ok</button>
                </div>
            </div>
        </div>
        <input type="hidden" id="latitude" name="latitude">
        <input type="hidden" id="longitude" name="longitude">

        <!-- ปุ่ม Home -->
        <button class="home-button" onclick="window.location.href='tasklist.php'">Home</button>

    </div>

    <script>
        let btns = document.getElementsByClassName("pinpad-btn");
        let pinInput = document.getElementById("pinpad-input");

        for (let i = 0; i < btns.length; i++) {
            let btn = btns.item(i);
            if (btn.id && (btn.id === "submit-btn" || btn.id === "delete-btn"))
                continue;

            btn.addEventListener("click", (e) => { pinInput.value += e.target.value; });
        }

        let submitBtn = document.getElementById("submit-btn");
        let delBtn = document.getElementById("delete-btn");

        submitBtn.addEventListener("click", () => {
            if (!pinInput || !pinInput.value || pinInput.value === "") {
                Swal.fire({
                    icon: 'warning',
                    title: 'Please enter a PIN',
                    text: 'You need to enter a PIN to continue!',
                });
            } else {
                let pinEntered = pinInput.value;
                $.ajax({
                    url: 'check_pin.php',
                    type: 'POST',
                    data: { pin: pinEntered, taskId: <?php echo $taskId; ?> },
                    success: function(response) {
                        let [type, value] = response.trim().split('|');
                        if (type === "boss" || type === "pin") {
                            Swal.fire({
                                title: `You are ${value}`,
                                text: 'Is this correct?',
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: 'Yes',
                                cancelButtonText: 'No',
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    saveLocationToDatabase(pinEntered);
                                } else {
                                    pinInput.value = ""; 
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred. Please try again.',
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while checking the PIN. Please try again.',
                        });
                    }
                });
                getLocation();
            }
        });

        function saveLocationToDatabase(pinEntered) {
            let latitude = document.getElementById("latitude").value || "0";
            let longitude = document.getElementById("longitude").value || "0";

            $.ajax({
                url: 'save_location.php',
                type: 'POST',
                data: {
                    taskId: <?php echo $taskId; ?>,
                    latitude: latitude,
                    longitude: longitude
                },
                success: function(response) {
                    if (response.trim() === "success") {
                        proceedToNextPage(pinEntered);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to save location data.',
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while saving the location data. Please try again.',
                    });
                }
            });
        }

        function proceedToNextPage(pinEntered) {
            let endTime = new Date().toLocaleTimeString('en-GB', { hour12: false });
            window.location.href = `destination.php?taskId=<?php echo $taskId; ?>&pin=${pinEntered}&end_time=${endTime}`;
        }


        delBtn.addEventListener("click", () => {
            if (pinInput.value)
                pinInput.value = pinInput.value.substr(0, pinInput.value.length - 1);
        });

        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition, showError);
            } else {
                alert("Geolocation is not supported this browser.");
            }
        }

        function showPosition(position) {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;

            document.getElementById("latitude").value = latitude;
            document.getElementById("longitude").value = longitude;

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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>