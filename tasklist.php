<?php
ini_set('session.gc_maxlifetime', 1800); 
session_set_cookie_params(1800); 
session_start();
date_default_timezone_set('Asia/Bangkok');
include('config.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$rowsPerPage = 25;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $rowsPerPage;

$username = $conn->real_escape_string($_SESSION['username']);

$sql = "SELECT dv_tasks.* 
        FROM dv_tasks 
        JOIN dv_users ON dv_tasks.driver_name = dv_users.username 
        WHERE dv_users.username = '$username'
        ORDER BY 
            (dv_tasks.destination_location IS NULL OR dv_tasks.end_time IS NULL) DESC, 
            dv_tasks.start_date DESC,  
            dv_tasks.start_time DESC   
        LIMIT $offset, $rowsPerPage";

$result = $conn->query($sql);

$sqlCount = "SELECT COUNT(*) AS total 
             FROM dv_tasks 
             JOIN dv_users ON dv_tasks.driver_name = dv_users.username 
             WHERE dv_users.username = '$username'";
$countResult = $conn->query($sqlCount);
$row = $countResult->fetch_assoc();
$totalRows = $row['total'];
$totalPages = ceil($totalRows / $rowsPerPage);

?>



<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task List</title>
    <link rel="stylesheet" href="layout/styletasklist.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <div>
            <h1>Task List</h1>
        </div>
        <!-- New Button -->
        <div class="new-button-container">
            <button class="new-button" onclick="location.href='addnewlist.php'">New</button>
        </div>
        <div>
            <span><?php echo $_SESSION['username']; ?></span> 
            <a href="driver_OT.php">
                <button class="ot-button">OT</button> 
            </a>     
            <a href="logout.php">
                <button class="logout-button">Logout</button> 
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="table-container">
        <table style="user-select: none;">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Start Location</th>
                    <th>Start Time</th>
                    <th>Destination</th>
                    <th>Destination Time</th>
                    <th>Car Users</th>
                </tr>
            </thead>
            <tbody id="taskListBody">
            <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {

                        $status = 'Wait Approve'; 
                        if (!empty($row['destination_location']) && !empty($row['end_time'])) {
                            $status = 'Approved';
                        }

                        echo "
                        <tr class='task-row' data-task-id='" . $row["task_id"] . "' data-status='$status'>
                            <td>" . ($row["start_date"] ? date("d-m-y", strtotime($row["start_date"])) : '-') . "</td>
                            <td>" . $row["location"] . "</td>
                            <td>" . ($row["start_time"] ? date("H:i", strtotime($row["start_time"])) : '-') . "</td>
                            <td>" . ($row["destination_location"] ? $row["destination_location"] : '-') . "</td>
                            <td>" . ($row["end_time"] ? date("H:i", strtotime($row["end_time"])) : '-') . "</td>
                            <td>" . $row["carUser"] . "</td>
                        </tr>
                        ";
                    }
                } else {
                    echo "<tr><td colspan='9'>ไม่มีข้อมูล</td></tr>";
                }
            ?>
            </tbody>
        </table>
    

    <!-- Pagination -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>" class="pagination-button">Previous</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" class="pagination-button <?php echo ($i == $page) ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>" class="pagination-button">Next</a>
        <?php endif; ?>
    </div>
</div>

    <!-- Popup Modal -->
    <div id="taskDetailModal" class="task-detail-modal">
        <div class="modal-content">
            <!-- <h2>From &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;To</h2> -->
            <div id="taskDetails"></div>
            <div class="model-footer">
                <button class="pop-button" id="backBtn" onclick="window.location.href='tasklist.php'">Back</button>
                <button class="pop-button" id="approveBtn">Approve</button>
            </div>
        </div>   
    </div>

    <script>
        $(document).ready(function() {
            $(".task-row").click(function(e) {
                var taskId = $(this).data('task-id'); 
                var taskStatus = $(this).data('status');
                $("#taskDetailModal").data('task-id', taskId); 
                
                $.ajax({
                    url: 'get_task_details.php', 
                    type: 'GET',
                    data: { task_id: taskId },
                    success: function(response) {
                        $("#taskDetails").html(response); 
                        $("#taskDetailModal").show();

                        if (taskStatus === 'Approved') {
                            $("#approveBtn").hide();
                        } else {
                            $("#approveBtn").show(); 
                        }
                    },
                    error: function(xhr, status, error) {
                        alert("An error occurred while retrieving task details. Please try again.");
                    }
                });
            });

            $("#approveBtn").click(function() {
                var taskId = $("#taskDetailModal").data('task-id');
                if (taskId) {
                    $.ajax({
                        url: 'check_pin_session.php', 
                        type: 'GET',
                        success: function(response) {
                            if (response === 'has_pin') {
                                window.location.href = "destination.php?taskId=" + taskId;
                            } else {
                                window.location.href = "pin.php?taskId=" + taskId;
                            }
                        },
                        error: function(xhr, status, error) {
                            alert("An error occurred while checking the pin session. Please try again.");
                        }
                    });
                } else {
                    alert("ไม่พบ Task ID");
                }
            });

            $(window).click(function(event) {
                if (event.target == document.getElementById("taskDetailModal")) {
                    $("#taskDetailModal").hide(); 
                }
            });
        });
    </script>
</body>
</html>
