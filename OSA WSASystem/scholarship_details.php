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
        $details = $row['details'];
        $requirements = explode("\n", $row['requirements']);
        $benefits = explode("\n", $row['benefits']);
?>


        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="css/scholarship_details.css">

            <title>Scholarship Details</title>
        </head>
        <?php include('header.php') ?>

        <body>

            <div class="container">
                <p style="font-size:xx-large">Scholarship Details</p>
                <hr>
                <div class="sholarship-name">
                <h2><?php echo $row['scholarship']; ?></h2>
                </div>

                <div class="scholarship-details">
                <p class='details'><?php echo $row['details']; ?></p>
                </div>

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
            <div class="faq-container">
                <div class="how-to-apply">How to apply <?php echo $row['scholarship']; ?> </div>
            </div>
                <a class="apply-button" href="apply.php" style="pointer-events: none">Apply</a>
            </div>
        </body>

        </html>
<?php
    } else {
        echo "No scholarship found with the specified ID.";
    }
} else {
    echo "No scholarship ID specified.";
}
?>