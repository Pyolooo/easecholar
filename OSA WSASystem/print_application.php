<?php
include '../include/connection.php';
// Check if the 'id' and 'user_id' parameters are set in the URL
if (isset($_GET['id']) && isset($_GET['user_id'])) {
  // Retrieve the values
  $application_id = $_GET['id'];
  $user_id = $_GET['user_id'];

  // Proceed with your database query
  $query = "SELECT * FROM `tbl_userapp` WHERE `application_id` = ? AND `user_id` = ? ";
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
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Printable Form</title>
  <link rel="stylesheet" href="css/printable.css">
</head>
<style>
  .print-button {
    cursor: pointer;
    margin: 20px;
    padding: 4px 30px;
    color: white;
    border: none;
    font-weight: 600;
    font-size: 15px;
    background-color: #4070f4;
    border-radius: 2px;
  }

  @media print {
    button {
      display: none;
    }
  }
</style>

<body>
    <button class="print-button" onclick='window.print()'>Print</button>
  <form action="" method="POST" enctype="multipart/form-data">
    <div class="container">
      <div class="top-section">
        <div class="head">
          <div class="img"><img src="../user_profiles/<?php echo $applicationData['image']; ?>" alt="Profile"></div>
        </div>
      </div>

  </form>

  <div class="form-first">
    <form action="view_application.php?id=<?php echo $application_id; ?>&user_id=<?php echo $user_id; ?>" method="POST" enctype="multipart/form-data">
      <h4>PERSONAL INFORMATION</h4>
      <br>
      <div class="details personal">
        <div class="fields">
          <div class="input-field">
            <label for="last_name">Last Name:</label>
            <span><?php echo $applicationData['last_name']; ?></span>
          </div>
          <div class="input-field">
            <label for="first_name">First Name:</label>
            <span><?php echo $applicationData['first_name']; ?></span>
          </div>
          <div class="input-field">
            <label for="middle_name">Middle Name:</label>
            <span><?php echo $applicationData['middle_name']; ?></span>
          </div>
        </div>
        <div class="fields">
          <div class="input-field">
            <label>Date of Birth:</label>
            <span><?php echo $applicationData['dob']; ?></span>
          </div>
          <div class="input-field">
            <label>Place of Birth:</label>
            <span><?php echo $applicationData['pob']; ?></span>
          </div>
        </div>

        <div class="fields">
          <div class="input-field">
            <label>Email:</label>
            <span><?php echo $applicationData['email']; ?></span>
          </div>
          <div class="input-field">
            <label>Mobile Number:</label>
            <span><?php echo $applicationData['mobile_num']; ?></span>
          </div>
        </div>

      </div>
      <div class="fields">
        <div class="input-field">
          <label>Citizenship</label>
          <span><?php echo $applicationData['citizenship']; ?></span>
        </div>
        <div class="input-field">
          <label>Sex:</label>
          <span><?php echo $applicationData['gender']; ?></span>
        </div>
      </div>

      <div class="fields">
        <div class="input-field">
          <label>School ID Number:</label>
          <span><?php echo $applicationData['id_number']; ?></span>
        </div>
        <div class="input-field">
          <label>Course:</label>
          <span><?php echo $applicationData['course']; ?></span>
        </div>
        <div class="input-field">
          <label>Year level:</label>
          <span><?php echo $applicationData['year_lvl']; ?></span>
        </div>
      </div>

      <div class="form-first">
        <h4>PERMANENT ADDRESS</h4>
        <br>
        <div class="fields">
          <div class="input-field">
            <label>Street & Barangay:</label>
            <span><?php echo $applicationData['barangay']; ?></span>
          </div>
          <div class="input-field">
            <label>Town City/Municipality:</label>
            <span><?php echo $applicationData['town_city']; ?></span>
          </div>
        </div>
        <div class="fields">
          <div class="input-field">
            <label for="middle_name">Province:</label>
            <span> <?php echo $applicationData['province']; ?></span>
          </div>
          <div class="input-field">
            <label for="middle_name">Zip Code:</label>
            <span><?php echo $applicationData['zip_code']; ?></span>
          </div>
        </div>

      </div>
  </div>
  </div>

  <br>
  <h4>FAMILY BACKGROUND:</h4>
  <br>
  <div class="details family">
    <div class="fields-info">
      <div class="form">
        <div class="input-field">
          <span class="title"> FATHER </span><br>
          <hr><br>
          <div class="input-field">
            <label>Name:</label>
            <span><?php echo $applicationData['father_name']; ?></span><br>
          </div>
          <div class="input-field">
            <label>Address:</label>
            <span><?php echo $applicationData['father_address']; ?></span><br>
          </div>
          <div class="input-field">
            <label>Occupation:</label>
            <span><?php echo $applicationData['father_work']; ?></span><br>
          </div>
        </div>
      </div>

      <div class="form">
        <div class="input-field">
          <span class="title"> MOTHER </span><br>
          <hr><br>
          <div class="input-field">
            <label>Name:</label>
            <span><?php echo $applicationData['mother_name']; ?></span><br>
          </div>
          <div class="input-field">
            <label>Address:</label>
            <span><?php echo $applicationData['mother_address']; ?></span><br>
          </div>
          <div class="input-field">
            <label>Occupation:</label>
            <span><?php echo $applicationData['mother_work']; ?></span><br>
          </div>
        </div>
      </div>
    </div>
  </div>
  <hr><br>
  <div class="select-input-field">
    <div class="input-field">
      <label>Total Gross Income</label>
      <span><?php echo $applicationData['gross_income']; ?></span><br>
    </div>
    <div class="input-field">
      <label>No. of Siblings in the family</label>
      <span><?php echo $applicationData['num_siblings']; ?></span><br>
    </div>
  </div>
  </div>
  <ul>
    <?php
    // Iterate through the uploaded files
    $fileNames = explode(',', $applicationData['file'] . ',' . $applicationData['attachments']);
    foreach ($fileNames as $fileName) {
      $filePath = '../file_uploads/' . $fileName;

      // Check the file extension to determine if it's an image or PDF
      $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

      echo '<li>';
      if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
        // Display image
        echo "<img src='{$filePath}' alt='Uploaded Image' style='max-width: 100%;'>";
      } elseif ($fileExtension === 'pdf') {
        // Display PDF using an iframe
        echo "<iframe src='{$filePath}' width='100%' height='1270px'></iframe>";
      }
      echo '</li>';
    }
    ?>
  </ul>


  </div>


  </div>
  </div>
</body>

</html>