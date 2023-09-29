<?php
include 'connection.php';
session_name("RegistrarSession");
session_start();
$registrar_id = $_SESSION['registrar_id'];

if(!isset($registrar_id)){
   header('location:registrar_login.php');
};

if(isset($_GET['logout'])){
   unset($registrar_id);
   session_destroy();
   header('location:registrar_login.php');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Boxicons -->
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<!-- My CSS -->
	<link rel="stylesheet" href="scholarships.css">

	<title>RegistrarModule</title>
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
        .scholarship-deadline{
            font-size: 15px;
            color:black;
        }
        /* In your CSS file */
        .scholarship-status {
        color: green; /* Change to your desired color */

}
		
    </style>
</head>
<body>


	<!-- SIDEBAR -->
	<section id="sidebar" class="hide">
		<a href="#" class="brand">
			<img src="img/isulogo.png">
			<span class="text">ISU Santiago Extension</span>
		</a>
		<ul class="side-menu top">
			<li>
				<a href="index.php">
					<i class='bx bxs-dashboard' ></i>
					<span class="text">Dashboard</span>
				</a>
			</li>
            <li class="active">
				<a href="#">
					<i class='bx bxs-shopping-bag-alt' ></i>
					<span class="text">Scholarships</span>
				</a>
			</li>
			<li>
				<a href="applicants.php">
					<i class='bx bxs-group' ></i>
					<span class="text">Applicants</span>
				</a>
			</li>
			<li>
				<a href="applicant_list.php">
					<i class='bx bxs-file' ></i>
					<span class="text">Application List</span>
				</a>
			</li>
			<li>
				<a href="#">
					<i class='bx bxs-message-dots' ></i>
					<span class="text">Message</span>
				</a>
			</li>
			<li>
				<a href="#">
					<i class='bx bxs-group' ></i>
					<span class="text">Team</span>
				</a>
			</li>
		</ul>
		<ul class="side-menu">
			<li>
				<a href="#" class="logout">
					<i class='bx bxs-log-out-circle' ></i>
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
                        $getNotificationCountQuery = mysqli_query($dbConn, "SELECT COUNT(*) as count FROM tbl_reg_notifications WHERE is_read = 'unread'") or die('query failed');
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
                        $notifications = mysqli_query($dbConn, "SELECT * FROM tbl_reg_notifications WHERE is_read = 'unread'") or die('query failed');
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
                    <a href="registrar_profile.php" class="profile">
                        <?php
                        $select_registrar = mysqli_query($dbConn, "SELECT * FROM `tbl_registrar` WHERE registrar_id = '$registrar_id'") or die('query failed');
                        $fetch = mysqli_fetch_assoc($select_registrar);
                        if ($fetch && $fetch['profile'] != '') {
                            // Build the absolute path to the image using $_SERVER['DOCUMENT_ROOT']
                            $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/EASE-CHOLAR/user_profiles/' . $fetch['profile'];

                            if (file_exists($imagePath)) {
                                echo '<img src="/EASE-CHOLAR/user_profiles/' . $fetch['profile'] . '">';
                            } else {
                                echo '<img src="img/default-avatar.png">';
                            }
                        } else {
                            echo '<img src="img/default-avatar.png">';
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
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="index.php">Home</a>
						</li>
					</ul>
				</div>
			</div>



            <?php
                function formatExpireDate($dbExpireDate) {
                    $dateTimeObject = new DateTime($dbExpireDate);
                    $formatted_date = "Until " . $dateTimeObject->format('F j, Y'); // Example: "Until January 1, 2023"
                    return $formatted_date;
                }
             ?>


			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>Available Scholarships</h3>
						<form action="#">
				<div class="form-input">
					<input type="search" placeholder="Search...">
					<button type="submit" class="search-btn"><i class='bx bx-search' ></i></button>
				</div>
			</form>
					</div>
					<table>
						<thead>
							<hr>
						</thead>
						<tbody>
							<?php
							include('connection.php');

							if ($dbConn->connect_error){
								die('Connection failed: ' . $dbConn->connect_errno);
							}

							$sql = "SELECT * FROM tbl_scholarship";
							$result = $dbConn->query($sql);

							if (!$result){
								die("Invalid query: " . $dbConn->connect_error);
							}

							while($row = $result->fetch_assoc()){
								echo "
								<tr>
									<td>
										$row[scholarship_id].
										<a href='scholarship_details.php?id=$row[scholarship_id]'>
											$row[scholarship] <div class='scholarship-deadline'> <span class ='scholarship-status'> $row[scholarship_status] </span>  " . formatExpireDate($row['expire_date']) . " </div>
										</a>
									</td>
								</tr>
								";
							}
							?>
							
						</tbody>
					</table>
				</div>
			</div>
		</main>
		<!-- MAIN -->

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
                        window.location.href = "registrar_logout.php";
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