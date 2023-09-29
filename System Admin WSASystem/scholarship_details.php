
<?php
include('connection.php');

if ($dbConn->connect_error) {
    die('Connection failed: ' . $dbConn->connect_errno);
}

if (isset($_GET['id'])) {
    $scholarshipId = $_GET['id'];
    $sql = "SELECT * FROM tbl_scholarship WHERE scholarship_id = $scholarshipId";
    $result = $dbConn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $requirements = explode("\n", $row['requirements']);
        $benefits = explode("\n", $row['benefits']);
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="scholarship_details.css">

            <title>Scholarship Details</title>
        </head>
       <?php include('header.php')?>
        <body>
           
        <div class="table-data">
            <p style="font-size:xx-large">Scholarship Details</p>
            <hr>
            <h2><?php echo $row['scholarship']; ?></h2>
            <h3>Requirements:</h3>
            <ul>
                <?php
                foreach ($requirements as $requirement) {
                    echo "<li>$requirement</li>";
                }
                ?>
            </ul>
            <h3>Benefits:</h3>
            <ul>
                <?php
                foreach ($benefits as $benefit) {
                    echo "<li>$benefit</li>";
                }
                ?>
            </ul>
            <a class="button" href="apply.php">Apply</a>
        </body>
        </html>
            </div>
        <?php
    } else {
        echo "No scholarship found with the specified ID.";
    }
} else {
    echo "No scholarship ID specified.";
}
?>
