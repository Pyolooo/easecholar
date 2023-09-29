<?php
include 'connection.php';
session_name("OsaSession");
session_start();
$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location: osa_login.php');
    exit();
}

if (isset($_GET['logout'])) {
    unset($admin_id);
    session_destroy();
    header('location: osa_login.php');
    exit();
}

    // Query the database to check if the user has seen the pop-up
    $checkPopupSeenQuery = mysqli_query($dbConn, "SELECT seen FROM tbl_admin WHERE admin_id = '$admin_id'");
    $userData = mysqli_fetch_assoc($checkPopupSeenQuery);
    $popupSeen = $userData['seen'];
    
    if (!$popupSeen) {
        // The user has not seen the pop-up, so show it
        $showPopupReminder = true;

        // Update the database to mark the pop-up as seen for this user
        mysqli_query($dbConn, "UPDATE tbl_admin SET seen = 1 WHERE admin_id = '$admin_id'");
    } else {
        // The user has already seen the pop-up, so don't show it
        $showPopupReminder = false;
    }


// No need to include the connection.php again here
$select = mysqli_query($dbConn, "SELECT * FROM tbl_userapp WHERE status = 'Pending'") or die('query failed');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <!-- My CSS -->
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.all.min.js"></script>

    <title>OSAModule</title>
    <style>

    </style>

</head>

<body>

 <!--Pop up -->
 <?php if ($showPopupReminder) { ?>
 <div class="modal" id="reminderModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Profile Completion Reminder</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Please complete or update your profile information.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="laterButton">Later</button>
                    <button type="button" class="btn btn-primary" id="completeNowButton">Complete Now</button>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>


    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="#" class="brand">
            <div class="isulog-container">
                <img class="isu-logo" src="/EASE-CHOLAR/isulogo.png">
            </div>
            <span class="osa-hub">OSA</span>
        </a>
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
                <a href="applicants.php">
                    <i class='bx bxs-group'></i>
                    <span class="text">Applicants</span>
                </a>
            </li>
            <li>
                <a href="applicant_list.php">
                    <i class='bx bxs-file'></i>
                    <span class="text">Application List</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class='bx bxs-message-dots'></i>
                    <span class="text">Message</span>
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
        <nav>
            <div class="menu">
                <i class='bx bx-menu'></i>
                <span class="school-name">ISABELA STATE UNIVERSITY SANTIAGO</span>
            </div>
            <div class="right-section">
                <div class="notif">
                    <div class="notification">
                        <?php
                        $getNotificationCountQuery = mysqli_query($dbConn, "SELECT COUNT(*) as count FROM tbl_notifications WHERE is_read = 'unread'") or die('query failed');
                        $notificationCountData = mysqli_fetch_assoc($getNotificationCountQuery);
                        $notificationCount = $notificationCountData['count'];


                        // Show the notification count only if there are new messages
                        if ($notificationCount > 0) {
                            echo '<i id="bellIcon" class="bx bxs-bell"></i>';
                            echo '<span class="num">' . $notificationCount . '</span>';
                        } else {
                            echo '<i id="bellIcon" class="bx bxs-bell"></i>';
                            echo '<span class="num" style="display: none;">' . $notificationCount . '</span>';
                        }
                        ?>
                    </div>

                    <?php
                    function formatCreatedAt($dbCreatedAt)
                    {
                        $dateTimeObject = new DateTime($dbCreatedAt);
                        return $dateTimeObject->format('Y-m-d, g:i A');
                    }
                    ?>

                    <!-- Inside the "notif" div, add the following code: -->
                    <div class="dropdown">
                        <?php
                        $notifications = mysqli_query($dbConn, "SELECT * FROM tbl_notifications WHERE is_read = 'unread'") or die('query failed');
                        ?>
                        <?php while ($row = mysqli_fetch_assoc($notifications)) { ?>
                            <div class="notify_item">
                                <div class="notify_img">
                                    <img src='/EASE-CHOLAR/user_profiles/<?php echo $row['image']; ?>' alt="" style="width: 50px">
                                </div>
                                <div class="notify_info">
                                    <p><?php echo $row['message']; ?></p>
                                    <span class="notify_time"><?php echo formatCreatedAt($row['created_at']); ?></span>
                                </div>
                                <div class="notify_options">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                    <!-- Add the ellipsis (three-dots) icon and the options menu -->
                                    <div class="options_menu">
                                        <span class="delete_option" data-notification-id="<?php echo $row['notification_id']; ?>">Delete</span>
                                        <span class="cancel_option">Cancel</span>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                </div>
                <div class="profile">
                    <a href="osa_profile.php" class="profile">
                        <?php
                        $select_osa = mysqli_query($dbConn, "SELECT * FROM `tbl_admin` WHERE admin_id = '$admin_id'") or die('query failed');
                        $fetch = mysqli_fetch_assoc($select_osa);
                        if ($fetch && $fetch['profile'] != '') {
                            // Build the absolute path to the image using $_SERVER['DOCUMENT_ROOT']
                            $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/EASE-CHOLAR/user_profiles/' . $fetch['profile'];

                            if (file_exists($imagePath)) {
                                echo '<img src="/EASE-CHOLAR/user_profiles/' . $fetch['profile'] . '">';
                            } else {
                                echo '<img src="/EASE-CHOLAR/user_profiles/default-avatar.png">';
                            }
                        } else {
                            echo '<img src="/EASE-CHOLAR/user_profiles/default-avatar.png">';
                        }
                        ?>
                    </a>

                </div>
            </div>
        </nav>
        <!-- MAIN -->
        <main>
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
                    <?php include('connection.php'); ?>

                    <?php
                    $result = mysqli_query($dbConn, "SELECT * FROM tbl_scholarship");
                    $num_rows = mysqli_num_rows($result);
                    ?>
                    <span class="text">
                        <h3><?php echo $num_rows; ?></h3>
                        <p>Available Scholarships </p>
                    </span>
                </li>
                <li>
                    <i class='bx bxs-group'></i>
                    <?php include('connection.php'); ?>

                    <?php
                    $result = mysqli_query($dbConn, "SELECT * FROM tbl_userapp");
                    $num_rows = mysqli_num_rows($result);
                    ?>
                    <span class="text">
                        <h3><?php echo $num_rows; ?></h3>
                        <p>Applicants</p>
                    </span>
                </li>
                <li>
                    <i class='bx bxs-receipt'></i>
                    <span class="text">
                        <h3><?php echo $num_rows; ?></h3>
                        <p>Total Applications Received</p>
                    </span>
                </li>
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
                        <h3>Recent Applicants</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Applicant</th>
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
                                <td><img src="/EASE-CHOLAR/user_profiles/' . $row['image'] . '" alt="">' . $row['applicant_name'] . '</td>
                                <td>' . formatDateSubmitted($row['date_submitted']) . '</td>
                                <td><p class="status ' . $statusClass . '">' . $row['status'] . '</td>
                            </tr>
                                ';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>


                <?php
                $newScholarsQuery = "SELECT * FROM tbl_userapp WHERE status = 'Accepted' ORDER BY application_id DESC LIMIT 10";
                $result = $dbConn->query($newScholarsQuery);
                ?>
                <div class="todo">
                    <div class="head">
                        <h3>New Scholars</h3>
                    </div>
                    <ul class="scholars_list">
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo '<li class="scholar_container"><img class="scholar_image" src="/EASE-CHOLAR/user_profiles/' . $row['image'] . '" alt=""> <span class="scholar_name">' . $row['applicant_name'] . ' </span> </li>';
                            }
                        } else {
                            echo '<li>No new scholars found.</li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </main>
        <!-- MAIN -->
    </section>

    <script>
         $(document).ready(function() {
            // Show the pop-up message
            $("#reminderModal").modal("show");

            // Handle "Complete Now" button click
            $("#completeNowButton").click(function() {
                // Redirect to update_profile.php
                window.location.href = "osa_profile.php";
            });

            // Handle "Later" button click
            $("#laterButton").click(function() {
                // Close the pop-up message
                $("#reminderModal").modal("hide");
            });
        });

        $(document).ready(function() {
            // Function to confirm logout
            function confirmLogout() {
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
                        window.location.href = "osa_logout.php";
                    }
                });
            }

            // Attach the click event to the "Logout" link
            document.querySelector(".logout").addEventListener("click", function(event) {
                event.preventDefault(); // Prevent the link from navigating directly
                confirmLogout();
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
                $(".num").hide(); // Hide the notification count when the dropdown is toggled
            }

            // Add click event listener to the bell icon to mark all notifications as read
            $(".notification .bxs-bell").on("click", function(event) {
                event.stopPropagation();
                // Toggle the dropdown
                $(".dropdown").toggleClass("active");
                toggleDropdown();
                // If the dropdown is being opened, mark all notifications as read
                if ($(".dropdown").hasClass("active")) {
                    markAllNotificationsAsRead();
                } else {
                    // If the dropdown is being closed, perform any other actions (if needed)
                }
            });

            // Close the dropdown when clicking outside of it
            $(document).on("click", function() {
                $(".dropdown").removeClass("active");
            });

            // Function to mark all notifications as read
            function markAllNotificationsAsRead() {
                $.ajax({
                    url: "mark_notification_as_read.php", // Replace with the correct path to your "mark_notification_as_read.php" file
                    type: "POST",
                    data: {
                        read_message: "all" // Pass "all" as a parameter to mark all notifications as read
                    },
                    success: function() {
                        // On successful marking as read, remove the "unread" class from all notification items
                        $(".notify_item").removeClass("unread");
                        // Fetch and update the notification count on the bell icon (if needed)
                        fetchNotificationCount();
                    },
                    error: function() {
                        alert("Failed to mark notifications as read.");
                    }
                });
            }

            // Add click event listener to the notifications to mark them as read
            $(".notify_item").on("click", function() {
                var notificationId = $(this).data("notification-id");
                markNotificationAsRead(notificationId);
            });

            // Function to handle delete option click
            $(".notify_options .delete_option").on("click", function(event) {
                event.stopPropagation();
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
                        $(".notify_item[data-notification-id='" + notificationId + "']").remove();
                        // Fetch and update the notification count on the bell icon
                        fetchNotificationCount();
                    },
                    error: function() {
                        // Handle error if deletion fails
                    }
                });
            });

            // Function to handle cancel option click
            $(".notify_options .cancel_option").on("click", function(event) {
                event.stopPropagation();
                // Hide the options menu
                $(this).closest(".options_menu").removeClass("active");
            });
        });
    </script>

</body>

</html>