<?php
include '../include/connection.php';// Replace with your database connection script

// Create an array to store the qualified applications
$qualifiedApplications = array();

// Query to select applications with status 'Qualified'
$query = "SELECT * FROM tbl_userapp WHERE status = 'Qualified'";

$result = mysqli_query($dbConn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($dbConn));
}

// Fetch the results and add them to the $qualifiedApplications array
while ($row = mysqli_fetch_assoc($result)) {
    $qualifiedApplications[] = $row;
}

// Close the database connection
mysqli_close($dbConn);

// Return the qualified applications data as JSON
echo json_encode($qualifiedApplications);
?>
