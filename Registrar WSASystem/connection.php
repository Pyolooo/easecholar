<?php

$dbConn = mysqli_connect ("localhost", "root", "") or die ('MySQL connect failed. ' );
mysqli_select_db($dbConn, "easecholar") or die('Cannot select database. '  );

?>
