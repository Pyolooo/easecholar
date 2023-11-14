<?php
include '../include/connection.php';
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

$checkPopupSeenQuery = mysqli_query($dbConn, "SELECT seen FROM tbl_admin WHERE admin_id = '$admin_id'");
$userData = mysqli_fetch_assoc($checkPopupSeenQuery);
$popupSeen = $userData['seen'];

if (!$popupSeen) {
    $showPopupReminder = true;
    mysqli_query($dbConn, "UPDATE tbl_admin SET seen = 1 WHERE admin_id = '$admin_id'");
} else {
    $showPopupReminder = false;
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


$sql = "SELECT s.scholarship, 
        (COUNT(ua.user_id) + COUNT(sf.user_id)) AS num_applicants
        FROM tbl_scholarship s
        LEFT JOIN tbl_userapp ua ON s.scholarship_id = ua.scholarship_id
        LEFT JOIN tbl_scholarship_1_form sf ON s.scholarship_id = sf.scholarship_id
        GROUP BY s.scholarship";

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>


    <title>OSAModule</title>

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
        <a class="brand">
            <div class="isulog-container">
                <img class="isu-logo" src="../img/isulogo.png">
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
                                        <img src='/EASE-CHOLAR/user_profiles/<?php echo $row['image']; ?>' alt="" style="width: 50px">
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
                    <i class='bx bxs-group'></i>
                    <?php include('../include/connection.php'); ?>

                    <?php
                    $sql = "
                    SELECT SUM(total_count) AS total FROM (
                        SELECT COUNT(*) AS total_count FROM tbl_userapp
                        UNION ALL
                        SELECT COUNT(*) AS total_count FROM tbl_scholarship_1_form
                    ) AS combined";

                    $result = mysqli_query($dbConn, $sql);

                    if ($result) {
                        $row = mysqli_fetch_assoc($result);

                        $total_count = $row['total'];
                    } else {
                        echo "Error: " . mysqli_error($dbConn);
                    }
                    ?>

                    <a href="applicants.php">
                        <span class="text">
                            <h3><?php echo $total_count; ?></h3>
                            <p>Applicants</p>
                        </span>
                    </a>
                </li>
                <li>
                    <i class='bx bxs-receipt'></i>
                    <a href="applicants.php">
                        <span class="text">
                            <h3><?php echo $total_count; ?></h3>
                            <p>Total Applications Received</p>
                        </span>
                    </a>
                </li>
            </ul>

            <div class="table-data">
                <div class="order">
                    <div class="scholarship-analytics">
                        <div class="head">
                            <h3>Scholarship Analytics</h3>
                            <div class="export-button-container">
                                <select title="Select format" id="exportFormatSelect">
                                    <option value="pdf">PDF</option>
                                    <option value="excel">Excel</option>
                                </select>
                                <button title="Export" id="exportButton">
                                <i class='bx bxs-file-export'></i>Export</button>
                            </div>
                        </div>
                        <canvas id="scholarshipAnalyticsChart"></canvas>
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
                                    echo '<li class="scholar_container hidden"><img class="scholar_image" src="/EASE-CHOLAR/user_profiles/' . $row['image'] . '" alt=""> <span class="scholar_name">' . $row['applicant_name'] . ' </span> </li>';
                                } else {
                                    echo '<li class="scholar_container"><img class="scholar_image" src="/EASE-CHOLAR/user_profiles/' . $row['image'] . '" alt=""> <span class="scholar_name">' . $row['applicant_name'] . ' </span> </li>';
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


    <script>
        $(document).ready(function() {
            $("#reminderModal").modal("show");

            $("#completeNowButton").click(function() {
                window.location.href = "osa_profile.php";
            });

            $("#laterButton").click(function() {
                $("#reminderModal").modal("hide");
            });
        });

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

            var backgroundColors = [
                'rgba(75, 192, 192, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 205, 86, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 0, 0, 0.2)',
                'rgba(0, 255, 0, 0.2)',
                'rgba(0, 0, 255, 0.2)',
                'rgba(128, 128, 128, 0.2)'
            ];

            if (!window.chartInitialized) {
                var labels = <?php echo json_encode(array_column($data, 'scholarship')); ?>;
                var numApplicants = <?php echo json_encode(array_column($data, 'num_applicants')); ?>;

                var ctx = document.getElementById('scholarshipAnalyticsChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Number of Applicants',
                            data: numApplicants,
                            backgroundColor: backgroundColors,
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }],
                    },
                    options: {
                        indexAxis: 'x',
                    }
                });

                window.chartInitialized = true;
            }

            document.getElementById("exportButton").addEventListener("click", function() {
                var exportFormatSelect = document.getElementById("exportFormatSelect");
                var selectedFormat = exportFormatSelect.value;

                var exportURL = "pdf_scholarship.php"; 

                if (selectedFormat === "excel") {
                    exportURL = "excel_scholarship.php";
                }

                window.location.href = exportURL;
            });

            const scholarshipAnalyticsTable = document.getElementById("scholarship-analytics-table");
            const recentApplicantsTable = document.getElementById("recent-applicants-table");

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
        });
    </script>

</body>

</html>