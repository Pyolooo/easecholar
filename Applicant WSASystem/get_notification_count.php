<?php
include '../include/connection.php';
session_start();
$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    // User is not logged in, return 0 notification count
    echo 0;
    exit();
}

// Fetch the notification count for the logged-in user using a prepared statement
$sql = "SELECT COUNT(*) AS notification_count FROM tbl_user_messages WHERE application_id = ? AND read_status = 0";
$stmt = mysqli_prepare($dbConn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $notificationCount = $row['notification_count'];
    // Return the notification count
    echo $notificationCount;
} else {
    // Error in fetching notification count, return 0
    echo 0;
}
?>
