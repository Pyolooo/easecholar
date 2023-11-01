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
          expire_date = ?
          WHERE scholarship_id = ?";

    $stmt = $dbConn->prepare($sql);
    $stmt->bind_param(
      "ssssssi",
      $scholarship,
      $details,
      $requirements,
      $benefits,
      $scholarshipStatus,
      $expireDate,
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
    $details = $row['details'];
    $requirements = explode("\n", $row['requirements']);
    $benefits = explode("\n", $row['benefits']);
    $scholarshipStatus = $row['scholarship_status'];
    $expireDate = $row['expire_date'];
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
    <form method="POST" action="" class="form">
      <input type="hidden" name="scholarship_id" value="<?php echo $scholarshipId; ?>">
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

</body>

</html>