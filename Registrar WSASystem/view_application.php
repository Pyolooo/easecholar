<?php
include 'connection.php';
session_name("RegistrarSession");
session_start();

// Check if 'registrar_id' is not set in the session, redirect to login page
if (!isset($_SESSION['registrar_id'])) {
    header('location: registrar_login.php');
    exit();
}

$registrar_id = $_SESSION['registrar_id'];

if (isset($_GET['logout'])) {
    unset($registrar_id);
    session_destroy();
    header('location: registrar_login.php');
    exit();
}

if (isset($_GET['id'])) {
    $application_id = $_GET['id'];

    // Retrieve application details from 'tbl_userapp' using prepared statement
    $query = "SELECT * FROM `tbl_userapp` WHERE `application_id` = ?";
    $stmt = mysqli_prepare($dbConn, $query);

    if (!$stmt) {
        echo "Error preparing query: " . mysqli_error($dbConn);
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $application_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        echo "Error executing query: " . mysqli_error($dbConn);
        exit();
    }

    if (mysqli_num_rows($result) == 0) {
        echo "Application not found.";
        exit();
    }

    $applicationData = mysqli_fetch_assoc($result);

    // Retrieve status from 'tbl_userapp' using prepared statement
    $statusQuery = "SELECT `grade_status` FROM `tbl_userapp` WHERE `application_id` = ?";
    $statusStmt = mysqli_prepare($dbConn, $statusQuery);

    if (!$statusStmt) {
        echo "Error preparing query: " . mysqli_error($dbConn);
        exit();
    }

    mysqli_stmt_bind_param($statusStmt, "i", $application_id);
    mysqli_stmt_execute($statusStmt);
    $statusResult = mysqli_stmt_get_result($statusStmt);

    if (!$statusResult) {
        echo "Error executing query: " . mysqli_error($dbConn);
        exit();
    }

    $statusData = mysqli_fetch_assoc($statusResult);
    $grade_status = $statusData['grade_status'];
} else {
    echo "Application ID not provided.";
    exit();
}

// Handle form submission for sending messages
if (isset($_POST['message_content'])) {
    $message_content = $_POST['message_content'];

    // Insert the message into 'tbl_user_messages' using prepared statement
    $insertQuery = "INSERT INTO `tbl_user_messages` (`application_id`, `registrar_id`, `message_content`, `sent_at`)
                    VALUES (?, ?, ?, NOW())";
    $insertStmt = mysqli_prepare($dbConn, $insertQuery);

    if (!$insertStmt) {
        echo "Error preparing query: " . mysqli_error($dbConn);
        exit();
    }

    mysqli_stmt_bind_param($insertStmt, "iis", $application_id, $registrar_id, $message_content);
    $insertResult = mysqli_stmt_execute($insertStmt);

    if ($insertResult) {
        // Message successfully sent, you can add any success message or redirection here
        echo "Message Sent";
        header("Location: view_application.php?id=$application_id");
        exit();
    } else {
        echo "Error sending message: " . mysqli_error($dbConn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Application</title>
    <link rel="stylesheet" href="view_application.css">
</head>
<?php include('header.php') ?>

<body>

    <div class="container">
        <div class="head">
            <div class="img"><img src="/EASE-CHOLAR/user_profiles/<?php echo $applicationData['image']; ?>" alt="Profile"></div>
            <p>Application ID: <?php echo $applicationData['application_id']; ?>
            <p>Applicant Name: <?php echo $applicationData['applicant_name']; ?>
            <div class="reminder">
            <h2>Grade Status: <?php echo $grade_status; ?></h2>
            <span class="remind">*Please update the applicant grade status</span>

            <form method="post" action="update_grade_status.php">
                <input type="hidden" name="application_id" value="<?php echo $application_id; ?>">
                <label for="status">Status:</label>
                <select name="grade_status" id="grade_status">
                    <option value="Pending" <?php if ($grade_status == 'Pending') echo 'selected'; ?>>Pending</option>
                    <option value="Passed" <?php if ($grade_status == 'Passed') echo 'selected'; ?>>Passed</option>
                    <option value="Failed" <?php if ($grade_status == 'Failed') echo 'selected'; ?>>Failed</option>
                </select>

                <button type="submit">Update</button>
            </form>
            </div>
        </div>
        <hr>

        <div class="info">
            <h3 style="text-align: center;">Personal Information</h3>

            <label>Scholarship Name:</label> <?php echo $applicationData['scholarship_name']; ?>

            <div class="field">
                <label>Full Name: </label><?php echo $applicationData['last_name']; ?> <?php echo $applicationData['first_name']; ?> <?php echo $applicationData['middle_name']; ?>
            </div>
            <div class="field">
                <label>Date of Birth:</label> <?php echo $applicationData['dob']; ?>
            </div>
            <div class="field">
                <label>Place of Birth:</label> <?php echo $applicationData['pob']; ?>
            </div>
            <div class="field">
                <label>Gender:</label> <?php echo $applicationData['gender']; ?>
            </div>
            <div class="field">
                <label>Email:</label> <?php echo $applicationData['email']; ?>
            </div>
            <div class="field">
                <label>Mobile Number:</label> <?php echo $applicationData['mobile_num']; ?>
            </div>
            <div class="field">
                <label>Citizenship:</label> <?php echo $applicationData['citizenship']; ?>
            </div>
            <div class="field">
                <label>Barangay:</label> <?php echo $applicationData['barangay']; ?>
            </div>
            <div class="field">
                <label>Town/City:</label> <?php echo $applicationData['town_city']; ?>
            </div>
            <div class="field">
                <label>Province:</label> <?php echo $applicationData['province']; ?>
            </div>
            <div class="field">
                <label>Zip Code:</label> <?php echo $applicationData['zip_code']; ?>
            </div>
            <div class="field">
                <label>ID Number:</label> <?php echo $applicationData['id_number']; ?>
            </div>

            <div class="fam-info">
                <div class="fam-field">
                    <label>Father's Name:</label> <?php echo $applicationData['father_name']; ?>
                    <label>Father's Address:</label> <?php echo $applicationData['father_address']; ?>
                    <label>Father's Work:</label> <?php echo $applicationData['father_work']; ?>
                </div>
                <div class="fam-field">
                    <label>Mother's Name:</label> <?php echo $applicationData['mother_name']; ?>
                    <label>Mother's Address:</label> <?php echo $applicationData['mother_address']; ?>
                    <label>Mother's Work:</label> <?php echo $applicationData['mother_work']; ?>
                </div>
            </div>

            
        </div>
        <hr>
        <div class="message-box">
    <h3>Send Message to Applicant</h3>
    <form method="post" action="send_applicant_message.php">
        <input type="hidden" name="application_id" value="<?php echo $application_id; ?>">
        <input type="hidden" name="registrar_id" value="<?php echo $registrar_id; ?>"> <!-- Add this line -->
        <label for="message_content">Message:</label>
        <textarea name="reg_message_content" id="reg_message_content" rows="4" cols="50"></textarea>
        <button type="submit">Send</button>
    </form>
</div>
    </div>

</body>

</html>