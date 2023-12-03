<?php

$host = "ease-cholar-server.mysql.database.azure.com";
$username = "httzpwmikk";
$password = "XTPPY2C81FS5328B$";
$database = "easecholar";
$sslmode = "require"; // Note: "require" instead of "REQUIRED"

$dbConn = mysqli_init();
mysqli_ssl_set($dbConn, NULL, NULL, NULL, NULL, NULL);
mysqli_real_connect($dbConn, $host, $username, $password, $database, 3306, NULL, MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT) or die('MySQL connect failed: ' . mysqli_connect_error());

?>
