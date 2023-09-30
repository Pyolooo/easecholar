<?php
include '../include/connection.php';
session_name("RegistrarSession");
session_start();
$registrar_id = $_SESSION['registrar_id'];

if (!isset($registrar_id)) {
   header('location: registrar_login.php');
   exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['application_id']) && isset($_POST['grade_status'])) {
        $application_id = $_POST['application_id'];
        $grade_status = $_POST['grade_status'];
        
        // Update grade_status in 'tbl_userapp'
        $query = "UPDATE `tbl_userapp` SET `grade_status` = '$grade_status' WHERE `application_id` = $application_id";
        $result = mysqli_query($dbConn, $query);
        
        if ($result) {
            echo "Grade Status updated successfully.";
        } else {
            echo "Error updating grade_status: " . mysqli_error($dbConn);
        }
    } else {
        echo "Invalid request.";
    }
} else {
    echo "Invalid request.";
}
