<?php
include 'connection.php';

if (!$dbConn) {
  die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['registrarId']) && isset($_POST['status'])) {
  $registrarId = $_POST['registrarId'];
  $status = $_POST['status'];

  // Update the registrar user's account status in the database
  $sql = "UPDATE tbl_registrar SET is_active = ? WHERE registrar_id = ?";
  $stmt = mysqli_prepare($dbConn, $sql);

  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $status, $registrarId);
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
