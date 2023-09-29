<?php
include 'connection.php';
session_name("OsaSession");
session_start();
$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location: osa_login.php');
   exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['application_id']) && isset($_POST['status'])) {
        $application_id = $_POST['application_id'];
        $status = $_POST['status'];
        
        // Update status in 'tbl_userapp'
        $query = "UPDATE `tbl_userapp` SET `status` = '$status' WHERE `application_id` = $application_id";
        $result = mysqli_query($dbConn, $query);
        
        if ($result) {
            echo "Status updated successfully.";
        } else {
            echo "Error updating status: " . mysqli_error($dbConn);
        }
    } else {
        echo "Invalid request.";
    }
} else {
    echo "Invalid request.";
}
?>
