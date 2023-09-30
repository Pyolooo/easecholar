<?php
include '../include/connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .container{
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          position: absolute;
    bottom: 0;
    top: 0;
        }
        img{
          width: 50px;
        }
        .message {
            font-size: 18px;
            margin: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
            line-height: 50px;
            text-align: justify;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
<?php
if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = mysqli_real_escape_string($dbConn, $_GET['email']);
    $token = mysqli_real_escape_string($dbConn, $_GET['token']);

    // Check if the email and token exist in your database
    $query = mysqli_prepare($dbConn, "SELECT * FROM `tbl_user` WHERE email = ? AND verification_token = ?");
    mysqli_stmt_bind_param($query, "ss", $email, $token);
    mysqli_stmt_execute($query);
    $result = mysqli_stmt_get_result($query);

    if (mysqli_num_rows($result) > 0) {
        // Update the user's account status to "verified"
        $update = mysqli_prepare($dbConn, "UPDATE `tbl_user` SET acc_status = 'verified' WHERE email = ?");
        mysqli_stmt_bind_param($update, "s", $email);

        if (mysqli_stmt_execute($update)) {
            // Account verified successfully
            echo '<div class="container">';
            echo '<img src="../img/isulogo.png">';
            echo '<div class="message success">Your account has been verified. You can now <a href="applicant_login.php">login</a> to the system.</div>';
            echo '</div>';
        } else {
            // Account verification failed
            echo '<div class="message error">Account verification failed. Please try again later.</div>';
        }
    } else {
        // Invalid verification link
        echo '<div class="message error">Invalid verification link. Please make sure you clicked the correct link or contact support.</div>';
    }
} else {
    // No email and token provided
    echo '<div class="message error">Invalid verification link. Please make sure you clicked the correct link or contact support.</div>';
}

// Close the database connection if needed
mysqli_close($dbConn);
?>
</body>
</html>
