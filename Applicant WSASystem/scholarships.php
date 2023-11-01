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

$select = mysqli_query($dbConn, "SELECT application_id, scholarship_name, date_submitted, status FROM tbl_userapp WHERE user_id = '$user_id'") or die(mysqli_error($dbConn));


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

function formatExpireDate($dbExpireDate)
{
    $dateTimeObject = new DateTime($dbExpireDate);
    $formatted_date = "Until " . $dateTimeObject->format('F j, Y');
    return $formatted_date;
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
    <link rel="stylesheet" href="css/scholarships.css">

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
            <li>
                <a href="applicant_dashboard.php">
                    <i class='bx bxs-dashboard'></i>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <li class="active">
                <a href="#">
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
                <span class="school-name">ISABELA STATE UNIVERSITY SANTIAGO</span>
            </div>
            <div class="right-section">
                <div class="notif">
                <div class="notification">
                <?php
                        $user_id = $_SESSION['user_id'];

                        // Initialize the total notification count
                        $totalNotificationCount = 0;

                        // Step 1: Fetch a list of application IDs for the user from both tables
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

                        // Step 2: Loop through application IDs and fetch counts for each application
                        while ($applicationData = mysqli_fetch_assoc($applicationIdsResult)) {
                            $application_id = $applicationData['application_id'];

                            $userMessageCountQuery = "SELECT COUNT(*) AS count FROM tbl_user_messages WHERE application_id = ? AND user_id = ? AND read_status = 'unread'";

                            $stmtUserMessageCount = mysqli_prepare($dbConn, $userMessageCountQuery);
                            mysqli_stmt_bind_param($stmtUserMessageCount, "ii", $application_id, $user_id);
                            mysqli_stmt_execute($stmtUserMessageCount);
                            $userMessageCountData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtUserMessageCount));
                            $userMessageCount = $userMessageCountData['count'];

                            // Add the count to the total count
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
                        <?php
                        $applicationIdsQuery = "SELECT application_id FROM tbl_userapp WHERE user_id = ?";
                        $stmtApplicationIds = mysqli_prepare($dbConn, $applicationIdsQuery);
                        mysqli_stmt_bind_param($stmtApplicationIds, "i", $user_id);
                        mysqli_stmt_execute($stmtApplicationIds);
                        $applicationIdsResult = mysqli_stmt_get_result($stmtApplicationIds);

                        while ($applicationData = mysqli_fetch_assoc($applicationIdsResult)) {
                            $application_id = $applicationData['application_id'];

                            $notificationsQuery = "
                                SELECT message_id, application_id, admin_id, osa_message_content, sent_at
                                FROM tbl_user_messages
                                WHERE user_id = ? AND application_id = ?
                                ORDER BY sent_at DESC
                            ";

                            $stmtNotifications = mysqli_prepare($dbConn, $notificationsQuery);
                            mysqli_stmt_bind_param($stmtNotifications, "ii", $user_id, $application_id);
                            mysqli_stmt_execute($stmtNotifications);
                            $notificationsResult = mysqli_stmt_get_result($stmtNotifications);

                            // Loop through messages for the current application
                            while ($row = mysqli_fetch_assoc($notificationsResult)) {
                        ?>
                                <div class="notify_item" data-message-id="<?php echo $row['message_id']; ?>" data-application-id="<?php echo $row['application_id']; ?>" data-admin-id="<?php echo $row['admin_id']; ?>">
                                    <div class="notify_img">
                                        <?php
                                        // Before accessing 'profile', check if it exists in the session
                                        if (isset($_SESSION['profile'])) {
                                            $admin_image = $_SESSION['profile'];
                                        } else {
                                            $admin_image = 'img/default-avatar.png';
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
                <div class="profile">
                    <a href="applicant_profile.php" class="profile">
                        <?php
                        $select_user = mysqli_query($dbConn, "SELECT * FROM `tbl_user` WHERE user_id = '$user_id'") or die('query failed');
                        $fetch = mysqli_fetch_assoc($select_user);
                        if ($fetch && $fetch['image'] != '') {
                            $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/EASE-CHOLAR/user_profiles/' . $fetch['image'];

                            if (file_exists($imagePath)) {
                                echo '<img src="../user_profiles/' . $fetch['image'] . '">';
                            } else {
                                echo '<img src="../user_profiles/default-avatar.png">';
                            }
                        } else {
                            echo '<img src="../user_profiles/default-avatar.png">';
                        }
                        ?>
                    </a>
                </div>

            </div>
        </nav>



        <!-- NAVBAR -->

        <!-- MAIN -->
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
                    <h1>Scholarships</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="scholarships.php">Scholarship</a>
                        </li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li>
                            <a class="active" href="#">Home</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3>Available Scholarships</h3>
                        <div class="filter-select">
                            <label for="filter-type">Filter:</label>
                            <select id="filter-type">
                                <option value="Ongoing">Ongoing</option>
                                <option value="Closed">Closed</option>
                                <option value="All">All</option>
                            </select>
                        </div>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="scholarship-list">
    <?php
    $sql = "SELECT scholarship_id, scholarship, scholarship_status, expire_date, application_form_table FROM tbl_scholarship";
    $result = $dbConn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $scholarshipId = $row['scholarship_id'];
        $scholarshipName = $row['scholarship'];
        $expireDate = $row['expire_date'];
        $scholarshipStatus = $row['scholarship_status'];
        $selectedFormTable = $row['application_form_table']; // Get the application_form_table

        $currentDate = date('Y-m-d');

        if ($currentDate >= $expireDate && $scholarshipStatus == 'Ongoing') {
            $updateSql = "UPDATE tbl_scholarship SET scholarship_status = 'Closed' WHERE scholarship_id = $scholarshipId";
            $updateResult = $dbConn->query($updateSql);

            if (!$updateResult) {
                echo "Error updating scholarship status for ID: $scholarshipId<br>";
            } else {
                $scholarshipStatus = 'Closed';
            }
        }
        // Add the data-status attribute based on scholarship status
        $dataStatusAttribute = ($scholarshipStatus == 'Ongoing') ? 'Ongoing' : 'Closed';

        // Modify the output based on the scholarship status
        $output = "<tr data-status='$dataStatusAttribute'>";

        if ($scholarshipStatus == 'Ongoing') {
            $output .= "<td>";
            $output .= "<a href='scholarship_details.php?id=$scholarshipId&application_form_table=$selectedFormTable'>";
            $output .= $scholarshipName;
            $output .= "<div class='scholarship-deadline'>";
            $output .= "<span class='scholarship-status'>$scholarshipStatus</span>";
            $output .= "  " . formatExpireDate($expireDate);
            $output .= "</div>";
            $output .= "</a>";
            $output .= "</td>";
            
        } else {
            $output .= "<td class='closed-scholarship'>";
            $output .= "<a href='scholarship_details.php?id=$scholarshipId&application_form_table=$selectedFormTable'>";
            $output .= $scholarshipName;
            $output .= "<div class 'scholarship-deadline'>";
            $output .= "<span class='scholarship-status'>$scholarshipStatus</span>";
            $output .= "</div>";
            $output .= "</a>";
            $output .= "</td>";
            
        }

        $output .= "</tr>";

        echo $output;
    }
    ?>
</tbody>
                    </table>

                </div>
            </div>
        </main>
        <!-- MAIN -->
    </section>
    <script>
        function confirmLogout() {
            // Display a SweetAlert confirmation
            Swal.fire({
                title: "Logout",
                text: "Are you sure you want to log out?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, log out",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    // If the user confirms, redirect to the logout script
                    window.location.href = "applicant_logout.php";
                }
            });
        }

        // Function to filter scholarships
        function filterScholarships(status) {
            const $scholarshipRows = $("#scholarship-list tr");

            $scholarshipRows.each(function() {
                const scholarshipStatus = $(this).data("status");

                if (status === "All" || status === scholarshipStatus) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            // Remove the 'active' class from all filter buttons
            $(".filter-buttons button").removeClass("active");
            // Add the 'active' class to the clicked filter button
            $(`#filter-${status.toLowerCase()}`).addClass("active");
        }

        // Function to handle select option change
        $("#filter-type").change(function() {
            const status = $(this).val(); // Get the selected option's value
            filterScholarships(status);
        });

        // Set 'Ongoing' as the default filter when the page loads
        $(document).ready(function() {
            filterScholarships("Ongoing");
        });


        // Attach the click event to the "Logout" link
        document.querySelector(".logout").addEventListener("click", function(event) {
            event.preventDefault(); // Prevent the link from navigating directly

            // Call the confirmLogout() function to display the SweetAlert confirmation
            confirmLogout();
        });

        function showMessageModal(messageId, applicationId, adminId) {
            console.log("messageId:", messageId);
            console.log("applicationId:", applicationId);
            console.log("adminId:", adminId);
            $.ajax({
                url: "fetch_message_content.php",
                type: "POST",
                data: {
                    message_id: messageId,
                    application_id: applicationId,
                    admin_id: adminId // Send the admin_id to the server
                },
                success: function(response) {
                    // Display the message content in the modal
                    console.log(response); // Add this line to see the response in the browser's console
                    document.getElementById("modalMessageContent").innerText = response;
                    document.querySelector("#messageModal .modal-title").innerText = "Message from <?php echo $_SESSION['admin_username']; ?>";
                    openModal();

                    // Now, after displaying the message, mark it as read by calling another AJAX request
                    markAsRead(applicationId); // Call the function to mark the message as read
                },
                error: function() {
                    alert("Failed to fetch message content.");
                }
            });
        }

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


        // TOGGLE SIDEBAR
        const menuBar = document.querySelector('#content nav .bx.bx-menu');
        const sidebar = document.getElementById('sidebar');

        function toggleSidebar() {
            sidebar.classList.toggle('hide');
        }

        menuBar.addEventListener('click', toggleSidebar);

        // Function to handle window resize and toggle sidebar based on screen width
        function handleResize() {
            const screenWidth = window.innerWidth;

            if (screenWidth <= 768) {
                sidebar.classList.add('hide');
            } else {
                sidebar.classList.remove('hide');
            }
        }

        // Add a window resize event listener
        window.addEventListener('resize', handleResize);

        // Initial check and toggle based on current screen width
        handleResize();



        // Function to toggle the dropdown
        function toggleDropdown() {
            $(".dropdown").toggleClass("active");
            $(".num").hide(); // Hide the notification count when the dropdown is toggled
        }

        // Add click event listener to the bell icon
        $("#bellIcon").on("click", function() {
            toggleDropdown();
        });

        // Function to mark a message as read
        function markAsRead(messageId, adminId, registrarId) {
            $.ajax({
                url: "applicant_dashboard.php?read_message=" + messageId + "&admin_id=" + adminId + "&registrar_id=" + registrarId,
                type: "GET",
                success: function() {
                    // Add a console log for success if needed
                },
                error: function() {
                    alert("Failed to mark message as read.");
                }
            });
        }

        // Add click event listener to the messages to mark them as read
        $(".notify_item").on("click", function() {
            var messageId = $(this).data("message-id");
            var adminId = $(this).data("admin-id");
            var registrarId = $(this).data("registrar-id");

            markAsRead(messageId, adminId, registrarId);
        });



        // Add click event listener to the three-dots icon
        $(".notify_options .bx").on("click", function(event) {
            event.stopPropagation(); // Prevent click event propagation to the document

            // Toggle the options menu for the clicked notification
            $(this).siblings(".options_menu").toggleClass("active");

            // Close other open options menus when a new menu is opened
            $(".options_menu.active").not($(this).siblings(".options_menu")).removeClass("active");
        });

        // Add click event listener to the delete option
        $(".notify_options .delete_option").on("click", function(event) {
            event.stopPropagation(); // Prevent click event propagation to the document

            const notificationId = $(this).data("notification-id");
            // Send an AJAX request to delete the notification from the database
            $.ajax({
                url: "delete_notification.php", // Replace with the PHP file to handle the delete operation
                type: "POST",
                data: {
                    notification_id: notificationId
                },
                success: function() {
                    // If deletion is successful, remove the notification from the dropdown
                    $(this).closest(".notify_item").remove();
                    // Update the notification count on the bell icon
                    fetchNotificationCount();
                    // Hide the options menu
                    $(this).closest(".options_menu").removeClass("active");
                },
                error: function() {
                    // Handle error if deletion fails
                    alert("Failed to delete notification.");
                }
            });
        });

        // Add click event listener to the cancel option
        $(".notify_options .cancel_option").on("click", function(event) {
            event.stopPropagation(); // Prevent click event propagation to the document

            // Hide the options menu
            $(this).closest(".options_menu").removeClass("active");
        });

        // Close the options menu when clicking outside of it
        $(document).on("click", function() {
            $(".options_menu.active").removeClass("active");
        });
    </script>
</body>

</html>