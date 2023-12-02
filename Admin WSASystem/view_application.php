<?php
include '../include/connection.php';
session_name("AdminSession");
session_start();
$super_admin_id = $_SESSION['super_admin_id'];

if (!isset($super_admin_id)) {
  header('location: admin_login.php');
  exit();
}

if (isset($_GET['logout'])) {
  unset($super_admin_id);
  session_destroy();
  header('location: admin_login.php');
  exit();
}


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

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Application</title>
  <link rel="stylesheet" href="css/view_application.css">
</head>

<body>
  <?php include('../include/header.php'); ?>

  <div class="wrapper">
    <form action="" method="POST" enctype="multipart/form-data">
      <div class="container">
      <div class="top-section">
        <div class="head">
          <div class="img"><img src="../user_profiles/<?php echo $applicationData['image']; ?>" alt="Profile"></div>
          <p class="applicant-name"><?php echo $applicationData['applicant_name']; ?></p>
          <div class="reminder">
            <h3 class="status-container">Status: <span class="status <?php echo strtolower($status); ?>"><?php echo $status; ?></span></h3>
          </div>
        </div>

        <div class="reasons-container">
      <label class="reason-label">Reasons:</label>
      <?php
      if (!empty($applicationData['reasons'])) {
        $reasonsArray = json_decode($applicationData['reasons']);

        foreach ($reasonsArray as $reason) {
          echo '<div><span class="reason-list">' . htmlspecialchars($reason) . '</span></div>';
        }
      }

      if (!empty($applicationData['other_reason'])) {
        echo '<br><div><span class="other-reason">Other reason: </span> <span class = "other-reason-list"> ' . htmlspecialchars($applicationData['other_reason']) . '</span></div>';
      }
      ?>
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
            </div>


            <div class="input-field">
              <label>Email</label>
              <input type="email" name="email" value="<?php echo $applicationData['email']; ?>" disabled>
            </div>
            <div class="fields">
              <div class="input-field">
                <label>School ID Number</label>
                <input type="text" name="id_number" value="<?php echo $applicationData['id_number']; ?>" disabled>
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
            <h4 class="files-label">Attachments</h4>
            <?php
            $attachmentFiles = [];

            if (!empty($applicationData['attachments'])) {
              $attachmentFiles = explode(',', $applicationData['attachments']);
            }

            if (!empty($attachmentFiles)) {
              foreach ($attachmentFiles as $attachmentName) {
                $attachmentPath = '../file_uploads/' . $attachmentName;
                if (file_exists($attachmentPath)) {
                  echo '<p>Attachment: <a href="' . $attachmentPath . '" target="_blank">' . $attachmentName . '</a></p>';
                } else {
                  echo '<p>Attachment not found: ' . $attachmentName . '</p>';
                }
              }
            } else {
              echo '<p>No attachments uploaded</p>';
            }
            ?>
          </div>

        </div>

        <hr><br>
        <div class="status-info">
          <?php
          if ($status === 'Pending') {
            echo "<p>Application has been received but has not yet been reviewed or processed. It's awaiting initial assessment.</p>";
          } elseif ($status === 'In Review') {
            echo "<p>Application is actively being evaluated by the scholarship committee or administrators. They are diligently assessing your eligibility and qualifications to make informed decisions. Rest assured that your application is receiving careful consideration and is part of a competitive selection process.</p>";
          } elseif ($status === 'Qualified') {

            echo "<p>Application is marked as 'Qualified,' it suggests that application meet the eligibility criteria and have advanced to the next stage of consideration. We recognize strong potential in the application, and we will continue to evaluate it further to make well-informed decisions.</p>";
          } elseif ($status === 'Accepted') {

            echo "<p>Have been selected as a recipient of the scholarship. It will receive further instructions on how to claim the award, complete necessary paperwork, and fulfill any additional requirements.</p>";
          } elseif ($status === 'Rejected') {
            echo "<p>Unfortunately, Application was not chosen for the scholarship.</p>";
          } else {
            echo "<p>Unknown status: " . htmlspecialchars($status) . "</p>";
          }

          ?>
        </div>

        <div class="button-container">
          <button class="cancel-button" type="button" onclick="window.location.href='application_list.php'">Back</button>
        </div>


      </div>
    </form>

  </div>
  <script>
  </script>
</body>

</html>