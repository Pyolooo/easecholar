<?php
include '../include/connection.php';

if (isset($_POST['notification_id'])) {
    $notificationId = $_POST['notification_id'];

    // Update the 'is_read' column to 'unread' for the specified notification ID
    $updateQuery = "UPDATE tbl_reg_notifications SET is_read = 'unread' WHERE notification_id = $notificationId";
    mysqli_query($dbConn, $updateQuery) or die('update failed');
}
?>
