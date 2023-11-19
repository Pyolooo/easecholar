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
                    <i class='bx bxs-file'></i>
                    <span class="text">Applications</span>
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

                    <div class="dropdown">
                        <div class="notif-label"><i style="margin-right: 50px;" class='bx bxs-bell'></i>Notifications</div>
                        <?php
                        $notifications = mysqli_query($dbConn, "SELECT * FROM tbl_notifications WHERE is_read = 'unread' OR is_read = 'read' ORDER BY created_at DESC") or die('query failed');
                        ?>
                        <div class="scrollable-notifications">
                            <?php while ($row = mysqli_fetch_assoc($notifications)) { ?>
                                <div class="notify_item">
                                    <div class="notify_img">
                                        <img src='../user_profiles/<?php echo $row['image']; ?>' alt="Profile">
                                    </div>
                                    <div class="notify_info">
                                        <p>
                                            <?php
                                            $source = $row['source'];
                                            $applicationId = $row['application_id'];
                                            $user_id = $row['user_id'];

                                            if ($source == 'tbl_userapp') {
                                                $viewLink = 'view_application';
                                            } elseif ($source == 'tbl_scholarship_1_form') {
                                                $viewLink = 'view_application1';
                                            } else {
                                                $viewLink = '#';
                                            }
                                            ?>

                                            <a href="<?php echo $viewLink ?>.php?id=<?php echo $applicationId; ?>&user_id=<?php echo $user_id; ?>">
                                                <?php echo $row['message']; ?>
                                            </a>

                                        </p>
                                        <span class="notify_time"><?php echo formatCreatedAt($row['created_at']); ?></span>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                
                <div class="profile">
                <a href="osa_profile.php" class="profile">
                        <?php
                        $select_osa = mysqli_query($dbConn, "SELECT * FROM `tbl_admin` WHERE admin_id = '$admin_id'") or die('query failed');
                        $fetch = mysqli_fetch_assoc($select_osa);
                        if ($fetch && $fetch['profile'] != '') {
                            echo '<img src="../user_profiles/' . $fetch['profile'] . '">';
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
                    <section class="table__header">
                        <div class="export-container">
                        <select title="Select Status" id="statusSelect">
                            <option value="Accepted">Accepted</option>
                            <option value="Rejected">Rejected</option>
                        </select>

                            <select title="Select Scholarships" id="scholarshipSelect" name="scholarship_id">
                                <option value="">All Scholarships</option>
                                <?php

                                $scholarshipQuery = "(SELECT DISTINCT scholarship_id, scholarship_name FROM tbl_userapp WHERE status = 'Accepted') UNION (SELECT scholarship_id, scholarship_name FROM tbl_scholarship_1_form WHERE status = 'Accepted')";

                                $scholarshipResult = mysqli_query($dbConn, $scholarshipQuery);

                                if ($scholarshipResult) {
                                    while ($row = mysqli_fetch_assoc($scholarshipResult)) {
                                        $scholarshipId = $row['scholarship_id'];
                                        $scholarshipName = $row['scholarship_name'];
                                        echo '<option value="' . urlencode($scholarshipId) . '">' . $scholarshipName . '</option>';
                                    }
                                }
                                ?>

                            </select>
                            <select title="Select format" id="exportFormatSelect">
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                            </select>

                            <button id="exportButton" class="btn-download" title="Export Data">
                                <i class='bx bxs-file-export'></i> Export
                            </button>

                        </div>
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
                            <?php
                            $select = mysqli_query($dbConn, "SELECT ua.application_id, ua.image, ua.applicant_name, ua.scholarship_name, ua.date_submitted, ua.status, ua.user_id, 'tbl_userapp' AS source
                    FROM tbl_userapp ua
                    JOIN tbl_user u ON ua.user_id = u.user_id
                    UNION
                    SELECT s1f.application_id, s1f.image, s1f.applicant_name, s1f.scholarship_name, s1f.date_submitted, s1f.status, s1f.user_id, 'tbl_scholarship_1_form' AS source
                    FROM tbl_scholarship_1_form s1f
                    JOIN tbl_user u ON s1f.user_id = u.user_id") or die('query failed');

                            $number = 1;
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
                                    <td><?= $number ?></td>
                                    <td><img src="../user_profiles/<?= $row['image'] ?>" alt=""><?= $row['applicant_name'] ?></td>
                                    <td><?= $row['scholarship_name'] ?></td>
                                    <td><?= formatDateSubmitted($row['date_submitted']) ?></td>
                                    <td>
                                        <p class="status <?= $statusClass ?>"><?= $row['status'] ?></p>
                                    </td>
                                    <td>
                                        <?php
                                        $source = $row['source'];
                                        $viewLink = ($source == 'tbl_userapp') ? 'view_application.php' : 'view_application1.php';
                                        $applicationId = $row['application_id'];
                                        $user_id = $row['user_id'];
                                        $reviewLink = $viewLink . '?id=' . $applicationId . '&user_id=' . $user_id;
                                        ?>
                                        <strong><a class="view-link" href="<?= $reviewLink ?>">Review</a></strong>
                                    </td>
                                </tr>

                            <?php
                                $number++;
                            }
                            ?>

                        </table>
                        <div class="pagination"></div>
                    </section>
                </div>
        </main>

        <script src="js/applicants.js"></script>
        <script>
            $(document).ready(function() {
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
                            window.location.href = "osa_logout.php";
                        }
                    });
                }


                document.querySelector(".logout").addEventListener("click", function(event) {
                    event.preventDefault();
                    confirmLogout();
                });

                document.getElementById("exportButton").addEventListener("click", function() {
    var scholarshipSelect = document.getElementById("scholarshipSelect");
    var exportFormatSelect = document.getElementById("exportFormatSelect");
    var statusSelect = document.getElementById("statusSelect"); // new line
    var selectedScholarshipId = scholarshipSelect.value;
    var selectedFormat = exportFormatSelect.value;
    var selectedStatus = statusSelect.value; // new line

    var exportURL = "generate_pdf.php";

    if (selectedFormat === "excel") {
        exportURL = "generate_excel.php";
    }

    if (selectedScholarshipId) {
        exportURL += "?scholarship_id=" + encodeURIComponent(selectedScholarshipId);
    }

    if (selectedStatus) { // new block
        exportURL += (selectedScholarshipId ? "&" : "?") + "status=" + encodeURIComponent(selectedStatus);
    }

    window.location.href = exportURL;
});




                const menuBar = document.querySelector('#content nav .bx.bx-menu');
                const sidebar = document.getElementById('sidebar');

                function toggleSidebar() {
                    sidebar.classList.toggle('hide');
                }

                menuBar.addEventListener('click', toggleSidebar);

                function handleResize() {
                    const screenWidth = window.innerWidth;

                    if (screenWidth <= 768) {
                        sidebar.classList.add('hide');
                    } else {
                        sidebar.classList.remove('hide');
                    }
                }

                window.addEventListener('resize', handleResize);
                handleResize();

                function toggleDropdown() {
                    $(".num").hide();
                }

                $(".notification .bxs-bell").on("click", function(event) {
                    event.stopPropagation();
                    $(".dropdown").toggleClass("active");
                    toggleDropdown();
                    if ($(".dropdown").hasClass("active")) {
                        markAllNotificationsAsRead();
                    } else {}
                });

                $(document).on("click", function() {
                    $(".dropdown").removeClass("active");
                });

                function markAllNotificationsAsRead() {
                    $.ajax({
                        url: "mark_notification_as_read.php",
                        type: "POST",
                        data: {
                            read_message: "all"
                        },
                        success: function() {
                            $(".notify_item").removeClass("unread");
                            fetchNotificationCount();
                        },
                        error: function() {
                            alert("Failed to mark notifications as read.");
                        }
                    });
                }

                $(".notify_item").on("click", function() {
                    var notificationId = $(this).data("notification-id");
                    markNotificationAsRead(notificationId);
                });

                $(".notify_options .delete_option").on("click", function(event) {
                    event.stopPropagation();
                    const notificationId = $(this).data("notification-id");
                    $.ajax({
                        url: "delete_notification.php",
                        type: "POST",
                        data: {
                            notification_id: notificationId
                        },
                        success: function() {
                            $(".notify_item[data-notification-id='" + notificationId + "']").remove();
                            fetchNotificationCount();
                        },
                        error: function() {}
                    });
                });

                $(".notify_options .cancel_option").on("click", function(event) {
                    event.stopPropagation();
                    $(this).closest(".options_menu").removeClass("active");
                });
            });

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

            document.querySelectorAll(".status-button").forEach(button => {
                button.addEventListener("click", () => {
                    document.querySelectorAll(".status-button").forEach(btn => {
                        btn.classList.remove("active");
                    });

                    button.classList.add("active");
                    const status = button.getAttribute("data-status");
                    filterTableByStatus(status);
                });
            });
        </script>
</body>

</html>