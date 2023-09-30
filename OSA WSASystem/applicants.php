<?php
include '../include/connection.php';
session_name("OsaSession");
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('location: osa_login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];

if (isset($_GET['logout'])) {
    unset($admin_id);
    session_destroy();
    header('location: osa_login.php');
    exit();
}

$select = mysqli_query($dbConn, "SELECT * FROM tbl_userapp") or die('query failed');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.all.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <!-- My CSS -->
    <link rel="stylesheet" href="css/applicants.css">

    <title>OSAModule</title>
</head>

<body>
    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="#" class="brand">
            <div class="isulog-container">
                <img class="isu-logo" src="../img/isulogo.png">
            </div>
            <span class="osa-hub">OSA</span>
        </a>
        <ul class="side-menu top">
            <li>
                <a href="osa_dashboard.php">
                    <i class='bx bxs-dashboard'></i>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="scholarships.php">
                    <i class='bx bxs-shopping-bag-alt'></i>
                    <span class="text">Scholarships</span>
                </a>
            </li>
            <li class="active">
                <a href="applicants.php">
                    <i class='bx bxs-group'></i>
                    <span class="text">Applicants</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class='bx bxs-message-dots'></i>
                    <span class="text">Message</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class='bx bxs-group'></i>
                    <span class="text">Team</span>
                </a>
            </li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="#">
                    <i class='bx bxs-cog'></i>
                    <span class="text">Settings</span>
                </a>
            </li>
            <li>
                <a href="#" class="logout">
                    <i class='bx bxs-log-out-circle'></i>
                    <span class="text">Logout</span>
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
                                    <span class="notify_time"><?php echo $row['created_at']; ?></span>
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
                            $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/EASE-CHOLAR/user_profiles/' . $fetch['profile'];

                            if (file_exists($imagePath)) {
                                echo '<img src="../user_profiles/' . $fetch['profile'] . '">';
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
            <div class="head-title">
                <div class="left">
                    <h1>Applicants</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="scholarships.php">Applicants</a>
                        </li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li>
                            <a class="active" href="index.php">Home</a>
                        </li>
                    </ul>
                </div>

        <?php while ($row = mysqli_fetch_array($select)) { ?>
            <?php
            $scholarshipNameVariable = $row['scholarship_name'];
            ?>
            <a href="generate_pdf.php?scholarship_name=<?php echo urlencode($scholarshipNameVariable); ?>" class="btn-download">
                <img class="export-img" src="../img/export.png">
                <span class="text">Export</span>
            </a>
        <?php } ?>




            </div>
        </main>

        <?php
        function formatDateSubmitted($dbDateSubmitted)
        {
            $dateTimeObject = new DateTime($dbDateSubmitted);
            return $dateTimeObject->format('F d, Y');
        }
        ?>


        <main class="table">
            <section class="table__header">
                <h1>Applicant's Application</h1>
                <div class="input-group">
                    <input type="search" placeholder="Search Data...">
                    <img src="../img/search.png" alt="">
                </div>
            </section>


            <section class="table__body filterable">
                <div class="filter">
                    <div class="status-filter">
                        <button class="status-button active" data-status="all">All</button>
                        <button class="status-button pending-button" data-status="Pending">Pending</button>
                        <button class="status-button inreview-button" data-status="In Review">In Review</button>
                        <button class="status-button qualified-button" data-status="Qualified">Qualified</button>
                        <button class="status-button accepted-button" data-status="Accepted">Accepted</button>
                        <button class="status-button rejected-button" data-status="Rejected">Rejected</button>
                    </div>
                </div>


                <table>
                    <thead>
                        <tr>
                            <th>Id <span class="icon-arrow">&UpArrow;</span></th>
                            <th>Applicant Name <span class="icon-arrow">&UpArrow;</span></th>
                            <th>Scholarship <span class="icon-arrow">&UpArrow;</span></th>
                            <th>Submission <span class="icon-arrow">&UpArrow;</span></th>
                            <th>Status <span class="icon-arrow">&UpArrow;</span></th>
                            <th>Action <span class="icon-arrow">&UpArrow;</span></th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php
$select = mysqli_query($dbConn, "SELECT ua.*, u.custom_id
            FROM tbl_userapp ua
            JOIN tbl_user u ON ua.user_id = u.user_id") or die('query failed');
?>

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
                        ?>

                            <tr>
                            <td><?= $row['custom_id'] ?></td>
                                <td><img src="/EASE-CHOLAR/user_profiles/<?= $row['image'] ?>" alt=""><?= $row['applicant_name'] ?></td>
                                <td><?= $row['scholarship_name'] ?></td>
                                <td><?= formatDateSubmitted($row['date_submitted']) ?></td>
                                <td>
                                    <p class="status <?= $statusClass ?>"><?= $row['status'] ?></p>
                                </td>
                                <td>
                                    <strong><a href="view_application.php?id=<?= $row['application_id'] ?>">Review</a></strong>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>

                </table>
            </section>
        </main>

        <script src="js/applicants.js"></script>
        <script>
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

            // Function to filter table rows based on status
            function filterTableByStatus(status) {
                const rows = document.querySelectorAll(".table__body tbody tr");

                rows.forEach(row => {
                    const statusCell = row.querySelector(".status");
                    if (status === "all" || statusCell.textContent === status) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            }

            // Add click event listeners to the status buttons
            document.querySelectorAll(".status-button").forEach(button => {
                button.addEventListener("click", () => {
                    // Remove the "active" class from all buttons
                    document.querySelectorAll(".status-button").forEach(btn => {
                        btn.classList.remove("active");
                    });

                    // Add the "active" class to the clicked button
                    button.classList.add("active");

                    // Get the status from the button's data attribute
                    const status = button.getAttribute("data-status");

                    // Filter the table rows based on the selected status
                    filterTableByStatus(status);
                });
            });
        </script>
</body>

</html>