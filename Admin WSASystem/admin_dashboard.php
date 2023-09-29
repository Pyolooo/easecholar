<?php
include 'connection.php';
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

// No need to include the connection.php again here
$select = mysqli_query($dbConn, "SELECT * FROM tbl_userapp WHERE status = 'Pending'") or die('query failed');

// Execute SQL queries to fetch counts for each status
$pendingCount = mysqli_query($dbConn, "SELECT COUNT(*) as count FROM tbl_userapp WHERE status = 'Pending'")->fetch_assoc()['count'];
$inReviewCount = mysqli_query($dbConn, "SELECT COUNT(*) as count FROM tbl_userapp WHERE status = 'In Review'")->fetch_assoc()['count'];
$qualifiedCount = mysqli_query($dbConn, "SELECT COUNT(*) as count FROM tbl_userapp WHERE status = 'Qualified'")->fetch_assoc()['count'];
$acceptedCount = mysqli_query($dbConn, "SELECT COUNT(*) as count FROM tbl_userapp WHERE status = 'Accepted'")->fetch_assoc()['count'];
$rejectedCount = mysqli_query($dbConn, "SELECT COUNT(*) as count FROM tbl_userapp WHERE status = 'Rejected'")->fetch_assoc()['count'];
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <!-- My CSS -->
    <link rel="stylesheet" href="css/admin_dashboard.css">
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>


    <title>adminModule</title>
    <style>
        .notification .bxs-bell {
            cursor: pointer;
        }

        .dropdown {
            width: 350px;
            height: auto;
            background: whitesmoke;
            border-radius: 5px;
            box-shadow: 2px 2px 3px rgba(0, 0, 0, 0.125);
            margin: 15px auto 0;
            padding: 15px;
            position: absolute;
            top: 40px;
            /* Adjust the distance from the notification icon as needed */
            right: 0;
            /* To align it with the notification icon */
            display: none;
        }

        .dropdown .notify_item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #dbdaff;
        }

        .dropdown .notify_item:last-child {
            border-bottom: 0px;
        }

        .dropdown .notify_item .notify_img {
            margin-right: 15px;
        }

        .dropdown .notify_item .notify_info p {
            margin-bottom: 5px;
        }

        .dropdown .notify_item .notify_info p span {
            color: #605dff;
            margin-left: 5px;
        }

        .dropdown .notify_item .notify_info .notify_time {
            color: #c5c5e6;
            font-size: 12px;
        }

        .dropdown:before {
            content: "";
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            border: 15px solid;
            border-color: transparent transparent #fff transparent;
        }

        .dropdown.active {
            display: block;
        }

        .scholar_image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border: 3px solid #28a745;
            border-radius: 25px;
            margin-right: 10px;
        }

        .bxs-message-square-check {
            border: #28a745;
        }

        .donut-container {
            width: 60%;
            height: 30%;
            background-color: white;
            padding: 10px;
            border-radius: 20px;
        }
        .scholarship-analytics{
            flex-grow: 1;
	        flex-basis: 300px;
        }
        td{
            padding-left: 10px;
            padding-top: 10px;

        }
        th{
            text-align: left;
        }
        .head-analytics{
            width: 20px;
        }
        .applicants-count{
            text-align: center;
        }
        .num_applicants{
            color: white;
            padding: 0px 20px;
            border-radius: 10px;
            background-color: brown;
            font-size: 20px;
            font-weight: 600;
        }
    </style>

</head>

<body>




    <!-- SIDEBAR -->
    <section id="sidebar" class="hide">
        <a href="#" class="brand">
            <img src="/EASE-CHOLAR/isulogo.png">
            <span class="text">ISU Santiago Extension</span>
        </a>
        <ul class="side-menu top">
            <li class="active">
                <a href="#">
                    <i class='bx bxs-dashboard'></i>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="scholarship_list.php">
                    <i class='bx bxs-shopping-bag-alt'></i>
                    <span class="text">Scholarship</span>
                </a>
            </li>
            <li>
                <a href="manage_users.php">
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
                <a href="#">
                    <i class='bx bxs-cog'></i>
                    <span class="text">Settings</span>
                </a>
            </li>
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
            </div>
            <div class="right-section">
                <div class="notif">
                    <div class="notification">
                        <?php
                        $getNotificationCountQuery = mysqli_query($dbConn, "SELECT COUNT(*) as count FROM tbl_admin_notif WHERE is_read = 'unread'") or die('query failed');
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
                        $notifications = mysqli_query($dbConn, "SELECT * FROM tbl_admin_notif WHERE is_read = 'unread'") or die('query failed');
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
                    <a href="admin_profile.php" class="profile">
                        <?php
                        $select_admin = mysqli_query($dbConn, "SELECT * FROM `tbl_super_admin` WHERE super_admin_id = '$super_admin_id'") or die('query failed');
                        if (mysqli_num_rows($select_admin) > 0) {
                            $fetch_admin = mysqli_fetch_assoc($select_admin);
                        }
                        if ($fetch_admin['profile'] == '') {
                            echo '<img src="img/isulogo.png">';
                        } else {
                            echo '<img src="img/' . $fetch_admin['profile'] . '">';
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




            <div class="table-data">
                <div class="donut-container">
                    <canvas id="applicationStatusChart"></canvas>
                </div>

                <!-- Scholarship Analytics table -->
                <div class="scholarship-analytics">
                    <div class="head">
                        <h3>Scholarship Analytics</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Scholarship Name</th>
                                <th>Number of Applicants</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // SQL query to retrieve scholarship names and count of applicants
                            $sql = "SELECT s.scholarship, COUNT(ua.user_id) AS num_applicants
                            FROM tbl_scholarship s
                            LEFT JOIN tbl_userapp ua ON s.scholarship_id = ua.scholarship_id
                            GROUP BY s.scholarship";

                            $listResult = mysqli_query($dbConn, $sql);

                            if ($listResult) {
                                while ($row = mysqli_fetch_assoc($listResult)) {
                                    echo '<tr>';
                                    echo '<td>' . $row['scholarship'] . '</td>';
                                    echo '<td class = "applicants-count"> <span class="num_applicants">'  . $row['num_applicants'] . '</span></td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="2">Error executing the query: ' . mysqli_error($dbConn) . '</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>


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
                $newScholarsQuery = "SELECT * FROM tbl_userapp WHERE status = 'Qualified' ORDER BY application_id DESC LIMIT 10";
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Fetch data from your PHP code (you can use AJAX)
        // For demonstration purposes, let's assume you have fetched the counts as follows:
        const statusCounts = {
            'Pending': <?php echo $pendingCount; ?>,
            'In Review': <?php echo $inReviewCount; ?>,
            'Qualified': <?php echo $qualifiedCount; ?>,
            'Accepted': <?php echo $acceptedCount; ?>,
            'Rejected': <?php echo $rejectedCount; ?>,
        };

        // Create a Donut Chart
        const ctx = document.getElementById('applicationStatusChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(statusCounts),
                datasets: [{
                    data: Object.values(statusCounts),
                    backgroundColor: [
                        '#fd7238', // Pending
                        '#ffce26', // In Review
                        '#00d084', // Qualified
                        '#28a745', // Accepted
                        'red', // Rejected
                    ],
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    },
                    title: {
                        display: true, // Display the title
                        text: 'Application Statistics', // Set the title text here
                        fontSize: 16, // Adjust the font size as needed
                    },
                },
            },
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
                        window.location.href = "admin_logout.php";
                    }
                });
            }

            // Attach the click event to the "Logout" link
            document.querySelector(".logout").addEventListener("click", function(event) {
                event.preventDefault(); // Prevent the link from navigating directly
                confirmLogout();
            });

            // TOGGLE SIDEBAR
            const menuBar = document.querySelector("#content nav .bx.bx-menu");
            const sidebar = document.getElementById("sidebar");

            menuBar.addEventListener("click", function() {
                sidebar.classList.toggle("hide");
            });

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