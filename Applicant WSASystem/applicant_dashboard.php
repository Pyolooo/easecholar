<?php
include '../include/connection.php';
session_name("ApplicantSession");
session_start();
$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:applicant_login.php');
}

if (isset($_GET['logout'])) {
    unset($user_id);
    session_destroy();
    header('location:applicant_login.php');
    exit();
}

$user_id = $_SESSION['user_id'];


$select = mysqli_query($dbConn, "
SELECT application_id, scholarship_name, date_submitted, status FROM tbl_userapp WHERE user_id = '$user_id'
    
    UNION
    
    SELECT application_id, scholarship_name, date_submitted, status FROM tbl_scholarship_1_form WHERE user_id = '$user_id'
") or die(mysqli_error($dbConn));



$sqlAdmin = "SELECT username FROM tbl_admin WHERE admin_id = ?";
$stmtAdmin = $dbConn->prepare($sqlAdmin);
$stmtAdmin->bind_param("s", $_SESSION['admin_id']);
$stmtAdmin->execute();
$resultAdmin = $stmtAdmin->get_result();

if ($resultAdmin->num_rows > 0) {
    $rowAdmin = $resultAdmin->fetch_assoc();
    $_SESSION['admin_username'] = $rowAdmin['username'];
} else {
    $_SESSION['admin_username'] = 'Osa';
}


$applicationQuery = "SELECT application_id FROM tbl_userapp WHERE user_id = ?";
$stmtApplication = mysqli_prepare($dbConn, $applicationQuery);
mysqli_stmt_bind_param($stmtApplication, "i", $user_id);
mysqli_stmt_execute($stmtApplication);
$applicationResult = mysqli_stmt_get_result($stmtApplication);


if ($applicationResult->num_rows > 0) {
    $applicationData = mysqli_fetch_assoc($applicationResult);
    $application_id = $applicationData['application_id'];
} else {
}

function markMessageAsRead($dbConn, $messageId, $adminId)
{
    $updateQuery = "UPDATE tbl_user_messages SET read_status = 'read' WHERE message_id = ? AND admin_id = ?";
    $stmtUpdate = mysqli_prepare($dbConn, $updateQuery);
    mysqli_stmt_bind_param($stmtUpdate, "ii", $messageId, $adminId);
    mysqli_stmt_execute($stmtUpdate);
}

if (isset($_GET['read_message']) && is_numeric($_GET['read_message'])) {
    $messageId = intval($_GET['read_message']);
    $adminId = null;

    if (isset($_GET['admin_id']) && is_numeric($_GET['admin_id'])) {
        $adminId = intval($_GET['admin_id']);
    }

    markMessageAsRead($dbConn, $messageId, $adminId);
}

$userInfoQuery = "SELECT full_name FROM tbl_user WHERE user_id = ?";
$stmtUserInfo = mysqli_prepare($dbConn, $userInfoQuery);
mysqli_stmt_bind_param($stmtUserInfo, "i", $user_id);
mysqli_stmt_execute($stmtUserInfo);
$resultUserInfo = mysqli_stmt_get_result($stmtUserInfo);

if ($rowUserInfo = mysqli_fetch_assoc($resultUserInfo)) {
    $full_name = $rowUserInfo['full_name'];
} else {
    $full_name = "Unknown User";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.all.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">


    <!-- My CSS -->
    <link rel="stylesheet" href="css/style.css">

    <title>ApplicantModule</title>
</head>

<body>


    <!-- SIDEBAR -->
    <section id="sidebar">
        <div class="brand">
            <img src="../img/isulogo.png">
            <span class="name-hub"><?= $full_name; ?></span>
        </div>
        <ul class="side-menu top">
            <li class="active">
                <a href="#">
                    <i class='bx bxs-dashboard'></i>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="scholarships.php">
                    <i class='bx bxs-shopping-bag-alt'></i>
                    <span class="text">Scholarship</span>
                </a>
            </li>
            <li>
                <a href="application_status.php">
                    <i class='bx bxs-file'></i>
                    <span class="text">Application</span>
                </a>
            </li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="#" class="logout">
                    <i class='bx bxs-log-out-circle'></i>
                    <span class="text" onclick="confirmLogout()">Logout</span>
                </a>
            </li>
        </ul>
    </section>
    <!-- SIDEBAR -->



    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <!-- NAVBAR -->
        <nav>
            <div class="menu">
                <i class='bx bx-menu'></i>
                <span class="school-name">EASE-CHOLAR</span>
            </div>
            <div class="right-section">
                <div class="notif">
                    <div class="notification">
                        <?php
                        $user_id = $_SESSION['user_id'];

                        $totalNotificationCount = 0;

                        $applicationIdsQuery = "(SELECT application_id
                            FROM tbl_userapp
                            WHERE user_id = ?)
                            UNION
                            (SELECT application_id
                            FROM tbl_scholarship_1_form
                            WHERE user_id = ?)";

                        $stmtApplicationIds = mysqli_prepare($dbConn, $applicationIdsQuery);
                        mysqli_stmt_bind_param($stmtApplicationIds, "ii", $user_id, $user_id);
                        mysqli_stmt_execute($stmtApplicationIds);
                        $applicationIdsResult = mysqli_stmt_get_result($stmtApplicationIds);

                        while ($applicationData = mysqli_fetch_assoc($applicationIdsResult)) {
                            $application_id = $applicationData['application_id'];

                            $userMessageCountQuery = "SELECT COUNT(*) AS count FROM tbl_user_messages WHERE application_id = ? AND user_id = ? AND read_status = 'unread'";

                            $stmtUserMessageCount = mysqli_prepare($dbConn, $userMessageCountQuery);
                            mysqli_stmt_bind_param($stmtUserMessageCount, "ii", $application_id, $user_id);
                            mysqli_stmt_execute($stmtUserMessageCount);
                            $userMessageCountData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtUserMessageCount));
                            $userMessageCount = $userMessageCountData['count'];

                            $totalNotificationCount += $userMessageCount;
                        }

                        if ($totalNotificationCount > 0) {
                            echo '<i id="bellIcon" class="bx bxs-bell"></i>';
                            echo '<span class="num">' . $totalNotificationCount . '</span>';
                        } else {
                            echo '<i id="bellIcon" class="bx bxs-bell"></i>';
                            echo '<span class="num" style="display: none;">' . $totalNotificationCount . '</span>';
                        }
                        ?>
                    </div>




                    <?php
                    function formatSentAt($dbSentAt)
                    {
                        $dateTimeObject = new DateTime($dbSentAt);
                        return $dateTimeObject->format('Y-m-d, g:i A');
                    }
                    ?>

                    <div class="dropdown">
                        <div class="notif-label"><i style="margin-right: 50px;" class='bx bxs-bell'></i>Notifications</div>
                        <div class="scrollable-notifications">
                            <?php
                            $applicationIdsQuery = "(SELECT application_id
                         FROM tbl_userapp
                         WHERE user_id = ?)
                         UNION
                         (SELECT application_id
                         FROM tbl_scholarship_1_form
                         WHERE user_id = ?)";

                            $stmtApplicationIds = mysqli_prepare($dbConn, $applicationIdsQuery);
                            mysqli_stmt_bind_param($stmtApplicationIds, "ii", $user_id, $user_id);
                            mysqli_stmt_execute($stmtApplicationIds);
                            $applicationIdsResult = mysqli_stmt_get_result($stmtApplicationIds);

                            while ($applicationData = mysqli_fetch_assoc($applicationIdsResult)) {
                                $application_id = $applicationData['application_id'];

                                $notificationsQuery = "
                                SELECT message_id, application_id, admin_id, osa_message_content, attach_files, sent_at
                                FROM tbl_user_messages
                                WHERE user_id = ? AND application_id = ?
                                ORDER BY sent_at DESC
                            ";

                                $stmtNotifications = mysqli_prepare($dbConn, $notificationsQuery);
                                mysqli_stmt_bind_param($stmtNotifications, "ii", $user_id, $application_id);
                                mysqli_stmt_execute($stmtNotifications);
                                $notificationsResult = mysqli_stmt_get_result($stmtNotifications);

                                while ($row = mysqli_fetch_assoc($notificationsResult)) {
                            ?>

                                    <div class="notify_item" data-message-id="<?php echo $row['message_id']; ?>" data-application-id="<?php echo $row['application_id']; ?>" data-admin-id="<?php echo $row['admin_id']; ?>">
                                        <div class="notify_img">
                                            <?php
                                            if (isset($_SESSION['profile'])) {
                                                $admin_image = $_SESSION['profile'];
                                            } else {
                                                $admin_image = '../user_profiles/default-avatar.png';
                                            }
                                            ?>
                                            <img src='img/<?php echo $admin_image; ?>' alt="" style="width: 50px">
                                        </div>
                                        <div class="notify_info">
                                            <a href="#" onclick="showMessageModal(<?php echo $row['message_id']; ?>, <?php echo $row['application_id']; ?>, <?php echo $row['admin_id']; ?>)">
                                                <?php
                                                if (!empty($row['admin_id'])) {
                                                    echo '<p>You received a new message from <span> ' . $_SESSION['admin_username'] . '.</span></p>';
                                                }
                                                ?>
                                            </a>
                                            <span class="notify_time"><?php echo formatSentAt($row['sent_at']); ?></span>
                                        </div>
                                    </div>
                            <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="profile">
                    <a href="applicant_profile.php" class="profile">
                        <?php
                        $select_user = mysqli_query($dbConn, "SELECT * FROM `tbl_user` WHERE user_id = '$user_id'") or die('query failed');
                        $fetch = mysqli_fetch_assoc($select_user);
                        if ($fetch && $fetch['image'] != '') {
                            echo '<img src="../user_profiles/' . $fetch['image'] . '">';
                        } else {
                            echo '<img src="../user_profiles/default-avatar.png">';
                        }
                        ?>
                    </a>
                </div>
            </div>
        </nav>

        <main>

            <!-- MODAL -->
            <div class="modal" id="messageModal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Modal title</h5>
                            <button type="button" class="close" onclick="closeModal()">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="info">
                                <label id="modalMessageContent"></label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeModal()">Close</button>
                        </div>
                    </div>
                </div>
            </div>


            <div class="head-title">
                <div class="left">
                    <h1>Dashboard</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="#">Dashboard</a>
                        </li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li>
                            <a class="active" href="#">Home</a>
                        </li>
                    </ul>
                </div>
            </div>

            <ul class="box-info">
                <li>
                    <i class='bx bxs-calendar-check'></i>
                    <?php include('../include/connection.php'); ?>

                    <?php
                    $result = mysqli_query($dbConn, "SELECT * FROM tbl_scholarship WHERE scholarship_status= 'Ongoing'");
                    $num_rows = mysqli_num_rows($result);
                    ?>
                    <a href="scholarships.php">
                        <span class="text">
                            <h3><?php echo $num_rows; ?></h3>
                            <p>Scholarship Applications</p>
                        </span>
                    </a>
                </li>

                <?php
                $totalAppliedQuery = "SELECT SUM(total_count) AS total_applied FROM (
                    SELECT COUNT(*) AS total_count FROM tbl_userapp WHERE user_id = ?
                    UNION ALL
                    SELECT COUNT(*) AS total_count FROM tbl_scholarship_1_form WHERE user_id = ?
                ) AS combined";

                $stmtTotalApplied = mysqli_prepare($dbConn, $totalAppliedQuery);
                mysqli_stmt_bind_param($stmtTotalApplied, "ii", $user_id, $user_id);
                mysqli_stmt_execute($stmtTotalApplied);
                $totalAppliedResult = mysqli_stmt_get_result($stmtTotalApplied);
                $totalAppliedData = mysqli_fetch_assoc($totalAppliedResult);
                $totalApplied = $totalAppliedData['total_applied'];
                ?>
                <li>
                    <i class='bx bxs-receipt'></i>
                    <a href="application_status.php">
                        <span class="text">
                            <h3><?php echo $totalApplied; ?></h3>
                            <p>Total Scholarships Applied</p>
                        </span>
                    </a>
            </ul>

            <?php
            function formatDateSubmitted($dbDateSubmitted)
            {
                $dateTimeObject = new DateTime($dbDateSubmitted);
                return $dateTimeObject->format('F d, Y');
            }
            ?>

            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3>Recent Application Status</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Scholarship</th>
                                <th>Date Submitted</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row = mysqli_fetch_array($select)) {
                                $statusClass = '';
                                switch ($row['status']) {
                                    case 'Pending':
                                        $statusClass = 'pending';
                                        break;
                                    case 'In Review':
                                        $statusClass = 'inreview';
                                        break;
                                    case 'Incomplete':
                                        $statusClass = 'incomplete';
                                        break;
                                    case 'Qualified':
                                        $statusClass = 'qualified';
                                        break;
                                    case 'Accepted':
                                        $statusClass = 'accepted';
                                        break;
                                    case 'Rejected':
                                        $statusClass = 'rejected';
                                        break;
                                    default:
                                        break;
                                }
                                echo '
                                    <tr>
                                        <td>' . $row['scholarship_name'] . '</td>
                                    <td>' . formatDateSubmitted($row['date_submitted']) . '</td>
                                        <td>
                                            <p class="status ' . $statusClass . '">' . $row['status'] . '</p>
                                        </td>
                                    </tr>';
                            }

                            ?>
                        </tbody>
                    </table>
                </div>

                <?php
                $ongoingScholarshipQuery = "SELECT * FROM tbl_scholarship WHERE scholarship_status = 'Ongoing'";
                $result = $dbConn->query($ongoingScholarshipQuery);
                ?>
                <div class="todo">
                    <div class="head">
                        <h3>Featured Scholarships</h3>
                    </div>
                    <ul class="todo-list">
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<li class='Ongoing'>";
                                echo "<p class='ongoing-scholarship'>" . $row['scholarship'] . "</p>";
                                echo "</li>";
                            }
                        } else {
                            echo "<li>";
                            echo "<p>No ongoing scholarships available.</p>";
                            echo "</li>";
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->

    <script src="js/applicant_logout.js"></script>
    <script src="js/toggle_sidebar.js"></script>
    <script src="js/bell_dropdown.js"></script>
    <script>

        function showMessageModal(messageId, applicationId, adminId) {
            $.ajax({
                url: "fetch_message_content.php",
                type: "POST",
                data: {
                    message_id: messageId,
                    application_id: applicationId,
                    admin_id: adminId
                },
                success: function(response) {
                    document.getElementById("modalMessageContent").innerText = response;
                    document.querySelector("#messageModal .modal-title").innerText = "Message from <?php echo $_SESSION['admin_username']; ?>";
                    openModal();

                    markAsRead(applicationId);
                },
                error: function() {
                    alert("Failed to fetch message content.");
                }
            });
        }


        // Function to open the modal
        function openModal() {
            document.getElementById("messageModal").style.display = "block";
        }

        // Function to close the modal
        function closeModal() {
            document.getElementById("messageModal").style.display = "none";
        }

        const allSideMenu = document.querySelectorAll('#sidebar .side-menu.top li a');

        allSideMenu.forEach(item => {
            const li = item.parentElement;

            item.addEventListener('click', function() {
                allSideMenu.forEach(i => {
                    i.parentElement.classList.remove('active');
                })
                li.classList.add('active');
            })
        });



        // Function to mark a message as read
        function markAsRead(messageId, adminId, registrarId) {
            $.ajax({
                url: "applicant_dashboard.php?read_message=" + messageId + "&admin_id=" + adminId + "&registrar_id=" + registrarId,
                type: "GET",
                success: function() {

                },
                error: function() {
                    alert("Failed to mark message as read.");
                }
            });
        }

        $(".notify_item").on("click", function() {
            var messageId = $(this).data("message-id");
            var adminId = $(this).data("admin-id");
            var registrarId = $(this).data("registrar-id");

            markAsRead(messageId, adminId, registrarId);
        });

    </script>
</body>

</html>