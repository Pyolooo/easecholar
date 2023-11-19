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

$select = mysqli_query($dbConn, "
SELECT 
    applicant_name, 
    date_submitted AS userapp_date_submitted, 
    status AS userapp_status,
    image,
    'tbl_userapp' AS source
FROM tbl_userapp
WHERE status = 'Pending'

UNION

SELECT 
    applicant_name, 
    date_submitted AS scholarship_date_submitted, 
    status AS scholarship_status,
    image,
    'tbl_scholarship_1_form' AS source
FROM tbl_scholarship_1_form
WHERE status = 'Pending'
") or die(mysqli_error($dbConn));

$query = "SELECT
    (SELECT COUNT(*) FROM tbl_userapp WHERE status = 'Pending') +
    (SELECT COUNT(*) FROM tbl_scholarship_1_form WHERE status = 'Pending') AS pendingCount,
    (SELECT COUNT(*) FROM tbl_userapp WHERE status = 'In Review') +
    (SELECT COUNT(*) FROM tbl_scholarship_1_form WHERE status = 'In Review') AS inReviewCount,
    (SELECT COUNT(*) FROM tbl_userapp WHERE status = 'Qualified') +
    (SELECT COUNT(*) FROM tbl_scholarship_1_form WHERE status = 'Qualified') AS qualifiedCount,
    (SELECT COUNT(*) FROM tbl_userapp WHERE status = 'Accepted') +
    (SELECT COUNT(*) FROM tbl_scholarship_1_form WHERE status = 'Accepted') AS acceptedCount,
    (SELECT COUNT(*) FROM tbl_userapp WHERE status = 'Rejected') +
    (SELECT COUNT(*) FROM tbl_scholarship_1_form WHERE status = 'Rejected') AS rejectedCount
";

$result = mysqli_query($dbConn, $query);
$row = mysqli_fetch_assoc($result);

$pendingCount = $row['pendingCount'];
$inReviewCount = $row['inReviewCount'];
$qualifiedCount = $row['qualifiedCount'];
$acceptedCount = $row['acceptedCount'];
$rejectedCount = $row['rejectedCount'];
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

</head>

<body>




    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="#" class="brand">
            <img src="../img/isulogo.png">
            <span class="admin-hub">ADMIN</span>
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
                    <span class="text">Manage Users</span>
                </a>
            </li>
            <li>
                <a href="application_list.php">
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
                <span class="school-name">ISABELA STATE UNIVERSITY SANTIAGO</span>
            </div>
            <div class="right-section">
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
                    <?php include('../include/connection.php'); ?>

                    <?php
                    $result = mysqli_query($dbConn, "SELECT * FROM tbl_scholarship WHERE scholarship_status = 'Ongoing'");
                    $num_rows = mysqli_num_rows($result);
                    ?>
                    <a href="scholarships.php">
                        <span class="text">
                            <h3><?php echo $num_rows; ?></h3>
                            <p>Available Scholarships </p>
                        </span>
                    </a>
                </li>
                <li>
                    <?php
                    include('../include/connection.php');

                    $sql = "
                    SELECT SUM(total_count) AS total FROM (
                        SELECT COUNT(*) AS total_count FROM tbl_user
                        UNION ALL
                        SELECT COUNT(*) AS total_count FROM tbl_admin
                        UNION ALL
                        SELECT COUNT(*) AS total_count FROM tbl_super_admin
                    ) AS combined";

                    $result = mysqli_query($dbConn, $sql);

                    if ($result) {
                        $row = mysqli_fetch_assoc($result);

                        $total_count = $row['total'];
                    } else {
                        echo "Error: " . mysqli_error($dbConn);
                    }
                    ?>

                    <i class='bx bxs-group'></i>
                    <a href="manage_all_users.php">
                        <span class="text">
                            <h3><?php echo $total_count; ?></h3>
                            <p>Total Users</p>
                        </span>
                    </a>
                </li>

                <li>
                    <i class='bx bxs-receipt'></i>
                    <?php include('../include/connection.php'); ?>

                    <?php include('../include/connection.php'); ?>

                    <?php
                    $sql = "
                    SELECT SUM(num_rows) AS total FROM (
                        SELECT COUNT(*) AS num_rows FROM tbl_userapp
                        UNION ALL
                        SELECT COUNT(*) AS num_rows FROM tbl_scholarship_1_form
                    ) AS combined";

                    $result = mysqli_query($dbConn, $sql);

                    if ($result) {
                        $row = mysqli_fetch_assoc($result);

                        $num_rows = $row['total'];
                    } else {
                        echo "Error: " . mysqli_error($dbConn);
                    }
                    ?>
                    <a href="application_list.php">
                        <span class="text">
                            <h3><?php echo $num_rows; ?></h3>
                            <p>Total Applications Received</p>
                        </span>
                    </a>
                </li>
            </ul>




            <div class="table-data">
                <div class="donut-container">
                    <canvas id="applicationStatusChart"></canvas>
                </div>

                <div class="scholarship-analytics">
                    <div class="head">
                        <h3>Scholarship Analytics</h3>
                        <div class="export-button-container">
                            <select id="exportFormatSelect">
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                            </select>
                            <button id="exportButton">Export</button>
                        </div>
                    </div>
                    <table id="scholarship-analytics-table">
                        <thead>
                            <tr>
                                <th>Scholarship Name</th>
                                <th>Number of Applicants</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT s.scholarship, 
                           (COUNT(ua.user_id) + COUNT(sf.user_id)) AS num_applicants
                            FROM tbl_scholarship s
                            LEFT JOIN tbl_userapp ua ON s.scholarship_id = ua.scholarship_id
                            LEFT JOIN tbl_scholarship_1_form sf ON s.scholarship_id = sf.scholarship_id
                            GROUP BY s.scholarship";

                            $listResult = mysqli_query($dbConn, $sql);

                            if ($listResult) {
                                while ($row = mysqli_fetch_assoc($listResult)) {
                                    echo '<tr>';
                                    echo '<td>' . $row['scholarship'] . '</td>';
                                    echo '<td class="applicants-count"> <span class="num_applicants">'  . $row['num_applicants'] . '</span></td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="2">Error executing the query: ' . mysqli_error($dbConn) . '</td></tr>';
                            }

                            $rowsPerPage = 3;
                            $data = array();

                            $listResult = mysqli_query($dbConn, $sql);

                            if (!$listResult) {
                                echo 'Error executing the query: ' . mysqli_error($dbConn);
                                die(mysqli_error($dbConn));
                            } else {
                                $data = array();

                                while ($row = mysqli_fetch_assoc($listResult)) {
                                    $data[] = $row;
                                }
                            }


                            ?>
                        </tbody>
                    </table>
                    <div class="pagination">
                        <a href="#" class="prev" id="prev-page">&lt; Previous</a>
                        <span id="page-number">Page 1</span>
                        <a href="#" class="next" id="next-page">Next &gt;</a>
                    </div>
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
                    <table id="recent-applicants-table">
                        <thead>
                            <tr>
                                <th>Applicant</th>
                                <th>Date Submitted</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $perPage = 10;
                            $applicantsData = array();

                            while ($row = mysqli_fetch_array($select)) {
                                $statusClass = '';
                                $dateSubmitted = '';
                                $statusText = '';

                                if (isset($row['userapp_status'])) {
                                    switch ($row['userapp_status']) {
                                        case 'Pending':
                                            $statusClass = 'Pending';
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
                                } elseif (isset($row['scholarship_status'])) {
                                    switch ($row['scholarship_status']) {
                                    }
                                }

                                if ($row['source'] === 'tbl_userapp' && isset($row['userapp_status'])) {
                                    $statusText = $row['userapp_status'];
                                    $dateSubmitted = $row['userapp_date_submitted'];
                                } elseif ($row['source'] === 'tbl_scholarship_1_form' && isset($row['scholarship_status'])) {
                                    $statusText = $row['scholarship_status'];
                                    $dateSubmitted = $row['date_submitted'];
                                }

                                echo '
                                <tr>
                                    <td><img src="../user_profiles/' . $row['image'] . '" alt="">' . $row['applicant_name'] . '</td>
                                    <td>' . formatDateSubmitted($dateSubmitted) . '</td>
                                    <td><p class="status ' . $statusClass . '">' . $statusText . '</td>
                                    
                                </tr>';



                                $applicantsData[] = array(
                                    'applicant_name' => $row['applicant_name'],
                                    'date_submitted' => formatDateSubmitted($dateSubmitted),
                                    'status' => $statusClass,
                                    'image' => $row['image'],
                                );
                            }
                            ?>

                        </tbody>
                    </table>
                    <div class="pagination applicants-pagination">
                        <a href="#" class="prev" id="prev-applicants-page">&lt; Previous</a>
                        <span id="applicants-page-number">Page 1</span>
                        <a href="#" class="next" id="next-applicants-page">Next &gt;</a>
                    </div>
                </div>

                <?php
                $newScholarsQuery = "(SELECT DISTINCT applicant_name, image, application_id FROM tbl_userapp WHERE status = 'Accepted') UNION (SELECT DISTINCT applicant_name, image, application_id FROM tbl_scholarship_1_form WHERE status = 'Accepted') ORDER BY application_id DESC";
                $result = $dbConn->query($newScholarsQuery);
                $totalScholars = $result->num_rows;
                ?>

                <div class="todo">
                    <div class="head">
                        <h3>New Scholars</h3>
                    </div>
                    <ul class="scholars_list" id="scholars-list">
                        <?php
                        if ($result->num_rows > 0) {
                            $count = 0;
                            while ($row = $result->fetch_assoc()) {
                                if ($count >= 10) {
                                    echo '<li class="scholar_container hidden"><img class="scholar_image" src="../user_profiles/' . $row['image'] . '" alt=""> <span class="scholar_name">' . $row['applicant_name'] . ' </span> </li>';
                                } else {
                                    echo '<li class="scholar_container"><img class="scholar_image" src="../user_profiles/' . $row['image'] . '" alt=""> <span class="scholar_name">' . $row['applicant_name'] . ' </span> </li>';
                                }
                                $count++;
                            }
                        } else {
                            echo '<li>No new scholars found.</li>';
                        }
                        ?>
                    </ul>
                    <?php
                    if ($totalScholars > 10) {
                        echo '<a href="application_list.php" id="view-all-scholars">View All</a>';
                    }
                    ?>
                </div>

            </div>
        </main>
        <!-- MAIN -->
    </section>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        window.location.href = "admin_logout.php";
                    }
                });
            }

            document.querySelector(".logout").addEventListener("click", function(event) {
                event.preventDefault();
                confirmLogout();
            });

            const statusCounts = {
                'Pending': <?php echo $pendingCount; ?>,
                'In Review': <?php echo $inReviewCount; ?>,
                'Qualified': <?php echo $qualifiedCount; ?>,
                'Accepted': <?php echo $acceptedCount; ?>,
                'Rejected': <?php echo $rejectedCount; ?>,
            };

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
                            display: true,
                            text: 'Application Statistics',
                            fontSize: 16,
                        },
                    },
                },
            });


            document.getElementById("exportButton").addEventListener("click", function() {
                var exportFormatSelect = document.getElementById("exportFormatSelect");
                var selectedFormat = exportFormatSelect.value;

                var exportURL = "generate_pdf.php"; // Default to PDF export URL

                if (selectedFormat === "excel") {
                    exportURL = "generate_excel.php"; // Use Excel export URL if selected format is "excel"
                }

                window.location.href = exportURL;
            });

            const scholarshipAnalyticsTable = document.getElementById("scholarship-analytics-table");
            const recentApplicantsTable = document.getElementById("recent-applicants-table");

            // Function to display a page of data
            function displayPage(page, data, rowsPerPage) {
                const tableBody = scholarshipAnalyticsTable.querySelector("tbody");
                tableBody.innerHTML = "";
                const start = (page - 1) * rowsPerPage;
                const end = start + rowsPerPage;
                const pageData = data.slice(start, end);

                pageData.forEach((row) => {
                    const newRow = document.createElement("tr");
                    newRow.innerHTML = `<td>${row.scholarship}</td><td class="applicants-count"><span class="num_applicants">${row.num_applicants}</span></td>`;
                    tableBody.appendChild(newRow);
                });

                document.getElementById("page-number").textContent = `Page ${page}`;
            }

            const data = <?php echo json_encode($data); ?>;
            const rowsPerPage = <?php echo $rowsPerPage; ?>;
            let currentPage = 1;

            displayPage(currentPage, data, rowsPerPage);


            document.getElementById("prev-page").addEventListener("click", () => {
                if (currentPage > 1) {
                    currentPage--;
                    displayPage(currentPage, data, rowsPerPage);
                }
            });

            document.getElementById("next-page").addEventListener("click", () => {
                const totalPages = Math.ceil(data.length / rowsPerPage);
                if (currentPage < totalPages) {
                    currentPage++;
                    displayPage(currentPage, data, rowsPerPage);
                }
            });

            function displayApplicantsPage(page, applicantData, applicantRowsPerPage) {
                const tableBody = recentApplicantsTable.querySelector("tbody");
                tableBody.innerHTML = "";
                const start = (page - 1) * applicantRowsPerPage;
                const end = start + applicantRowsPerPage;
                const pageData = applicantData.slice(start, end);

                pageData.forEach((row) => {
                    const newRow = document.createElement("tr");
                    newRow.innerHTML = `
                    <td><img src="../user_profiles/${row.image}" alt=""> ${row.applicant_name}</td>
                    <td>${row.date_submitted}</td>
                    <td><p class="status ${row.status}">${row.status}</td>
                `;
                    tableBody.appendChild(newRow);
                });

                document.getElementById("applicants-page-number").textContent = `Page ${page}`;
            }

            const applicantData = <?php echo json_encode($applicantsData); ?>;
            const applicantRowsPerPage = <?php echo $perPage; ?>;
            let applicantCurrentPage = 1;

            displayApplicantsPage(applicantCurrentPage, applicantData, applicantRowsPerPage);

            document.getElementById("prev-applicants-page").addEventListener("click", () => {
                if (applicantCurrentPage > 1) {
                    applicantCurrentPage--;
                    displayApplicantsPage(applicantCurrentPage, applicantData, applicantRowsPerPage);
                }
            });

            document.getElementById("next-applicants-page").addEventListener("click", () => {
                const totalPages = Math.ceil(applicantData.length / applicantRowsPerPage);
                if (applicantCurrentPage < totalPages) {
                    applicantCurrentPage++;
                    displayApplicantsPage(applicantCurrentPage, applicantData, applicantRowsPerPage);
                }
            });

            document.addEventListener("DOMContentLoaded", function() {
                const viewAllScholarsLink = document.getElementById("view-all-scholars");
                const hiddenScholars = document.querySelectorAll(".hidden");

                viewAllScholarsLink.addEventListener("click", function() {
                    hiddenScholars.forEach(function(scholar) {
                        scholar.style.display = "block";
                    });

                    // Hide the "View All" link after showing all scholars
                    viewAllScholarsLink.style.display = "none";
                });
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
    </script>

</body>

</html>