<?php

$host = "easecholar-do-user-15025878-0.c.db.ondigitalocean.com";
$port = 25060;
$username = "doadmin";
$password = "AVNS_I9q9-Dls3cwYbepb0qX";
$database = "defaultdb";
$sslmode = "REQUIRED";

$dbConn = mysqli_connect($host, $username, $password, $database, $port, null) or die('MySQL connect failed: ' . mysqli_connect_error());

?>