<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer-master/src/Exception.php';
require_once 'PHPMailer-master/src/PHPMailer.php';
require_once 'PHPMailer-master/src/SMTP.php';


include '../include/connection.php';
session_name("OsaSession");
session_start();

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

$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : ''; 

if (isset($_GET['id'])) {
    $application_id = $_GET['id'];

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
    $message_choice = $_POST['message_choice'];

    $columnToInsert = ($message_choice === 'request_attachments') ? 'attach_files' : 'osa_message_content';


    $insertQuery = "INSERT INTO `tbl_user_messages` (`application_id`, `admin_id`, `user_id`, `$columnToInsert`, `sent_at`, `read_status`)
                VALUES (?, ?, ?, ?, NOW(), 'unread')";

    $insertStmt = mysqli_prepare($dbConn, $insertQuery);

    if (!$insertStmt) {
        echo "Error preparing query: " . mysqli_error($dbConn);
        exit();
    }

    mysqli_stmt_bind_param($insertStmt, "iiis", $application_id, $admin_id, $user_id, $message_content);
    $insertResult = mysqli_stmt_execute($insertStmt);

    if ($insertResult) {
        $success_message = "Message Sent";

    $applicantEmail = $applicationData['email'];
    $applicantName = $applicationData['applicant_name'];
    $phoneNumber = $applicationData['mobile_num'];
    $emailSubject = 'New Message from OSA';
    $websiteLink = 'https://easecholarship.azurewebsites.net/';
    $emailBody = "Dear $applicantName,\n\nYou have received a new message from OSA:\n\n$message_content\n\nPlease log in to check your messages.:\n$websiteLink\n";

    sendEmailNotification($applicantEmail, $applicantName, $emailSubject, $emailBody);

 // Send SMS notification
 $phoneNumber = $applicationData['mobile_num'];
 $smsMessage = "Dear $applicantName,\n\nYou have received a new message from OSA:\n\n$message_content";

 sendSmsNotification($phoneNumber, $smsMessage);


 $success_message = "Message Sent";

 echo '<script>
     document.addEventListener("DOMContentLoaded", function() {
         Swal.fire({
             position: "center",
             icon: "success",
             title: "' . $success_message . '",
             showConfirmButton: false,
             timer: 1500
         }).then((result) => {
             if (result.dismiss === Swal.DismissReason.timer) {
                 if (' . isset($application_id) . ' && ' . isset($user_id) . ') {
                     window.location.href = "view_application.php?id=' . $application_id . '&user_id=' . $user_id . '";
                 }
             }
         });
     });
 </script>';
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
        $websiteLink = 'https://easecholarship.azurewebsites.net/';
        $emailBody = "Dear $applicantName,\n\nYour application status has been updated to: $newStatus\n\nPlease visit the website to check your application:\n$websiteLink\n";

        sendEmailNotification($applicantEmail, $applicantName, $emailSubject, $emailBody);

        $status_message = "Status updated";
    } else {
        mysqli_rollback($dbConn); // Rollback the transaction in case of a status update error
        echo "Error updating status: " . mysqli_error($dbConn);
    }
}

function sendSmsNotification($phoneNumber, $message) {
    $apiKey = 'd9e762406ca20e174568cd6d83026550';
    $url = 'https://api.semaphore.co/api/v4/messages';
  
    $data = [
      'apikey' => $apiKey,
      'number' => $phoneNumber,
      'message' => $message,
      'sendername' => 'EASECHOLAR'
    ];
  
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  
  
    $output = curl_exec($ch);
  
    if ($output === false) {
      echo 'Curl error: ' . curl_error($ch);
    } else {
      // Check the response for error messages
      $response = json_decode($output, true);
      if (isset($response['error'])) {
        echo 'Semaphore API Error: ' . $response['error']['description'];
      } else {
        // SMS sent successfully
        return true;
      }
    }
  
    curl_close($ch);
    return false;
  }
  
// Function to send email notifications
function sendEmailNotification($toEmail, $toName, $subject, $body)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'easecholar@gmail.com';
        $mail->Password = 'benz pupq lkxj amje';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('easecholar@gmail.com', 'OSA');
        $mail->addAddress($toEmail, $toName);

        // Content
        $mail->isHTML(false);
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</head>

<body>
    <?php include('../include/header.php'); ?>
    <div class="wrapper">

    <?php
if (!empty($status_message)) {
    echo '<script>
        Swal.fire({
            position: "center",
            icon: "success",
            title: "' . $status_message . '",
            showConfirmButton: false,
            timer: 1500
        }).then((result) => {
            if (result.dismiss === Swal.DismissReason.timer) {
                if (' . isset($application_id) . ' && ' . isset($user_id) . ') {
                    window.location.href = "view_application.php?id=' . $application_id . '&user_id=' . $user_id . '";
                }
            }
        });
    </script>';
}

?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="container">
                <div class="head">
                    <div class="img"><img src="/EASE-CHOLAR/user_profiles/<?php echo $applicationData['image']; ?>" alt="Profile"></div>
                    <p class="applicant-name"><?php echo $applicationData['applicant_name']; ?></p>
                    <div class="reminder">
                        <h3 class="status-container">Status: <span class="status <?php echo strtolower($status); ?>"><?php echo $status; ?></span></h3>
                        <span class="remind">*Please update the applicant status: </span>


                        <form method="post" action="view_application.php?id=<?php echo $application_id; ?>">
                            <select name="status" id="status">
                                <option value="Pending" <?php if ($status == 'Pending') echo 'selected'; ?>>Pending</option>
                                <option value="In Review" <?php if ($status == 'In Review') echo 'selected'; ?>>In Review</option>
                                <option value="Qualified" <?php if ($status == 'Qualified') echo 'selected'; ?>>Qualified</option>
                                <option value="Accepted" <?php if ($status == 'Accepted') echo 'selected'; ?>>Accepted</option>
                                <option value="Rejected" <?php if ($status == 'Rejected') echo 'selected'; ?>>Rejected</option>
                            </select>

                            <button class="submit-button" type="submit">Update</button>
                            <?php
                if (isset($status_message)) {
                    echo '<p style="color: green; text-align:left; margin-top:10px">' . $status_message . '</p>';
                }
                ?>
                        </form>
                    </div>
                </div>
                <div class="form-first">
                    <h3 style="color:darkgreen">PERSONAL INFORMATION:</h3>
                    <br>
                    <div class="details personal">
                        <div class="fields">
                            <div class="input-field">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo $applicationData['last_name']; ?>" disabled>
                            </div>
                            <div class="input-field">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo $applicationData['first_name']; ?>" disabled>
                            </div>
                            <div class="input-field">
                                <label for="middle_name">Middle Name</label>
                                <input type="text" id="middle_name" name="middle_name" value="<?php echo $applicationData['middle_name']; ?>" disabled>
                            </div>
                            <div class="input-field">
                                <label>Date of Birth</label>
                                <input type="date" name="dob" value="<?php echo $applicationData['dob']; ?>" disabled>
                            </div>
                            <div class="input-field">
                                <label>Place of Birth</label>
                                <input type="text" name="pob" placeholder="Enter birth date" value="<?php echo $applicationData['pob']; ?>" disabled>
                            </div>
                            <div class="input-field">
                                <label>Gender</label>
                                <select name="gender" disabled>
                                    <option><?php echo $applicationData['gender']; ?></option>
                                </select>
                            </div>
                        


                        <div class="input-field">
                            <label>Email</label>
                            <input type="email" name="email" value="<?php echo $applicationData['email']; ?>" disabled>
                        </div>
                        <div class="input-field">
                                <label>Mobile Number</label>
                                <input type="number" name="mobile_num" value="<?php echo $applicationData['mobile_num']; ?>" disabled>
                            </div>

                            <div class="input-field">
                                <label>Citizenship</label>
                                <input type="text" name="citizenship" value="<?php echo $applicationData['citizenship']; ?>" disabled>
                            </div>

                            </div>
                        <div class="fields">
                            <div class="input-field">
                                <label>School ID Number</label>
                                <input type="text" name="id_number" value="<?php echo $applicationData['id_number']; ?>" disabled>
                            </div>
                            <div class="input-field">
                                <label>Course</label>
                                <select name="course" disabled>
                                    <option value="BSIT" <?php if ($applicationData['course'] === 'BSIT') echo 'selected'; ?>>BSIT</option>
                                    <option value="BSA" <?php if ($applicationData['course'] === 'BSA') echo 'selected'; ?>>BSA</option>
                                </select>
                            </div>
                            <div class="input-field">
                                <label>Year level</label>
                                <input type="text" name="year_lvl" value="<?php echo $applicationData['year_lvl']; ?>" disabled>
                            </div>
                        </div>

                        <div class="form-second">
                            <div class="input-field">
                                <h3 style="color:darkgreen">PERMANENT ADDRESS</h3>
                                <div class="address-inputs">
                                    <input type="text" name="barangay" value="<?php echo $applicationData['barangay']; ?>" disabled>
                                    <input type="text" name="town_city" value="<?php echo $applicationData['town_city']; ?>" disabled>
                                    <input type="text" name="province" value="<?php echo $applicationData['province']; ?>" disabled>
                                    <input type="number" name="zip_code" value="<?php echo $applicationData['zip_code']; ?>" disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-second">
                    <h3 style="color:darkgreen">FAMILY BACKGROUND:</h3>
                    <div class="details family">
                        <div class="fields-info">
                            <div class="form">
                                <div class="input-field">
                                    <span class="title"> FATHER </span>
                                    <hr>
                                    <label>Name</label>
                                    <input type="text" name="father_name" value="<?php echo $applicationData['father_name']; ?>" disabled>
                                    <label>Address</label>
                                    <input type="text" name="father_address" placeholder="Enter address" value="<?php echo $applicationData['father_address']; ?>" disabled>
                                    <label>Occupation</label>
                                    <input type="text" name="father_work" value="<?php echo $applicationData['father_work']; ?>" disabled>
                                </div>
                            </div>

                            <div class="form">
                                <div class="input-field">
                                    <span class="title"> MOTHER </span>
                                    <hr>
                                    <label>Name</label>
                                    <input type="text" name="mother_name" value="<?php echo $applicationData['mother_name']; ?>" disabled>
                                    <label>Address</label>
                                    <input type="text" name="mother_address" placeholder="Enter address" value="<?php echo $applicationData['mother_address']; ?>" disabled>
                                    <label>Occupation</label>
                                    <input type="text" name="mother_work" placeholder="Enter Occupation" value="<?php echo $applicationData['mother_work']; ?>" disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
          <div class="select-input-field">
                <div class="input-field">
                <label>Total Gross Income</label>
                <input type="number" id="email" name="gross_income" value="<?php echo $applicationData['gross_income']; ?>"disabled>
              </div>
              <div class="input-field">
                <label>No. of Siblings in the family</label>
                <input type="number" id="mobile_num" name="num_siblings"value="<?php echo $applicationData['num_siblings']; ?>" disabled>
              </div>
                </div>

                <h3 style="color:darkgreen">REQUIREMENTS UPLOADED</h3>
        <div class="attachments-container">
          <div class="files-column">
            <h4 class="files-label">Files Uploaded</h4>
            <?php
            if (!empty($applicationData['file'])) {
              $fileNames = explode(',', $applicationData['file']);
              foreach ($fileNames as $fileName) {
                $filePath = '../file_uploads/' . $fileName;
                // Check if the file exists on the server
                if (file_exists($filePath)) {
                  // Display a link to the file
                  echo '<p>File: <a href="' . $filePath . '" target="_blank">' . $fileName . '</a></p>';
                } else {
                  // Handle the case where the file is missing
                  echo '<p>File not found: ' . $fileName . '</p>';
                }
              }
            } else {
              echo '<p>No files uploaded</p>';
            }
            ?>

          </div>


          <div class="attachments-column">
    <h4 class="files-label">Lack of Documents</h4>
    <?php
    $attachmentsExist = false;

    $sqlAttachmentMessages = "SELECT attach_files FROM tbl_user_messages WHERE application_id = ?";
$stmtAttachmentMessages = $dbConn->prepare($sqlAttachmentMessages);

if ($stmtAttachmentMessages) {
    $stmtAttachmentMessages->bind_param("i", $application_id);
    $stmtAttachmentMessages->execute();
    $resultAttachmentMessages = $stmtAttachmentMessages->get_result();

    if ($resultAttachmentMessages->num_rows > 0) {
        while ($rowAttachment = $resultAttachmentMessages->fetch_assoc()) {
            $attach_files = $rowAttachment['attach_files'];

            if (!empty($attach_files)) {
                // Display the attachments if they exist
                $attachmentNames = explode(',', $attach_files);
                foreach ($attachmentNames as $attachmentName) {
                    $attachmentPath = '../file_uploads/' . $attachmentName;
                    if (file_exists($attachmentPath)) {
                        echo '<p>Attachment: <a href="' . $attachmentPath . '" target="_blank">' . $attachmentName . '</a></p>';
                    } else {
                        echo '<p>' . $attachmentName . '</p>';
                    }
                    $attachmentsExist = true;
                }
            }
        }
    }
}

    // Fetch attachment files from the tbl_userapp table
    $attachments = $applicationData['attachments'];

    if (!empty($attachments)) {
        // Display the attachments from tbl_userapp if they exist
        $attachmentNames = explode(',', $attachments);
        foreach ($attachmentNames as $attachmentName) {
            $attachmentPath = '../file_uploads/' . $attachmentName;
            if (file_exists($attachmentPath)) {
                echo '<p><a href="' . $attachmentPath . '" target="_blank">' . $attachmentName . '</a></p>';
            } else {
                echo '<p>' . $attachmentName . '</p>';
            }
            $attachmentsExist = true;
        }
    }

    if (!$attachmentsExist) {
        echo '<p>No attachments uploaded</p>';
    }
    ?>
</div>

        </div>

                <hr>
                <div class="message-box">
    <h3>Send Message to Applicant</h3>
    <form method="post" action="view_application.php?id=<?php echo $application_id; ?>">
        <div class="message-form">
            <input type="hidden" name="user_id" value="<?php echo $applicationData['user_id']; ?>">
            <input type="hidden" name="application_id" value="<?php echo $application_id; ?>">
            <input type="hidden" name="admin_id" value="<?php echo $admin_id; ?>">

            <div class="message-box-container">
                <input type="radio" name="message_choice" value="send_message" id="send_message" checked>
                <label for="send_message">Send Message</label>

                <input type="radio" name="message_choice" value="request_attachments" id="request_attachments">
                <label for="request_attachments">Request Attachments</label>
            </div>

            <div class="message-box-container">
                <div class="message-label">
                    <label for="message_content">Message:</label>
                </div>
                <div class="text-area">
                      <textarea name="message_content" id="message_content" rows="6" cols="172"></textarea>
                    </div>
                    <button class="cancel-button" type="button" onclick="window.location.href='applicants.php'">Back</button>
                    
                    <button class="send-button" type="submit">Send</button> 
                  </div>
            </div>
        </div>
    </form>
</div>
            </div>
        </form>

    </div>
    <script>
    </script>
</body>

</html>