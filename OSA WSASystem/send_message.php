<?php
session_name("OsaSession");
session_start();
include '../include/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_id = $_SESSION['admin_id'];
    $application_id = $_POST['application_id'];
    $user_id = $_POST['user_id'];
    $osa_message_content = $_POST['osa_message_content'];

    $insertQuery = "INSERT INTO `tbl_user_messages` (`application_id`, `admin_id`, `user_id`, `osa_message_content`, `sent_at`, `read_status`)
                VALUES (?, ?, ?, ?, NOW(), 'unread')";


    $stmt = mysqli_prepare($dbConn, $insertQuery);

    if ($stmt === false) {
        echo "Error preparing statement: " . mysqli_error($dbConn);
        exit();
    }

    mysqli_stmt_bind_param($stmt, "iiis", $application_id, $admin_id, $user_id, $osa_message_content);
    $result = mysqli_stmt_execute($stmt);

    if ($result === false) {
        echo "Error executing query: " . mysqli_error($dbConn);
        exit();
    }

    // Set a flash message to indicate that the message was sent successfully
    $_SESSION['success_message'] = "Message sent successfully";

    // Redirect back to the View Application page after sending the message
    header('Location: view_application.php?id=' . $application_id);
    exit();
}
?>
