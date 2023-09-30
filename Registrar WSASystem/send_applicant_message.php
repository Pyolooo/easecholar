<?php
session_name("RegistrarSession");
session_start();
include '../include/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $registrar_id = $_SESSION['registrar_id'];
    $application_id = $_POST['application_id'];
    $reg_message_content = $_POST['reg_message_content'];

    // Insert the message into 'tbl_user_messages' using prepared statement
    $insertQuery = "INSERT INTO `tbl_reg_messages` (`application_id`, `registrar_id`, `registrar_message_content`, `sent_at`, `read_status`)
                    VALUES (?, ?, ?, NOW(), 'unread')";

    $stmt = mysqli_prepare($dbConn, $insertQuery);

    if ($stmt === false) {
        echo "Error preparing statement: " . mysqli_error($dbConn);
        exit();
    }

    mysqli_stmt_bind_param($stmt, "iis", $application_id, $registrar_id, $reg_message_content);
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
