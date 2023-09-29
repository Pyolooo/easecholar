<?php
// fetch_message_content.php

include 'connection.php';

if (isset($_POST['message_id']) && isset($_POST['application_id']) && isset($_POST['admin_id'])) {
    $messageId = $_POST['message_id'];
    $applicationId = $_POST['application_id'];
    $adminId = $_POST['admin_id'];

    // Function to update the message status as read
    function markMessageAsRead($dbConn, $messageId, $adminId)
    {
        $updateQuery = "UPDATE tbl_user_messages SET read_status = 'read' WHERE message_id = ? AND admin_id = ?";
        $stmtUpdate = mysqli_prepare($dbConn, $updateQuery);
        mysqli_stmt_bind_param($stmtUpdate, "ii", $messageId, $adminId);
        mysqli_stmt_execute($stmtUpdate);
    }

    // Check if the message is associated with the provided application ID and admin ID
    $selectMessage = mysqli_prepare($dbConn, "SELECT osa_message_content FROM tbl_user_messages WHERE message_id = ? AND application_id = ? AND admin_id = ?");
    mysqli_stmt_bind_param($selectMessage, "iii", $messageId, $applicationId, $adminId);
    mysqli_stmt_execute($selectMessage);
    $result = mysqli_stmt_get_result($selectMessage);

    if ($fetchMessage = mysqli_fetch_assoc($result)) {
        // Return the message content
        echo $fetchMessage['osa_message_content'];
    } else {
        // Handle the case when no message is found for the given message_id, application_id, and admin_id
        echo "No message found.";
    }
}
?>
