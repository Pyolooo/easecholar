<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '/wamp64/www/EASE-CHOLAR/PHPMailer-master/src/Exception.php';
require '/wamp64/www/EASE-CHOLAR/PHPMailer-master/src/PHPMailer.php';
require '/wamp64/www/EASE-CHOLAR/PHPMailer-master/src/SMTP.php';

// The rest of your PHPMailer configuration and email sending code goes here

include 'connection.php';
session_name("OsaSession");
session_start();

// Check if 'admin_id' is not set in the session, redirect to login page
if (!isset($_SESSION['admin_id'])) {
    header('location: osa_login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];

if (isset($_GET['logout'])) {
    unset($admin_id);
    session_destroy();
    header('location: osa_login.php');
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
    $statusQuery = "SELECT `status` FROM `tbl_userapp` WHERE `application_id` = ?";
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
    $status = $statusData['status'];
} else {
    echo "Application ID not provided.";
    exit();
}

// Handle form submission for sending messages
if (isset($_POST['message_content'])) {
    $message_content = $_POST['message_content'];

    // Insert the message into 'tbl_user_messages' using prepared statement
    $insertQuery = "INSERT INTO `tbl_user_messages` (`application_id`, `admin_id`, `osa_message_content`, `sent_at`, `read_status`)
                    VALUES (?, ?, ?, NOW(), 'unread')";
    $insertStmt = mysqli_prepare($dbConn, $insertQuery);

    if (!$insertStmt) {
        echo "Error preparing query: " . mysqli_error($dbConn);
        exit();
    }

    mysqli_stmt_bind_param($insertStmt, "iis", $application_id, $admin_id, $message_content);
    $insertResult = mysqli_stmt_execute($insertStmt);

    if ($insertResult) {
        // Message successfully sent, you can add any success message or redirection here
        echo "Message Sent";

        // Send email notification to the applicant
        $applicantEmail = $applicationData['email'];
        $applicantName = $applicationData['applicant_name'];
        $emailSubject = 'New Message from OSA';
        $emailBody = "Dear $applicantName,\n\nYou have received a new message from OSA:\n\n$message_content\n\nPlease log in to check your messages.\n";

        sendEmailNotification($applicantEmail, $applicantName, $emailSubject, $emailBody);

        header("Location: view_application.php?id=$application_id");
        exit();
    } else {
        echo "Error sending message: " . mysqli_error($dbConn);
    }
}

// After updating the status in the database, send an email notification to the applicant
if (isset($_POST['status'])) {
    $newStatus = $_POST['status'];

    // Update the status in the database here
    $statusUpdateQuery = "UPDATE `tbl_userapp` SET `status` = ? WHERE `application_id` = ?";
    $statusUpdateStmt = mysqli_prepare($dbConn, $statusUpdateQuery);

    if (!$statusUpdateStmt) {
        echo "Error preparing status update query: " . mysqli_error($dbConn);
        exit();
    }

    mysqli_stmt_bind_param($statusUpdateStmt, "si", $newStatus, $application_id);
    $statusUpdateResult = mysqli_stmt_execute($statusUpdateStmt);

    if ($statusUpdateResult) {
        // Status updated successfully, proceed with sending the email notification
        $applicantEmail = $applicationData['email'];
        $applicantName = $applicationData['applicant_name'];
        $emailSubject = 'Application Status Update';
        $emailBody = "Dear $applicantName,\n\nYour application status has been updated to: $newStatus\n\nPlease visit the website to check your application.\n";

        sendEmailNotification($applicantEmail, $applicantName, $emailSubject, $emailBody);

        echo 'Message Sent';
    } else {
        mysqli_rollback($dbConn); // Rollback the transaction in case of a status update error
        echo "Error updating status: " . mysqli_error($dbConn);
    }
}

// Function to send email notifications
function sendEmailNotification($toEmail, $toName, $subject, $body)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  // Specify your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'easecholar@gmail.com'; // SMTP username
        $mail->Password = 'benz pupq lkxj amje'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port = 587; // TCP port to connect to

        // Recipients
        $mail->setFrom('easecholar@gmail.com', 'OSA');
        $mail->addAddress($toEmail, $toName); // Add recipient's email and name

        // Content
        $mail->isHTML(false); // Set email format to plain text
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
    } catch (Exception $e) {
        echo 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Application</title>
    <link rel="stylesheet" href="css/view_application.css">
</head>
<?php include('header.php') ?>

<body>

    <div class="container">
        <div class="head">
            <div class="img"><img src="/EASE-CHOLAR/user_profiles/<?php echo $applicationData['image']; ?>" alt="Profile"></div>
            <p>Application ID: <?php echo $applicationData['application_id']; ?></p>
            <p>Applicant Name: <?php echo $applicationData['applicant_name']; ?></p>
            <div class="reminder">
                <h2>Status: <?php echo $status; ?></h2>
                <span class="remind">*Please update the applicant status</span>

                <form method="post" action="view_application.php?id=<?php echo $application_id; ?>">
                    <label for="status">Status:</label>
                    <select name="status" id="status">
                        <option value="Pending" <?php if ($status == 'Pending') echo 'selected'; ?>>Pending</option>
                        <option value="In Review" <?php if ($status == 'In Review') echo 'selected'; ?>>In Review</option>
                        <option value="Qualified" <?php if ($status == 'Qualified') echo 'selected'; ?>>Qualified</option>
                        <option value="Accepted" <?php if ($status == 'Accepted') echo 'selected'; ?>>Accepted</option>
                        <option value="Rejected" <?php if ($status == 'Rejected') echo 'selected'; ?>>Rejected</option>
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

            <h3>Other Documents</h3>
            <?php
            // Assuming $applicationData['file'] contains comma-separated file names
            if (!empty($applicationData['file'])) {
                $fileNames = explode(',', $applicationData['file']);
                foreach ($fileNames as $fileName) {
                    $filePath = '/EASE-CHOLAR/file_uploads/' . $fileName;
                    // Update the file path
                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $filePath)) {
                        echo '<p>File: <a href="' . $filePath . '" target="_blank">' . $fileName . '</a></p>';
                    } else {
                        echo '<p>File path not found: ' . $filePath . '</p>';
                    }
                }
            }
            ?>
        </div>
        <hr>
        <div class="message-box">
            <h3>Send Message to Applicant</h3>
            <form method="post" action="view_application.php?id=<?php echo $application_id; ?>">
                <input type="hidden" name="application_id" value="<?php echo $application_id; ?>">
                <input type="hidden" name="admin_id" value="<?php echo $admin_id; ?>"> <!-- Add this line -->
                <label for="message_content">Message:</label>
                <textarea name="message_content" id="message_content" rows="4" cols="50"></textarea>
                <button type="submit">Send</button>
            </form>
        </div>
    </div>

</body>

</html>
