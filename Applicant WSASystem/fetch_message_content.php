<?php
include '../include/connection.php';

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

    $selectMessage = mysqli_prepare($dbConn, "SELECT osa_message_content FROM tbl_user_messages WHERE message_id = ? AND application_id = ? AND admin_id = ?");
    mysqli_stmt_bind_param($selectMessage, "iii", $messageId, $applicationId, $adminId);
    mysqli_stmt_execute($selectMessage);
    $result = mysqli_stmt_get_result($selectMessage);

    if ($fetchMessage = mysqli_fetch_assoc($result)) { 
        echo $fetchMessage['osa_message_content'];
    } else {
    
        echo "No message found.";
    }
}
?>
