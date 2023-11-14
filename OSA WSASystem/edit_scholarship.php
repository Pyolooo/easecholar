<?php
include '../include/connection.php';
session_name("OsaSession");
session_start();
$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:osa_login.php');
};

if (isset($_GET['logout'])) {
    unset($admin_id);
    session_destroy();
    header('location:osa_login.php');
}

$error_message = "";
$successMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $scholarshipId = $_POST['scholarship_id'];
  $scholarship = $_POST['scholarship'];
  $details = $_POST['details'];
  $requirements = $_POST['requirements'];
  $benefits = $_POST['benefits'];
  $scholarshipStatus = $_POST['scholarship_status'];
  $expireDate = $_POST['expire_date'];
  $applicationForm = $_POST['application_form_table'];

  // Handle image update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if a new image is selected
    if ($_FILES['image']['name'] != '') {
      $targetDir = $_SERVER['DOCUMENT_ROOT'] . '/EASE-CHOLAR/file_uploads/';
        
      // Generate a unique and secure filename
      $originalFileName = basename($_FILES["image"]["name"]);
      $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
      $uniqueFileName = uniqid('uploaded_image_') . '.' . $extension;
      $targetFile = $targetDir . $uniqueFileName;

      $uploadOk = 1;
      $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

      // Check if the file is an actual image
      $check = getimagesize($_FILES["image"]["tmp_name"]);
      if ($check === false) {
          $error_message = "File is not an image.";
          $uploadOk = 0;
      }
  
      // Check if file already exists
      if (file_exists($targetFile)) {
          $error_message = "Sorry, the file already exists.";
          $uploadOk = 0;
      }
  
      // Check file size
      if ($_FILES["image"]["size"] > 500000) {
          $error_message = "Sorry, your file is too large.";
          $uploadOk = 0;
      }
  
      // Allow certain file formats
      $allowedFormats = ["jpg", "jpeg", "png"];
      if (!in_array($imageFileType, $allowedFormats)) {
          $error_message = "Sorry, only JPG, JPEG, and PNG files are allowed.";
          $uploadOk = 0;
      }
  
      // Check if $uploadOk is set to 0 by an error
      if ($uploadOk == 0) {
          $error_message = "Sorry, your file was not uploaded.";
      } else {
          // If everything is ok, try to upload file
          if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
// Update the scholarship_logo in the database
$sql = "UPDATE tbl_scholarship SET scholarship_logo = ? WHERE scholarship_id = ?";
$stmt = $dbConn->prepare($sql);
$stmt->bind_param("si", $targetFile, $scholarshipId);

if ($stmt->execute()) {
    $successMessage = 'Scholarship information updated successfully';
} else {
    $error_message = "Error updating scholarship information: " . $stmt->error;
}

$stmt->close();

          } else {
              $error_message = "Sorry, there was an error uploading your file.";
          }
      }
  }
}  

  // Check if the selected date is in the future
  if (strtotime($expireDate) <= time()) {
    $error_message = "The expiration date should be in the future.";
  } else {
    // Perform the database update
    $sql = "UPDATE tbl_scholarship SET
          scholarship = ?,
          details = ?,
          requirements = ?,
          benefits = ?,
          scholarship_status = ?,
          expire_date = ?,
          application_form_table = ?
          WHERE scholarship_id = ?";

    $stmt = $dbConn->prepare($sql);
    $stmt->bind_param(
      "sssssssi",
      $scholarship,
      $details,
      $requirements,
      $benefits,
      $scholarshipStatus,
      $expireDate,
      $applicationForm,
      $scholarshipId
    );

    if ($stmt->execute()) {
      $successMessage = 'Scholarship information updated successfully';
    } else {
      $error_message = "Error updating scholarship information: " . $stmt->error;
    }

    $stmt->close();
  }
}


// Fetch current scholarship information from the database
if (isset($_GET['id'])) {
  $scholarshipId = $_GET['id'];
  $sql = "SELECT * FROM tbl_scholarship WHERE scholarship_id = ?";
  $stmt = $dbConn->prepare($sql);
  $stmt->bind_param("i", $scholarshipId);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $scholarship = $row['scholarship'];
      $targetFile = $row['scholarship_logo'];
      $details = $row['details'];
      $requirements = explode("\n", $row['requirements']);
      $benefits = explode("\n", $row['benefits']);
      $scholarshipStatus = $row['scholarship_status'];
      $expireDate = $row['expire_date'];
      $applicationForm = $row['application_form_table'];
  } else {
      die('Scholarship details not found');
  }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Update Scholarship</title>
  <link rel="stylesheet" href="css/edit_scholarship.css">
  <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
  <section class="container">
    <div class="header">Add Scholarship</div>
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      if (empty($expire_date)) {
        // Show the error message for an empty expiration date
        echo '<script>
          Swal.fire({
              icon: "error",
              title: "Invalid Date",
              text: "' . $error_message . '",
              showConfirmButton: false,
              timer: 2000
          })
      </script>';
      }
    }
    if (!empty($successMessage)) {
      echo '<script>
            Swal.fire({
                position: "center",
                icon: "success",
                title: "' . $successMessage . '",
                showConfirmButton: false,
                timer: 1500
            }).then((result) => {
                if (result.dismiss === Swal.DismissReason.timer) {
                    window.location.href = "scholarships.php";
                }
            });
            </script>';
    }
    ?>
        <form method="POST" action="" class="form" enctype="multipart/form-data">
      <input type="hidden" name="scholarship_id" value="<?php echo $scholarshipId; ?>">

      <div class="selected-image-container">
      <div class="image-container">
      <div class="image-container">
      <img id="selected-image" src='/EASE-CHOLAR/file_uploads/<?php echo basename($targetFile); ?>' alt="Scholarship Logo">

</div>

</div>

                <div class="round">
                    <input class="input-style" id="image-input" type="file" name="image"  accept="image/jpg, image/jpeg, image/png">
                    <i class='bx bxs-camera'></i>
                </div>
            </div>

      <div class="input-box">
        <label>Scholarship</label>
        <input type="text" name="scholarship" placeholder="Scholarship name" value="<?php echo $scholarship; ?>" required>
      </div>

      <div class="input-box">
        <label>Details</label>
        <input type="text" name="details" placeholder="Scholarship details" value="<?php echo $details; ?>" required>
      </div>
      <div class="input-box">
        <label>Requirements</label>
        <textarea name="requirements" placeholder="Requirements" required><?php echo implode("\n", $requirements); ?></textarea>
      </div>
      <div class="input-box">
        <label>Benefits</label>
        <textarea name="benefits" placeholder="Benefits" required><?php echo implode("\n", $benefits); ?></textarea>
      </div>

      <div class="input-box">
        <label>Choose Application Form:</label>
        <select class="form-option" name="application_form_table" required>
          <option value="tbl_userapp" <?php echo ($applicationForm === 'tbl_userapp') ? 'selected' : ''; ?>>Annex 1 Form</option>
          <option value="tbl_scholarship_1_form" <?php echo ($applicationForm === 'tbl_scholarship_1_form') ? 'selected' : ''; ?>>Government Application Form</option>
        </select>
        <button title="Preview form" type="button" id="previewButton">Preview</button>
      </div>


      <div class="date-container">
        <div class="input-class">
          <label>Scholarship Status:</label>
          <select name="scholarship_status" required>
            <option value="ongoing" <?php echo ($scholarshipStatus === 'ongoing') ? 'selected' : ''; ?>>Ongoing</option>
            <option value="closed" <?php echo ($scholarshipStatus === 'closed') ? 'selected' : ''; ?>>Closed</option>
          </select>

        </div>

        <div class="input-class">
          <label>Deadline:</label>
          <input type="date" name="expire_date" value="<?php echo $expireDate; ?>" required>
        </div>
      </div>

      <div class="button-container">
      <button class="cancel-button" type="button" onclick="window.location.href='scholarships.php'">Cancel</button>
        <button type="submit">Submit</button>
      </div>


    </form>
  </section>

  <script>

document.getElementById("previewButton").addEventListener("click", function() {
      var selectedFormTable = document.querySelector("select[name=application_form_table]").value;

      var previewPages = {
        "tbl_userapp": "preview_application1.php",
        "tbl_scholarship_1_form": "preview_application2.php",
        // Add more form tables and their respective preview pages as needed
      };

      if (previewPages.hasOwnProperty(selectedFormTable)) {
        window.location.href = previewPages[selectedFormTable];
      } else {
        alert("Selected form table is not recognized for preview.");
      }
    });

     document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        let isFormDirty = false;

        // Function to mark the form as dirty when a form field is changed
        function handleFormChange() {
            isFormDirty = true;
        }

        // Add an event listener to each form field to detect changes
        const formFields = form.querySelectorAll('input, select');
        formFields.forEach(field => {
            field.addEventListener('input', handleFormChange);
        });

        // Add a submit event listener to the form to prevent submission if the form is not dirty
        form.addEventListener('submit', function (event) {
            if (!isFormDirty) {
                // Form is not dirty; prevent submission
                event.preventDefault();
                Swal.fire({
                    icon: 'info',
                    title: 'No changes made',
                    text: 'Please update some data.',
                    showConfirmButton: false,
                    timer: 2000 // Close the alert after 2 seconds
                });
            }
        });
    });
  </script>

</body>

</html>