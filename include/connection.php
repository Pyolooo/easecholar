<?php
$serverName = "ease-cholar-server.mysql.database.azure.com";
$databaseName = "httzpwmikk";
$username = "easecholar";
$password = "XTPPY2C81FS5328B$";

// Check if the mysqli extension is available
if (!function_exists('mysqli_init')) {
    die('mysqli extension is not available. Please enable it in your PHP configuration.');
}

// Construct the connection string with SSL options
$connectionString = "Server=$serverName;Database=$databaseName;User Id=$username;Password=$password;Encrypt=true;TrustServerCertificate=false";

// Create a database connection with the mysqli_init function
$dbConn = mysqli_init();
if (!$dbConn) {
    die("mysqli_init failed");
}

// Connect to the database using mysqli_real_connect with SSL
if (!$dbConn->real_connect($serverName, $username, $password, $databaseName, 3306, NULL, MYSQLI_CLIENT_SSL)) {
    die("Connect Error: " . mysqli_connect_error());
}

// Now $conn is your mysqli connection object
?>
