<?php
include '../include/connection.php';

if (isset($_POST['read_message'])) {
    $notificationId = $_POST['read_message'];
    if ($notificationId === 'all') {
        // Update all notifications as read
        $sql = "UPDATE tbl_notifications SET is_read = 'read'";
        $stmt = $dbConn->prepare($sql);
        $stmt->execute();
    } else {
        // Update a specific notification as read
        $sql = "UPDATE tbl_notifications SET is_read = 'read' WHERE notification_id = ?";
        $stmt = $dbConn->prepare($sql);
        $stmt->bind_param("i", $notificationId);
        $stmt->execute();
    }
}
?>
