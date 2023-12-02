<?php
include '../include/connection.php';
session_name("OsaSession");
session_start();
$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:osa_login.php');
};

if (isset($_GET['logout'])) {
    unset($admin_id);
    session_destroy();
    header('location:osa_login.php');
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
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.all.min.js"></script>
    <!-- My CSS -->
    <link rel="stylesheet" href="css/scholarships.css">

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
            <li class="active">
                <a href="#">
                    <i class='bx bxs-shopping-bag-alt'></i>
                    <span class="text">Scholarships</span>
                </a>
            </li>
            <li>
                <a href="applicants.php">
                <i class='bx bxs-file' ></i>
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
                <span class="school-name">EASE-CHOLAR</span>
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
                    <h1>Scholarships</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="scholarships.php">Scholarship</a>
                        </li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li>
                            <a class="active" href="index.php">Home</a>
                        </li>
                    </ul>
                </div>
                
                <a href="create_scholarship.php" class="btn-download" title="Create Scholarship">
                <i class='bx bxs-file-plus'></i>
                </a>
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
                            $sql = "SELECT scholarship_id, scholarship_logo, scholarship, scholarship_status, expire_date FROM tbl_scholarship";
                            $result = $dbConn->query($sql);

                            while ($row = $result->fetch_assoc()) {
                                $scholarshipId = $row['scholarship_id'];
                                $scholarshipLogo = $row['scholarship_logo'];
                                $scholarshipName = $row['scholarship'];
                                $expireDate = $row['expire_date'];
                                $scholarshipStatus = $row['scholarship_status'];

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
                                    $output .= "<a href='scholarship_details.php?id=$scholarshipId'>";
                                    $output .= "<div class='scholarship-container'>";
                                    $output .= "<img class='scholarship-logo' src='../file_uploads/" . basename($scholarshipLogo) . "' alt='Scholarship Logo'>";
                                    $output .= "<div class='scholarship-name'>";
                                    $output .= "$scholarshipName";
                                    $output .= "<div class='scholarship-deadline'>";
                                    $output .= "<span class='scholarship-status'>$scholarshipStatus</span>";
                                    $output .= "  " . formatExpireDate($expireDate);
                                    $output .= "</div>";
                                    $output .= "</div>";
                                    $output .= "</div>";
                                    $output .= "</a>";
                                    $output .= "</td>";
                                } else {
                                    $output .= "<td class='closed-scholarship'>";
                                    $output .= "<a href='scholarship_details.php?id=$scholarshipId'>";
                                    $output .= "<div class='scholarship-container'>";
                                    $output .= "<img class='scholarship-logo' src='../file_uploads/" . basename($scholarshipLogo) . "' alt='Scholarship Logo'>";
                                    $output .= "<div class='scholarship-name'>";
                                    $output .= "$scholarshipName";
                                    $output .= "<div class='scholarship-deadline'>";
                                    $output .= "<span class='scholarship-status'>$scholarshipStatus</span>";
                                    $output .= "</div>";
                                    $output .= "</div>";
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
        <script src="js/osa_logout.js"></script>
        <script src="js/toggle_sidebar.js"></script>
        <script>
            $(document).ready(function() {
                

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

                    $(".filter-buttons button").removeClass("active");
                    $(`#filter-${status.toLowerCase()}`).addClass("active");
                }

                // Function to handle select option change
                $("#filter-type").change(function() {
                    const status = $(this).val(); 
                    filterScholarships(status);
                });

                $(document).ready(function() {
                    filterScholarships("Ongoing");
                });


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


                // Function to mark all notifications as read
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