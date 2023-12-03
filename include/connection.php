<?php

$host = "ease-cholar-server.mysql.database.azure.com";
$username = "httzpwmikk";
$password = "XTPPY2C81FS5328B$";
$database = "easecholar";

$dbConn = mysqli_init();
mysqli_real_connect($dbConn, $host, $username, $password, $database, 3306, NULL, 0) or die('MySQL connect failed: ' . mysqli_connect_error());

?>
