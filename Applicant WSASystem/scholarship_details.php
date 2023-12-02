<?php
session_name("ApplicantSession");
session_start();
include('../include/connection.php');

if ($dbConn->connect_error) {
    die('Connection failed: ' . $dbConn->connect_errno);
}

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $scholarshipId = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    $sqlCheckApplication = "SELECT user_id, scholarship_id FROM tbl_userapp WHERE user_id = ? AND scholarship_id = ? UNION SELECT user_id, scholarship_id FROM tbl_scholarship_1_form WHERE user_id = ? AND scholarship_id = ?";
    $stmtCheckApplication = $dbConn->prepare($sqlCheckApplication);
    $stmtCheckApplication->bind_param("iiii", $user_id, $scholarshipId, $user_id, $scholarshipId);
    $stmtCheckApplication->execute();
    $resultCheckApplication = $stmtCheckApplication->get_result();



    if ($stmtCheckApplication->error) {
        die('Error in SQL query: ' . $stmtCheckApplication->error);
    }

    if ($resultCheckApplication->num_rows > 0) {
        $applicationStatus = "You have already applied for this scholarship.";
        $showApplyButton = false;
    } else {
        $sqlCheckScholarshipStatus = "SELECT scholarship_status FROM tbl_scholarship WHERE scholarship_id = ?";
        $stmtCheckScholarshipStatus = $dbConn->prepare($sqlCheckScholarshipStatus);
        $stmtCheckScholarshipStatus->bind_param("i", $scholarshipId);
        $stmtCheckScholarshipStatus->execute();
        $resultCheckScholarshipStatus = $stmtCheckScholarshipStatus->get_result();

        if ($stmtCheckScholarshipStatus->error) {
            die('Error in SQL query: ' . $stmtCheckScholarshipStatus->error);
        }

        if ($resultCheckScholarshipStatus->num_rows > 0) {
            $row = $resultCheckScholarshipStatus->fetch_assoc();
            $scholarshipStatus = $row['scholarship_status'];

            if ($scholarshipStatus === 'Closed') {
                $applicationStatus = "This scholarship is closed and no longer accepting applications.";
                $showApplyButton = false;
            } else {
                $applicationStatus = "";
                $showApplyButton = true;
            }
        } else {
            $applicationStatus = "Scholarship status not found.";
            $showApplyButton = false;
        }
    }


    $sql = "SELECT * FROM tbl_scholarship WHERE scholarship_id = $scholarshipId";
    $result = $dbConn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $details = $row['details'];
        $scholarship_logo = $row['scholarship_logo'];
        $requirements = explode("\n", $row['requirements']);
        $benefits = explode("\n", $row['benefits']);
        $selectedFormTable = $row['application_form_table'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/scholarship_details.css">

    <title>Scholarship Details</title>
</head>
<?php include('../include/header.php') ?>

<body>
    <div class="table-data">
        <div class="scholarship-container">
            <img class='scholarship-logo' src='../file_uploads/<?php echo basename($scholarship_logo); ?>' alt="Scholarship Logo">
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
            <p class="guidelines">To check the status of your application, log in to your account and navigate to the '<a class="aplication-status" href="application_status.php">Application Status</a>' section, where you can view whether your application is in one of the following states: Pending, In Review, Qualified, Accepted, or Rejected. Click <span class="status-details" onclick="showStatusInfo()">Status Details</span> for more information.</p>
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



        <p class="alert-message"><?php echo $applicationStatus; ?></p>
        <?php if ($showApplyButton) { ?>
            <button class="button" onclick="redirectToApplicationForm()">Apply</button>
        <?php } ?>
    </div>

    <script>
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

        function redirectToApplicationForm() {
            var applyPage = 'apply.php';

            if ('<?php echo $selectedFormTable; ?>' === 'tbl_scholarship_1_form') {
                applyPage = 'apply1.php';
            }

            location.href = applyPage + '?id=<?php echo $scholarshipId; ?>&user_id=<?php echo $_SESSION['user_id']; ?>';
        }
    </script>
</body>

</html>