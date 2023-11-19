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

if (isset($_GET['id']) && isset($_GET['user_id'])) {
  $application_id = $_GET['id'];
  $user_id = $_GET['user_id'];

  $query = "SELECT * FROM `tbl_scholarship_1_form` WHERE `application_id` = ? AND `user_id` = ?";
  $stmt = mysqli_prepare($dbConn, $query);

  if (!$stmt) {
    echo "Error preparing query: " . mysqli_error($dbConn);
    exit();
  }

  mysqli_stmt_bind_param($stmt, "ii", $application_id, $user_id);
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

  $statusQuery = "SELECT `status` FROM `tbl_scholarship_1_form` WHERE `application_id` = ? AND `user_id` = ?";
  $statusStmt = mysqli_prepare($dbConn, $statusQuery);

  if (!$statusStmt) {
    echo "Error preparing query: " . mysqli_error($dbConn);
    exit();
  }

  mysqli_stmt_bind_param($statusStmt, "ii", $application_id, $user_id);
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

  $source = 'tbl_scholarship_1_form';

  $insertQuery = "INSERT INTO `tbl_user_messages` (`application_id`, `admin_id`, `user_id`, `$columnToInsert`, `sent_at`, `read_status`, `source`)
                VALUES (?, ?, ?, ?, NOW(), 'unread', ?)";

  $insertStmt = mysqli_prepare($dbConn, $insertQuery);

  if (!$insertStmt) {
    echo "Error preparing query: " . mysqli_error($dbConn);
    exit();
  }

  mysqli_stmt_bind_param($insertStmt, "iiiss", $application_id, $admin_id, $user_id, $message_content, $source);
  $insertResult = mysqli_stmt_execute($insertStmt);

  if ($insertResult) {

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
                        window.location.href = "view_application1.php?id=' . $application_id . '&user_id=' . $user_id . '";
                    }
                }
            });
        });
    </script>';
} else {
    echo "Error sending message: " . mysqli_error($dbConn);
}
}


if (isset($_POST['status'])) {
  $newStatus = $_POST['status'];

  // Update the status in the database here
  $statusUpdateQuery = "UPDATE `tbl_scholarship_1_form` SET `status` = ? WHERE `application_id` = ?";
  $statusUpdateStmt = mysqli_prepare($dbConn, $statusUpdateQuery);

  if (!$statusUpdateStmt) {
    echo "Error preparing status update query: " . mysqli_error($dbConn);
    exit();
  }

  mysqli_stmt_bind_param($statusUpdateStmt, "si", $newStatus, $application_id);
  $statusUpdateResult = mysqli_stmt_execute($statusUpdateStmt);

  if ($statusUpdateResult) {
    $applicantEmail = $applicationData['email'];
    $applicantName = $applicationData['applicant_name'];
    $emailSubject = 'Application Status Update';
    $websiteLink = 'https://easecholarship.azurewebsites.net/';
    $emailBody = "Dear $applicantName,\n\nYour application status has been updated to: $newStatus\n\nPlease visit the website to check your application:\n$websiteLink\n";

    sendEmailNotification($applicantEmail, $applicantName, $emailSubject, $emailBody);

    $status_message = "Status updated";
  } else {
    mysqli_rollback($dbConn);
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
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if (isset($_POST['update_details'])) {

      $updatedLastName = mysqli_real_escape_string($dbConn, $_POST['last_name']);
      $updatedFirstName = mysqli_real_escape_string($dbConn, $_POST['first_name']);
      $updatedMiddleName = mysqli_real_escape_string($dbConn, $_POST['middle_name']);
      $updatedDob = mysqli_real_escape_string($dbConn, $_POST['dob']);
      $updatedPob = mysqli_real_escape_string($dbConn, $_POST['pob']);
      $updatedAge = mysqli_real_escape_string($dbConn, $_POST['age']);
      $updatedCitizenship = mysqli_real_escape_string($dbConn, $_POST['citizenship']);
      $updatedCivilStatus = mysqli_real_escape_string($dbConn, $_POST['civil_status']);
      $updatedGender = mysqli_real_escape_string($dbConn, $_POST['gender']);
      $updatedEmail = mysqli_real_escape_string($dbConn, $_POST['email']);
      $updatedMobileNum = mysqli_real_escape_string($dbConn, $_POST['mobile_num']);
      $updatedReligion = mysqli_real_escape_string($dbConn, $_POST['religion']);
      $updatedIdNum = mysqli_real_escape_string($dbConn, $_POST['id_number']);
      $updatedCourse = mysqli_real_escape_string($dbConn, $_POST['course']);
      $updatedYearLvl = mysqli_real_escape_string($dbConn, $_POST['year_lvl']);
      $updatedBarangay = mysqli_real_escape_string($dbConn, $_POST['barangay']);
      $updatedTownCity = mysqli_real_escape_string($dbConn, $_POST['town_city']);
      $updatedProvince = mysqli_real_escape_string($dbConn, $_POST['province']);
      $updatedZipCode = mysqli_real_escape_string($dbConn, $_POST['zip_code']);
      $updatedFLastName = mysqli_real_escape_string($dbConn, $_POST['father_lname']);
      $updatedFFirstName = mysqli_real_escape_string($dbConn, $_POST['father_fname']);
      $updatedFMiddleName = mysqli_real_escape_string($dbConn, $_POST['father_mname']);
      $updatedFatherWork = mysqli_real_escape_string($dbConn, $_POST['father_work']);
      $updatedMSurname = mysqli_real_escape_string($dbConn, $_POST['mother_sname']);
      $updatedMFirstName = mysqli_real_escape_string($dbConn, $_POST['mother_fname']);
      $updatedMMiddleName = mysqli_real_escape_string($dbConn, $_POST['mother_mname']);
      $updatedMotherWork = mysqli_real_escape_string($dbConn, $_POST['mother_work']);
      $updatedPrimSchool = mysqli_real_escape_string($dbConn, $_POST['primary_school']);
      $updatedSecSchool = mysqli_real_escape_string($dbConn, $_POST['secondary_school']);
      $updatedTerSchool = mysqli_real_escape_string($dbConn, $_POST['tertiary_school']);
      $updatedPrimYear = mysqli_real_escape_string($dbConn, $_POST['prim_year_grad']);
      $updatedSecYear = mysqli_real_escape_string($dbConn, $_POST['sec_year_grad']);
      $updatedTerYear = mysqli_real_escape_string($dbConn, $_POST['ter_year_grad']);

      $updateQuery = "UPDATE `tbl_scholarship_1_form` SET `last_name` = ?, `first_name` = ?, `middle_name` = ?, `dob` = ?, `pob` = ?, `age` = ?, `citizenship` = ?, `civil_status` = ?, `gender` = ?, `email` = ?, `mobile_num` = ?, `religion` = ?, `id_number` = ?, `course` = ?, `year_lvl` = ?, `barangay` = ?, `town_city` = ?, `province` = ?, `zip_code` = ?, `father_lname` = ?, `father_fname` = ?, `father_mname` = ?, `father_work` = ?, `mother_sname` = ?, `mother_fname` = ?, `mother_mname` = ?, `mother_work` = ?, `primary_school` = ?, `secondary_school` = ?, `tertiary_school` = ?, `prim_year_grad` = ?, `sec_year_grad` = ?, `ter_year_grad` = ? WHERE `application_id` = ? AND `user_id` = ?";
      $updateStmt = mysqli_prepare($dbConn, $updateQuery);

      if ($updateStmt) {
          mysqli_stmt_bind_param($updateStmt, "ssssssssssssssssssssssssssssssssssi", $updatedLastName, $updatedFirstName, $updatedMiddleName, $updatedDob, $updatedPob, $updatedAge, $updatedCitizenship, $updatedCivilStatus, $updatedGender, $updatedEmail, $updatedMobileNum, $updatedReligion, $updatedIdNum, $updatedCourse, $updatedYearLvl, $updatedBarangay, $updatedTownCity, $updatedProvince, $updatedZipCode, $updatedFLastName, $updatedFFirstName, $updatedFMiddleName, $updatedFatherWork, $updatedMSurname, $updatedMFirstName, $updatedMMiddleName, $updatedMotherWork, $updatedPrimSchool, $updatedSecSchool, $updatedTerSchool, $updatedPrimYear, $updatedSecYear, $updatedTerYear, $application_id, $user_id);
          $updateResult = mysqli_stmt_execute($updateStmt);

          if ($updateResult) {
              // Update successful
              echo '<script>alert("Information updated successfully!");</script>';
          } else {
              // Update failed
              echo '<script>alert("Error updating information: ' . mysqli_error($dbConn) . '");</script>';
          }
      } else {
          echo '<script>alert("Error preparing update query: ' . mysqli_error($dbConn) . '");</script>';
      }
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
                    window.location.href = "view_application1.php?id=' . $application_id . '&user_id=' . $user_id . '";
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


            <form method="post" action="view_application1.php?id=<?php echo $application_id; ?>&user_id=<?php echo $user_id; ?>">

              <select name="status" id="status">
                <option value="Pending" <?php if ($status == 'Pending') echo 'selected'; ?>>Pending</option>
                <option value="In Review" <?php if ($status == 'In Review') echo 'selected'; ?>>In Review</option>
                <option value="Qualified" <?php if ($status == 'Qualified') echo 'selected'; ?>>Qualified</option>
                <option value="Accepted" <?php if ($status == 'Accepted') echo 'selected'; ?>>Accepted</option>
                <option value="Rejected" <?php if ($status == 'Rejected') echo 'selected'; ?>>Rejected</option>
              </select>

              <button class="submit-button" type="submit">Update</button>
            </form>
          </div>
        </div>

        <div class="form-first">
        <form action="view_application1.php?id=<?php echo $application_id; ?>&user_id=<?php echo $user_id; ?>" method="POST" enctype="multipart/form-data">

          <h3 style="color:darkgreen">PERSONAL INFORMATION:</h3>
          <br>
          <div class="details personal">
            <div class="fields">
              <div class="input-field">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" placeholder="Enter your lastname" value="<?php echo $applicationData['last_name']; ?>" >
                <div class="validation-message" id="last_name-error"></div>
              </div>
              <div class="input-field">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" placeholder="Enter your firstname" value="<?php echo $applicationData['first_name']; ?>" >
                <div class="validation-message" id="first_name-error"></div>
              </div>
              <div class="input-field">
                <label for="middle_name">Middle Name</label>
                <input type="text" id="middle_name" name="middle_name" placeholder="Enter your middlename" value="<?php echo $applicationData['middle_name']; ?>" >
                <div class="validation-message" id="middle_name-error"></div>
              </div>
              <div class="input-field">
                <label>Date of Birth</label>
                <input type="date" id="dob" name="dob" placeholder="Enter birthdate" value="<?php echo $applicationData['dob']; ?>" >
                <div class="validation-message" id="date_birth-error"></div>
              </div>
              <div class="input-field">
                <label>Place of Birth</label>
                <input type="text" id="pob" name="pob" placeholder="Enter birthplace" value="<?php echo $applicationData['pob']; ?>" >
                <div class="validation-message" id="pob-error"></div>
              </div>

              <div class="input-field">
                <label for="zip_code">Age</label>
                <input type="number" id="age" name="age" placeholder="Age" value="<?php echo $applicationData['age']; ?>" >
                <div class="validation-message" id="age-error"></div>
              </div>

              <div class="input-field">
                <label>Citizenship</label>
                <input type="text" id="citizenship" name="citizenship" placeholder="Enter your citizenship" value="<?php echo $applicationData['citizenship']; ?>" >
                <div class="validation-message" id="citizenship-error"></div>
              </div>

              <div class="input-field">
                <label>Civil Status</label>
                <select id="civil_status" name="civil_status" >
                  <option><?php echo $applicationData['civil_status']; ?></option>
                </select>
                <div class="validation-message" id="civil_status-error"></div>
              </div>

              <div class="input-field">
                <label>Sex</label>
                <select id="gender" name="gender" >
                  <option><?php echo $applicationData['gender']; ?></option>
                </select>
                <div class="validation-message" id="gender-error"></div>
              </div>


              <div class="input-field">
                <label>Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo $applicationData['email']; ?>" >
                <div class="validation-message" id="email-error"></div>
              </div>
              <div class="input-field">
                <label>Mobile Number</label>
                <input type="number" id="mobile_num" name="mobile_num" placeholder="09XXXXXXXXX" value="<?php echo $applicationData['mobile_num']; ?>" >
                <div class="validation-message" id="mobile_num-error"></div>
              </div>

              <div class="input-field">
                <label>Religion</label>
                <input type="text" id="religion" name="religion" placeholder="Enter your religion" value="<?php echo $applicationData['religion']; ?>" >
                <div class="validation-message" id="religion-error"></div>
              </div>

            </div>




            <div class="fields">
              <div class="input-field">
                <label>School ID Number</label>
                <input type="number" id="id_number" name="id_number" placeholder="2XXXX21" value="<?php echo $applicationData['id_number']; ?>" >
                <div class="validation-message" id="id_number-error"></div>
              </div>

              <div class="input-field">
                <label>Course</label>
                <select id="course" name="course" >
                  <option><?php echo $applicationData['course']; ?></option>
                </select>
                <div class="validation-message" id="course-error"></div>
              </div>

              <div class="input-field">
                <label>Year Level</label>
                <select id="year_lvl" name="year_lvl" >
                  <option><?php echo $applicationData['year_lvl']; ?></option>
                </select>
                <div class="validation-message" id="year_lvl-error"></div>
              </div>
            </div>

            <div class="form-second">
              <div class="input-field">
                <h3 style="color:darkgreen">PERMANENT ADDRESS</h3>
                <div class="address-inputs">
                  <input type="text" name="barangay" value="<?php echo $applicationData['barangay']; ?>" >
                  <input type="text" name="town_city" value="<?php echo $applicationData['town_city']; ?>" >
                  <input type="text" name="province" value="<?php echo $applicationData['province']; ?>" >
                  <input type="number" name="zip_code" value="<?php echo $applicationData['zip_code']; ?>" >
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
                  <label>Last Name</label>
                  <input type="text" id="father_lname" name="father_lname" placeholder="Enter your father's lastname" value="<?php echo $applicationData['father_lname']; ?>" >
                  <div class="validation-message" id="father_lname-error"></div>

                  <label>First Name</label>
                  <input type="text" id="father_fname" name="father_fname" placeholder="Enter your father's firstname" value="<?php echo $applicationData['father_fname']; ?>" >
                  <div class="validation-message" id="father_fname-error"></div>

                  <label>MIddle Name</label>
                  <input type="text" id="father_mname" name="father_mname" placeholder="Enter your father's middlename" value="<?php echo $applicationData['father_mname']; ?>" >
                  <div class="validation-message" id="father_mname-error"></div>

                  <label>Occupation</label>
                  <input type="text" id="father_work" name="father_work" placeholder="Enter Occupation" value="<?php echo $applicationData['father_work']; ?>" >
                  <div class="validation-message" id="father_work-error"></div>
                </div>
              </div>

              <div class="form">
                <div class="input-field">
                  <span class="title"> MOTHER </span>
                  <hr>
                  <label>Surname</label>
                  <input type="text" id="mother_sname" name="mother_sname" placeholder="Enter mother's surname" value="<?php echo $applicationData['mother_sname']; ?>" >
                  <div class="validation-message" id="mother_sname-error"></div>

                  <label>First Name</label>
                  <input type="text" id="mother_fname" name="mother_fname" placeholder="Enter mother's firstname" value="<?php echo $applicationData['mother_fname']; ?>" >
                  <div class="validation-message" id="mother_fname-error"></div>

                  <label>Middle Name</label>
                  <input type="text" id="mother_mname" name="mother_mname" placeholder="Enter mother's middlename" value="<?php echo $applicationData['mother_mname']; ?>" >
                  <div class="validation-message" id="mother_mname-error"></div>

                  <label>Occupation</label>
                  <input type="text" id="mother_work" name="mother_work" placeholder="Enter Occupation" value="<?php echo $applicationData['mother_work']; ?>" >
                  <div class="validation-message" id="mother_work-error"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="form first">
          <h3 style="color: darkgreen;">EDUCATIONAL BACKGROUND:</h4>
            <br>
            <div class="select-input-field">
              <div class="input-field">
                <label>Primary School</label>
                <input type="text" id="primary_school" name="primary_school" placeholder="Name of your Primary School" value="<?php echo $applicationData['primary_school']; ?>" >
                <div class="validation-message" id="primary_school-error"></div>
              </div>
              <div class="input-field">
                <label>Year Graduated</label>
                <input type="number" id="prim_year_grad" name="prim_year_grad" placeholder="Primary year graduated" value="<?php echo $applicationData['prim_year_grad']; ?>" >
                <div class="validation-message" id="prim_year_grad-error"></div>
              </div>

              <div class="input-field">
                <label>Secondary School</label>
                <input type="text" id="secondary_school" name="secondary_school" placeholder="Name of your Secondary School" value="<?php echo $applicationData['secondary_school']; ?>" >
                <div class="validation-message" id="secondary_school-error"></div>
              </div>
              <div class="input-field">
                <label>Year Graduated</label>
                <input type="number" id="sec_year_grad" name="sec_year_grad" placeholder="Primary year graduated" value="<?php echo $applicationData['sec_year_grad']; ?>" >
                <div class="validation-message" id="sec_year_grad-error"></div>
              </div>


              <div class="input-field">
                <label>Tertiary School</label>
                <input type="text" id="tertiary_school" name="tertiary_school" placeholder="Name of your Tertiary School" value="<?php echo $applicationData['tertiary_school']; ?>" >
                <div class="validation-message" id="tertiary_school-error"></div>
              </div>
              <div class="input-field">
                <label>Year Graduated</label>
                <input type="text" id="ter_year_grad" name="ter_year_grad" placeholder="Tertiary year graduated" value="<?php echo $applicationData['ter_year_grad']; ?>" >
                <div class="validation-message" id="ter_year_grad-error"></div>
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
                    if (file_exists($filePath)) {
                      echo '<p>File: <a href="' . $filePath . '" target="_blank">' . $fileName . '</a></p>';
                    } else {
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

                $sqlAttachmentMessages = "SELECT attach_files FROM tbl_user_messages WHERE application_id = ? AND user_id = ? AND source = 'tbl_scholarship_1_form'";
                $stmtAttachmentMessages = $dbConn->prepare($sqlAttachmentMessages);

                if ($stmtAttachmentMessages) {
                  $stmtAttachmentMessages->bind_param("ii", $application_id, $user_id);
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

                $attachments = $applicationData['attachments'];

                if (!empty($attachments)) {
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
            <div class="update-container">
            <button class="update-button" type="submit" name="update_details">Update Details</button>
            </div>
              </form>

            <hr>
            <div class="message-box">
              <h3>Send Message to Applicant</h3>
              <form method="post" action="view_application1.php?id=<?php echo $application_id; ?>&user_id=<?php echo $user_id; ?>">
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
                      <textarea name="message_content" id="message_content" rows="6" cols="170"></textarea>

                      
                    </div>
                    <button class="cancel-button" type="button" onclick="window.location.href='applicants.php'">Back</button>
                    
                    <button class="send-button" type="submit">Send</button> 
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