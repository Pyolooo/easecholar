<?php
include '../include/connection.php';

if (!$dbConn) {
  die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['osaId']) && isset($_POST['status'])) {
  $osaId = $_POST['osaId'];
  $status = $_POST['status'];

  // Update the OSA user's account status in the database
  $sql = "UPDATE tbl_admin SET is_active = ? WHERE admin_id = ?";
  $stmt = mysqli_prepare($dbConn, $sql);

  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $status, $osaId);
    if (mysqli_stmt_execute($stmt)) {
      // Update was successful
      echo "success";
    } else {
      // Update failed
      echo "error";
    }
    mysqli_stmt_close($stmt);
  } else {
    // Statement preparation failed
    echo "error";
  }
} else {
  // Invalid request
  echo "error";
}

mysqli_close($dbConn);
?>
