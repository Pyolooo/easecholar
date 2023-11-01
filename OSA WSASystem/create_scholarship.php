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

  $sql = "INSERT INTO `tbl_scholarship` (scholarship, details, requirements, benefits, scholarship_status, expire_date, application_form_table) VALUES (?, ?, ?, ?, ?, ?, ?)";
  $stmt = $dbConn->prepare($sql);
  $stmt->bind_param("sssssss", $scholarship, $details, $requirementsString, $benefitsString, $scholarship_status, $expire_date, $application_form_table);

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
    <form method="POST" action="" class="form">
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

      <div class="date-container">
        <div class="input-class">
          <label>Scholarship Status:</label>
          <select name="scholarship_status" required>
            <option value="ongoing" selected>Ongoing</option>
            <option value="closed">Closed</option>
          </select>
        </div>

        <div class="input-class">
  <label>Deadline:</label>
  <input type="date" name="expire_date" value="<?php echo date('Y-m-d'); ?>" required>
</div>

<div class="input-box">
    <label>Choose Application Form:</label>
    <select name="application_form_table" required>
        <option value="tbl_scholarship_1_form">Application Form 1</option>
        <option value="tbl_scholarship_2_form">Application Form 2</option>
        <!-- Add options for different application forms (tables) -->
    </select>
</div>

        <button type="button" id="previewButton">Preview</button>

        <button type="submit">Submit</button>
      </div>

      
    </form>
  </section>

  <script>
    document.getElementById("previewButton").addEventListener("click", function() {
        // Get the selected application form table name
        var selectedFormTable = document.querySelector("select[name=application_form_table]").value;

        // Redirect to a preview page with the selected form table as a parameter
        window.location.href = "preview_form.php?form=" + selectedFormTable;
    });
</script>
</body>

</html>