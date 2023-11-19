<?php
include('../include/connection.php');

$scholarship = "";
$details = "";
$requirements = array();
$benefits = array();
$scholarship_status = "";
$expire_date = "";
$error_message = "";
$error_input = "";
$successMessage = ""; // Initialize success message to an empty string

// ...
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $scholarship = htmlspecialchars($_POST["scholarship"]);
  $details = htmlspecialchars($_POST["details"]);
  $requirements = explode("\n", htmlspecialchars($_POST["requirements"]));
  $benefits = explode("\n", htmlspecialchars($_POST["benefits"]));
  $scholarship_status = $_POST["scholarship_status"];
  $expire_date = $_POST["expire_date"];
  $application_form_table = $_POST["application_form_table"];

   // Handle image upload
$targetDirectory = $_SERVER['DOCUMENT_ROOT'] . '/file_uploads/';
$defaultImage = 'isulogo.png';
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));


if ($_FILES["image"]["size"] > 0) {
    if (!in_array($imageFileType, array("jpg", "jpeg", "png"))) {
        $error_message = "Sorry, only JPG, JPEG, and PNG files are allowed.";
        $uploadOk = 0;
    }

    if ($_FILES["image"]["size"] > 500000) {
        $error_message = "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        $error_message .= " The image was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetDirectory . basename($_FILES["image"]["name"]))) {
            $targetFile = $targetDirectory . basename($_FILES["image"]["name"]);
        } else {
            $error_message = "Sorry, there was an error uploading your file.";
        }
    }
} else {
    $targetFile = $targetDirectory . $defaultImage;
}


  date_default_timezone_set('Asia/Manila');

  $currentTimestamp = strtotime('now');
  $expireTimestamp = strtotime($expire_date);

  if (empty($expire_date)) {
    $error_message = "Expiration date is required.";
  } elseif ($expireTimestamp <= $currentTimestamp) {
    $error_message = "Expiration date must be in the future.";
  } else {
    $requiredFields = [$scholarship, $details, $requirements, $benefits, $scholarship_status, $expire_date];
    $fieldLabels = ["Scholarship", "Details", "Requirements", "Benefits", "Scholarship Status", "Deadline"];

    $isEmptyField = false;
    foreach ($requiredFields as $index => $field) {
      if (empty($field)) {
        $error_input = $fieldLabels[$index] . " is required.";
        $isEmptyField = true;
        break;
      }
    }


    if (!$isEmptyField) {
      $requirementsString = implode("\n", $requirements);
      $benefitsString = implode("\n", $benefits);

      $sql = "INSERT INTO `tbl_scholarship` (scholarship, details, requirements, benefits, scholarship_status, expire_date, application_form_table, scholarship_logo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
      $stmt = $dbConn->prepare($sql);
      $stmt->bind_param("ssssssss", $scholarship, $details, $requirementsString, $benefitsString, $scholarship_status, $expire_date, $application_form_table, $targetFile);

      if ($stmt->execute()) {
        // Database insertion successful
        $successMessage = 'You have created successfully';
      } else {
        // Database insertion failed
        $error_message = "Database error: " . $stmt->error;
      }

      $stmt->close();
    }
  }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Scholarship</title>
  <link rel="stylesheet" href="css/create_scholarship.css">
  <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
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
              title: "Empty Field",
              text: "Expiration date is required.",
              showConfirmButton: false,
              timer: 2000
          })
      </script>';
      } elseif (!empty($error_message) && strtotime($expire_date) <= time()) {

        // Show the error message for an invalid date
        echo '<script>
            Swal.fire({
                icon: "error",
                title: "Invalid Date",
                text: "' . $error_message . '",
                showConfirmButton: false,
                timer: 2000
            })
        </script>';
      } elseif (isset($error_input)) {
        // Show the error message related to the empty field
        echo '<script>
          Swal.fire({
              icon: "error",
              title: "Empty Field",
              text: "' . $error_input . '",
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

    <div class="selected-image-container">
                <div class="image-container">
                    <img id="selected-image" src="../user_profiles/isulogo.png" alt="Selected Image">
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
        <input class="scholarship-details" type="text" name="details" placeholder="Scholarship details" value="<?php echo $details; ?>" required>
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
          <option value="tbl_userapp">Annex 1 Form</option>
          <option value="tbl_scholarship_1_form">Government Application Form</option>
          <!-- Add options for different application forms (tables) -->
        </select>
        <button title="Preview form" type="button" id="previewButton">Preview</button>
      </div>

      <div class="date-container">
        <div class="input-class">
          <label>Scholarship Status:</label>
          <select class="form-option" name="scholarship_status" required>
            <option value="ongoing" selected>Ongoing</option>
            <option value="closed">Closed</option>
          </select>
        </div>

        <div class="input-class">
          <label>Deadline:</label>
          <input class="form-option" type="date" name="expire_date" value="<?php echo date('Y-m-d'); ?>" required>
        </div>


        <button title="Submit" type="submit">Submit</button>
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

    // Function to display the selected image and control label visibility
    function displaySelectedImage() {
            var input = document.getElementById('image-input');
            var selectedImage = document.getElementById('selected-image');
            var imageLabel = document.getElementById('image-label');

            input.addEventListener('change', function() {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        selectedImage.src = e.target.result;
                        selectedImage.style.display = ''; // Show the selected image
                        imageLabel.style.display = 'none'; // Hide the label
                    };

                    reader.readAsDataURL(input.files[0]);
                } else {
                    selectedImage.src = ""; // Clear the selected image if no file is selected
                    selectedImage.style.display = 'none'; // Hide the selected image
                    imageLabel.style.display = 'block'; // Show the label
                }
            });
        }

        // Call the function to display the selected image
        displaySelectedImage();

  </script>
</body>

</html>