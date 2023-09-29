<?php
include('connection.php');

$scholarship = "";
$details = "";
$requirements = array();
$benefits = array();
$scholarship_status = "";
$expire_date = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $scholarship = $_POST["scholarship"];
  $details = $_POST["details"];
  $requirements = explode("\n", $_POST["requirements"]); 
  $benefits = explode("\n", $_POST["benefits"]); 
  $scholarship_status = $_POST["scholarship_status"];
  $expire_date = $_POST["expire_date"];


   if (!empty($expire_date) && strtotime($expire_date) <= time()) {
    $error_message = "Expiration date must be in the future.";
  } else {
    if (!empty($scholarship) && !empty($details) && !empty($requirements) && !empty($benefits) && !empty($scholarship_status) && !empty($expire_date)) {
      $requirementsString = implode("\n", $requirements); 
      $benefitsString = implode("\n", $benefits); 


    $sql = "INSERT INTO `tbl_scholarship` (scholarship, details, requirements, benefits, scholarship_status, expire_date) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $dbConn->prepare($sql);
    $stmt->bind_param("ssssss", $scholarship, $details, $requirementsString, $benefitsString, $scholarship_status, $expire_date);
    $stmt->execute();
    $stmt->close();

      // Clear form inputs
      $scholarship = "";
      $details = "";
      $requirements = array();
      $benefits = array();
      $scholarship_status = "";
      $expire_date = "";

      $successMessage = 'You have created successfully';
      exit;
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
            if (isset($error_message)) {
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

            if (isset($successMessage)) {
                echo '<script>
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "' . $successMessage . '",
                    showConfirmButton: false,
                    timer: 1500
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.timer) {
                        window.location.href = "scholarship.php";
                    }
                });
                </script>';
            }
            
            ?>
    <form method="POST" action="#" class="form">
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
          <input type="date" name="expire_date">
        </div>
      </div>

      <button type="submit">Submit</button>
    </form>
  </section>
</body>

</html>