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

$rowsPerPage = isset($_GET['rows']) ? intval($_GET['rows']) : 10;

$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $rowsPerPage;

$number = 1;

$select = mysqli_query($dbConn, "SELECT ua.application_id, ua.image, ua.applicant_name, ua.scholarship_name, ua.date_submitted, ua.status, ua.reasons, ua.other_reason, ua.user_id, 'tbl_userapp' AS source
FROM tbl_userapp ua
JOIN tbl_user u ON ua.user_id = u.user_id
UNION
SELECT s1f.application_id, s1f.image, s1f.applicant_name, s1f.scholarship_name, s1f.date_submitted, s1f.status, s1f.reasons, s1f.other_reason, s1f.user_id, 'tbl_scholarship_1_form' AS source
FROM tbl_scholarship_1_form s1f
JOIN tbl_user u ON s1f.user_id = u.user_id
ORDER BY date_submitted DESC
LIMIT $offset, $rowsPerPage") or die('query failed');

$countUserAppQuery = mysqli_query($dbConn, "SELECT COUNT(*) AS total FROM tbl_userapp") or die('count userapp query failed');
$countUserAppData = mysqli_fetch_assoc($countUserAppQuery);

$countScholarship1FormQuery = mysqli_query($dbConn, "SELECT COUNT(*) AS total FROM tbl_scholarship_1_form") or die('count scholarship_1_form query failed');
$countScholarship1FormData = mysqli_fetch_assoc($countScholarship1FormQuery);

$totalRows = $countUserAppData['total'] + $countScholarship1FormData['total'];


$exactPages = floor($totalRows / $rowsPerPage);
$remainingRows = $totalRows % $rowsPerPage;
$totalPages = ($remainingRows > 0) ? $exactPages + 1 : $exactPages;


$currentPage = min($currentPage, $totalPages);

$totalPages = max($totalPages, 1);

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
    <link rel="stylesheet" href="css/application_list.css">

    <title>ADMINModule</title>
</head>

<body>
    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="#" class="brand">
            <div class="isulog-container">
                <img class="isu-logo" src="../img/isulogo.png">
            </div>
            <span class="admin-hub">ADMIN</span>
        </a>
        <ul class="side-menu top">
            <li>
                <a href="admin_dashboard.php">
                    <i class='bx bxs-dashboard'></i>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="scholarship_list.php">
                    <i class='bx bxs-shopping-bag-alt'></i>
                    <span class="text">Scholarships</span>
                </a>
            </li>
            <li>
                <a href="manage_users.php">
                    <i class='bx bxs-group'></i>
                    <span class="text">Manage Users</span>
                </a>
            </li>
            <li class="active">
                <a href="#">
                    <i class='bx bxs-file'></i>
                    <span class="text">Application List</span>
                </a>
            </li>
        </ul>
        <ul class="side-menu">
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
                <span class="school-name">EASE-CHOLAR</span>
            </div>
            <!-- <div class="right-section">
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

                    <div class="dropdown">
                        <?php
                        $notifications = mysqli_query($dbConn, "SELECT * FROM tbl_notifications WHERE is_read = 'unread'") or die('query failed');
                        ?>
                        <?php while ($row = mysqli_fetch_assoc($notifications)) { ?>
                            <div class="notify_item">
                                <div class="notify_img">
                                    <img src='../user_profiles/<?php echo $row['image']; ?>' alt="" style="width: 50px">
                                </div>
                                <div class="notify_info">
                                    <p><?php echo $row['message']; ?></p>
                                    <span class="notify_time"><?php echo $row['created_at']; ?></span>
                                </div>
                            </div>
                        <?php } ?>
                    </div> -->

            </div>
            <div class="profile">
                <a href="admin_profile.php" class="profile">
                    <?php
                    $select_admin = mysqli_query($dbConn, "SELECT * FROM `tbl_super_admin` WHERE super_admin_id = '$super_admin_id'") or die('query failed');
                    $fetch = mysqli_fetch_assoc($select_admin);
                    if ($fetch && $fetch['profile'] != '') {
                        echo '<img src="../user_profiles/' . $fetch['profile'] . '">';
                    } else {
                        echo '<img src="../user_profiles/isulogo.png">';
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
                    <div class="rowsPerpage">
                        <label for="rowsPerPage">Number of Rows:</label>
                        <select id="rowsPerPage" onchange="changeRowsPerPage()">
                            <option value="10" <?php if ($rowsPerPage == 10) echo 'selected'; ?>>10</option>
                            <option value="20" <?php if ($rowsPerPage == 20) echo 'selected'; ?>>20</option>
                            <option value="50" <?php if ($rowsPerPage == 50) echo 'selected'; ?>>50</option>
                            <option value="100" <?php if ($rowsPerPage == 100) echo 'selected'; ?>>100</option>
                        </select>
                    </div>
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

                        <div class="reason-filter">
                            <label for="reasonFilter">Filter by Reason:</label>
                            <select id="reasonFilter" name="reasonFilter">
                                <option value="">Select Reason</option>
                                <option value="Insufficient GWA">Insufficient GWA</option>
                                <option value="Failure to meet eligible criteria">Failure to meet eligible criteria</option>
                                <option value="Lack of evidence">Lack of evidence</option>
                                <option value="Lack of supporting documents">Lack of supporting documents</option>
                            </select>
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
                                    <th id="reasonHeader" style="display: none;">Reason <span class="icon-arrow">&UpArrow;</span></th>
                                </tr>
                            </thead>
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
                                    <td>
                                        <div class="image-applicant-container"><img src="../user_profiles/<?= $row['image'] ?>" alt="">
                                            <p class="applicant_name"><?= $row['applicant_name'] ?></p>
                                        </div>
                                    </td>
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
                                        $reviewLink = $viewLink . '?id=' . $applicationId . '&user_id=' . $user_id; // Include user_id in the URL
                                        ?>
                                        <strong><a class="view-link" href="<?= $reviewLink ?>">Review</a></strong>
                                    </td>

                                    <?php if ($row['status'] == 'Rejected') { ?>
                                        <td class="reasons-cell" style="display: none;">
                                            <?php
                                            // Check if reasons are not null and not empty
                                            if (!empty($row['reasons'])) {
                                                $reasons = json_decode($row['reasons']);

                                                // Check if $reasons is an array
                                                if (is_array($reasons) || is_object($reasons)) {
                                                    foreach ($reasons as $reason) {
                                                        echo $reason . "<br>";
                                                    }
                                                } else {
                                                    echo "Invalid reasons format";
                                                }
                                            } else {
                                                echo "No reasons provided";
                                            }

                                            if (!empty($row['other_reason'])) {
                                                echo "Others: " . $row['other_reason'];
                                            }
                                            ?>
                                        </td>

                                    <?php } ?>
                                </tr>

                            <?php
                                $number++;
                            }
                            ?>
                        </table>
                        <div class="entries-range">
                            Showing <?php echo min(($currentPage - 1) * $rowsPerPage + 1, $totalRows); ?> to <?php echo min($currentPage * $rowsPerPage, $totalRows); ?> of <?php echo $totalRows; ?> entries
                        </div>


                        <div class="pagination">
                            <?php
                            if ($currentPage > 1) {
                                echo '<button class="pagination-button" onclick="changePage(' . ($currentPage - 1) . ')">&lt; Prev</button>';
                            }

                            for ($i = 1; $i <= $totalPages; $i++) {
                                if ($totalPages > 5 && $i > 2 && $i < ($totalPages - 1) && ($i < ($currentPage - 1) || $i > ($currentPage + 1))) {
                                    if ($i == 3 && $currentPage > 4) {
                                        echo '<span class="pagination-ellipsis">...</span>';
                                    }
                                    continue;
                                }

                                echo '<button class="pagination-button' . ($currentPage == $i ? ' active' : '') . '" onclick="changePage(' . $i . ')">' . $i . '</button>';
                            }

                            if ($totalPages > 5 && $currentPage < ($totalPages - 2)) {
                                echo '<span class="pagination-ellipsis">...</span>';
                            }


                            if ($totalRows > $rowsPerPage && $currentPage < $totalPages) {
                                echo '<button class="pagination-button" onclick="changePage(\'next\')">Next &gt;</button>';
                            }
                            ?>
                        </div>
                    </section>
                </div>
        </main>

        <script src="js/applicants.js"></script>
        <script src="js/admin_logout.js"></script>
        <script src="js/toggle_sidebar.js"></script>
        <script>
            function changePage(page) {
                if (page === 'next' && <?php echo $currentPage; ?> < <?php echo $totalPages; ?>) {
                    page = <?php echo $currentPage; ?> + 1;
                } else if (page === 'prev' && <?php echo $currentPage; ?> > 1) {
                    page = <?php echo $currentPage; ?> - 1;
                }

                window.location.href = "application_list.php?rows=" + document.getElementById("rowsPerPage").value + "&page=" + page;
            }

            function changeRowsPerPage() {
                var selectedRows = document.getElementById("rowsPerPage").value;
                var currentPage = <?php echo $currentPage; ?>;
                var url = "application_list.php?rows=" + selectedRows + "&page=" + currentPage;



                window.location.href = url;
            }

            $(document).ready(function() {


                document.getElementById("reasonFilter").addEventListener("change", function() {
                    const selectedStatus = document.querySelector('.status-button.active').getAttribute("data-status");
                    const selectedReason = this.value;

                    filterTableByStatusAndReason(selectedStatus, selectedReason);
                });

                function filterTableByStatusAndReason(status, reason) {
                    const rows = document.querySelectorAll(".table__body tbody tr");

                    rows.forEach((row) => {
                        const statusCell = row.querySelector(".status");
                        const reasonsCell = row.querySelector(".reasons-cell");

                        // Check if the status and reason match the selected values
                        if (
                            (status === "all" || statusCell.textContent === status) &&
                            (reason === "" || (reasonsCell && reasonsCell.textContent.includes(reason)))
                        ) {
                            row.style.display = "";
                        } else {
                            row.style.display = "none";
                        }
                    });
                }

                filterTableByStatusAndReasonOnLoad();



                function filterTableByStatusAndReasonOnLoad() {
                    const selectedStatus = document.querySelector('.status-button.active').getAttribute("data-status");
                    const selectedReason = document.getElementById("reasonFilter").value;

                    filterTableByStatusAndReason(selectedStatus, selectedReason);
                }

                toggleReasonFilter(document.querySelector('.status-button.active').getAttribute("data-status"));

                filterTableByStatusAndReasonOnLoad();


                document.getElementById("reasonFilter").addEventListener("change", function() {
                    const selectedStatus = document.querySelector('.status-button.active').getAttribute("data-status");

                    filterTableByStatusAndReason(selectedStatus, this.value);
                });

                function toggleReasonFilter(selectedStatus) {
                    var reasonFilterLabel = document.querySelector('label[for="reasonFilter"]');
                    var reasonFilter = document.getElementById("reasonFilter");
                    var reasonHeader = document.getElementById("reasonHeader");

                    if (selectedStatus === "Rejected") {

                        reasonFilterLabel.style.display = "inline-block";
                        reasonFilter.style.display = "inline-block";
                        reasonHeader.style.display = "";
                    } else {

                        reasonFilterLabel.style.display = "none";
                        reasonFilter.style.display = "none";
                        reasonHeader.style.display = "none";
                    }

                }


                document.querySelectorAll(".status-button").forEach(function(button) {
                    button.addEventListener("click", function() {
                        const selectedStatus = button.getAttribute("data-status");

                        document.querySelectorAll(".status-button").forEach(function(btn) {
                            btn.classList.remove("active");
                        });
                        button.classList.add("active");

                        toggleReasonFilter(selectedStatus);

                        filterTableByStatusAndReason(selectedStatus, document.getElementById("reasonFilter").value);
                    });
                });

                toggleReasonFilter(document.querySelector('.status-button.active').getAttribute("data-status"));


                function toggleDropdown() {
                    $(".num").hide();
                }

                $(".notification .bxs-bell").on("click", function(event) {
                    event.stopPropagation();
                    // Toggle the dropdown
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
                    // Hide the options menu
                    $(this).closest(".options_menu").removeClass("active");
                });
            });

            function filterTableByStatus(status) {
                const rows = document.querySelectorAll(".table__body tbody tr");

                rows.forEach(row => {
                    const statusCell = row.querySelector(".status");
                    const reasonsCell = row.querySelector("td.reasons-cell");

                    if (status === "all" || statusCell.textContent === status) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }

                    // Always hide the reasons cell when the status is "all"
                    if (reasonsCell) {
                        reasonsCell.style.display = (status === "all") ? "none" : "";
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