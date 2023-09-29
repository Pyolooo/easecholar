<?php
// Start the session and include the connection.php file
session_start();
include 'connection.php';

// Retrieve the application_id and user_id from the session
$user_id = $_SESSION['user_id'];

// Check if the user_id is not set in the session or if the user is not logged in, redirect to the login page
if (!isset($user_id)) {
    header('location: user_login.php');
    exit();
}

// Check if the application_id is provided as a URL parameter
if (isset($_GET['application_id'])) {
    $application_id = $_GET['application_id'];

    // Retrieve message content from 'tbl_user_messages' using prepared statement
    $messageQuery = "SELECT `message_content` FROM `tbl_user_messages` WHERE `application_id` = ?";
    $stmt = mysqli_prepare($dbConn, $messageQuery);

    if (!$stmt) {
        echo "Error preparing query: " . mysqli_error($dbConn);
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $application_id);
    mysqli_stmt_execute($stmt);
    $messageResult = mysqli_stmt_get_result($stmt);

    if (!$messageResult) {
        echo "Error executing query: " . mysqli_error($dbConn);
        exit();
    }

    // Check if the message exists for the specific application
    if (mysqli_num_rows($messageResult) == 0) {
        echo "Message not found for this application.";
        exit();
    }

    $messageData = mysqli_fetch_assoc($messageResult);
} else {
    // Redirect back to the View Application page if the application_id is not provided
    header('Location: view_application.php');
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Inbox</title>
    <link rel="stylesheet" href="css/message.css">
</head>

<body>

    <div class="container">
    
        <div class="info">
            <h3 style="text-align: center;">Message</h3>
            <hr><br>

            <label><?php echo $messageData['message_content']; ?> </label>


        </div>
    </div>

</body>

</html>