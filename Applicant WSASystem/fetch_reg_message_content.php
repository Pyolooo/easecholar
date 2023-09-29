<?php
// fetch_reg_message_content.php

include 'connection.php';

if (isset($_POST['message_id']) && isset($_POST['application_id']) && isset($_POST['registrar_id'])) {
    $messageId = $_POST['message_id'];
    $applicationId = $_POST['application_id'];
    $registrarId = $_POST['registrar_id'];

    // Function to update the message status as read
    function markMessageAsRead($dbConn, $messageId, $registrarId)
    {
        $updateQuery = "UPDATE tbl_reg_messages SET read_status = 'read' WHERE message_id = ? AND registrar_id = ?";
        $stmtUpdate = mysqli_prepare($dbConn, $updateQuery);
        mysqli_stmt_bind_param($stmtUpdate, "ii", $messageId, $registrarId);
        mysqli_stmt_execute($stmtUpdate);
    }

    // Check if the message is associated with the provided application ID and registrar ID
    $selectMessage = mysqli_prepare($dbConn, "SELECT registrar_message_content FROM tbl_reg_messages WHERE message_id = ? AND application_id = ? AND registrar_id = ?");
    mysqli_stmt_bind_param($selectMessage, "iii", $messageId, $applicationId, $registrarId);
    mysqli_stmt_execute($selectMessage);
    $result = mysqli_stmt_get_result($selectMessage);

    if ($fetchMessage = mysqli_fetch_assoc($result)) {
        // Return the message content
        echo $fetchMessage['registrar_message_content'];
    } else {
        // Handle the case when no message is found for the given message_id, application_id, and registrar_id
        echo "No message found.";
    }
}
?>

