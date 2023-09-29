<?php
include 'connection.php';

// Fetch the notification count
$getNotificationCountQuery = mysqli_query($dbConn, "SELECT COUNT(*) as count FROM tbl_reg_notifications WHERE is_read = 'unread'") or die('query failed');
$notificationCountData = mysqli_fetch_assoc($getNotificationCountQuery);
$notificationCount = $notificationCountData['count'];

// Return the notification count as JSON data
header('Content-Type: application/json');
echo json_encode(array('count' => $notificationCount));
?>
