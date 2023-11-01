<?php
include '../include/connection.php';
session_name("AdminSession");
session_start();
$super_admin_id = $_SESSION['super_admin_id'];

if (!isset($super_admin_id)) {
    header('location: admin_login.php');
    exit();
}

if (isset($_GET['logout'])) {
    unset($super_admin_id);
    session_destroy();
    header('location: admin_login.php');
    exit();
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
            <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
            <link rel="stylesheet" href="https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css">
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.all.min.js"></script>
            <link rel="stylesheet" href="css/scholarship_details.css">

            <title>Scholarship Details</title>
        </head>
        <?php include('../include/header.php') ?>

        <body>
            <div class="table-data">
                <div class="label-container">
                <h1 class="scholarship-title"><?php echo $row['scholarship']; ?></h1>
                </div>


                <hr>
                <div class="scholarship-details"> <?php echo $row['details']; ?></div>
                <div class="details-container">
                    <h4 class="details-label">Requirements:</h4>

                    <ul>
                        <?php
                        foreach ($requirements as $requirement) {
                            echo "<li>$requirement</li>";
                        }
                        ?>
                    </ul>
                </div>
                <div class="details-container">
                    <h4 class="details-label">Benefits:</h4>

                    <ul>
                        <?php
                        foreach ($benefits as $benefit) {
                            echo "<li>$benefit</li>";
                        }
                        ?>
                    </ul>
                </div>

                <div class="faq-content">
                    <label class="how-to-apply">How to apply for the Scholarship? </label>
                    <p class="guidelines">All applicants should fill up the application form. Provide a clear information and details. Upon submitting the Application Form wait for the OSA or committee to process your application.</p>
                </div>

                <div class="faq-content">
                    <label class="how-to-apply">How to know the status of your application? </label>
                    <p class="guidelines">To check the status of your application, log in to your account and navigate to the '<a class="aplication-status">Application Status</a>' section, where you can view whether your application is in one of the following states: Pending, In Review, Qualified, Accepted, or Rejected. Click <span class="status-details" onclick="showStatusInfo()">Status Details</span> for more information.</p>
                </div>

                <div id="statusInfoModal" class="modal">
                    <div class="modal-content">
                        <h2>Status Information</h2>
                        <div class="status-row">
                            <div class="status-label status-pending">Pending</div>
                            <div class="status-description">This status indicates that your application has been received but has not yet been reviewed or processed. It's awaiting initial assessment.</div>
                        </div>
                        <div class="status-row">
                            <div class="status-label status-inreview">In Review</div>
                            <div class="status-description">Your application is actively being evaluated by the scholarship committee or administrators. They are assessing your eligibility and qualifications.</div>
                        </div>
                        <div class="status-row">
                            <div class="status-label status-qualified">Qualified</div>
                            <div class="status-description">If your application is marked as "Qualified," it suggests that you meet the eligibility criteria and have advanced to the next stage of consideration.</div>
                        </div>
                        <div class="status-row">
                            <div class="status-label status-accepted">Accepted</div>
                            <div class="status-description">Congratulations, if your application status is "Accepted," it means you have been selected as a recipient of the scholarship. You may receive further instructions on how to claim the award.</div>
                        </div>
                        <div class="status-row">
                            <div class="status-label status-rejected">Rejected</div>
                            <div class="status-description">Unfortunately, this status means that your application was not chosen for the scholarship. You may receive feedback on why your application was not successful.</div>
                        </div>
                    </div>
                </div>
            </div>

    <?php
    } else {
        echo "No scholarship found with the specified ID.";
    }
} else {
    echo "Invalid request or not logged in.";
}
    ?>
    <script>
        // JavaScript function to show the status information modal
        function showStatusInfo() {
            var statusInfoModal = document.getElementById('statusInfoModal');
            statusInfoModal.style.display = 'block';

            // Close the modal if the user clicks outside of it
            window.onclick = function(event) {
                if (event.target == statusInfoModal) {
                    statusInfoModal.style.display = 'none';
                }
            };
        }
    </script>
        </body>

        </html>