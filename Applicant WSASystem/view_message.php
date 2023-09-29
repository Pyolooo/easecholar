<?php
// Retrieve the user_id from the session
session_start();
$user_id = $_SESSION['user_id'];

// Check if the user_id is not set in the session or if the user is not logged in, redirect to login page
if (!isset($user_id)) {
    header('location:user_login.php');
    exit();
}

// Check if the message_id is provided in the URL
if (isset($_GET['message_id'])) {
    $message_id = $_GET['message_id'];

    // Retrieve message content from 'tbl_user_messages' using prepared statement
    $messageQuery = "SELECT `message_content` FROM `tbl_user_messages` WHERE `message_id` = ? AND `user_id` = ?";
    $stmt = mysqli_prepare($dbConn, $messageQuery);

    if (!$stmt) {
        echo "Error preparing query: " . mysqli_error($dbConn);
        exit();
    }

    mysqli_stmt_bind_param($stmt, "ii", $message_id, $user_id);
    mysqli_stmt_execute($stmt);
    $messageResult = mysqli_stmt_get_result($stmt);

    if (!$messageResult) {
        echo "Error executing query: " . mysqli_error($dbConn);
        exit();
    }

    // Check if the message exists for the specific user
    if (mysqli_num_rows($messageResult) == 0) {
        echo "Message not found for this user.";
        exit();
    }

    $messageData = mysqli_fetch_assoc($messageResult);
} else {
    echo "Message ID not provided.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Message</title>
</head>
<body>
    <!-- Display the whole message -->
    <div>
        <?php echo $messageData['message_content']; ?>
    </div>
</body>
</html>
