<?php
// delete_notification.php

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["notification_id"])) {
    include 'connection.php';
    $notificationId = $_POST["notification_id"];

    // Perform the deletion operation using the notification_id
    $query = "DELETE FROM tbl_notifications WHERE notification_id = ?";
    $stmt = $dbConn->prepare($query);
    $stmt->bind_param("i", $notificationId);

    if ($stmt->execute()) {
        // Deletion successful
        echo "success";
    } else {
        // Deletion failed
        echo "error";
    }
}
