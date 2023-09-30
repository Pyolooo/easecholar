<?php
include '../include/connection.php';
session_name("AdminSession");
session_start();

if (!isset($_SESSION['super_admin_id'])) {
  header('location: admin_login.php');
  exit();
}

$super_admin_id = $_SESSION['super_admin_id'];

if (isset($_GET['logout'])) {
  unset($super_admin_id);
  session_destroy();
  header('location: admin_login.php');
  exit();
}

if (!$dbConn) {
  die("Connection failed: " . mysqli_connect_error());
}

// Retrieve regular users (tbl_user)
$sqlUser = "SELECT * FROM tbl_user WHERE acc_status = 'verified'";
$resultUser = mysqli_query($dbConn, $sqlUser);

if (!$resultUser) {
  die("Query failed: " . mysqli_error($dbConn));
}

// Retrieve OSA users (tbl_admin)
$sqlAdmin = "SELECT * FROM tbl_admin WHERE role = 'OSA'";
$resultAdmin = mysqli_query($dbConn, $sqlAdmin);

if (!$resultAdmin) {
  die("Query failed: " . mysqli_error($dbConn));
}

$sqlRegistrar = "SELECT * FROM tbl_registrar WHERE role = 'Registrar'";
$resultRegistrar = mysqli_query($dbConn, $sqlRegistrar);

if (!$resultRegistrar) {
  die("Query failed: " . mysqli_error($dbConn));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Boxicons -->
  <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- My CSS -->
  <link rel="stylesheet" href="css/manage_users.css">

  <title>AdminModule</title>
  <style>
  </style>
</head>

<body>
  <!-- SIDEBAR -->
  <section id="sidebar" class="hide">
    <a href="#" class="brand">
      <img src="../img/isulogo.png">
      <span class="text">ISU Santiago Extension</span>
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
                  <img src='../user_profiles/<?php echo $row['image']; ?>' alt="" style="width: 50px">
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
              echo '<img src="../user_profiles/isulogo.png">';
            } else {
              echo '<img src="../user_profiles/' . $fetch_admin['profile'] . '">';
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
          <h1>List of Users</h1>
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
        <a href="create_user.php" class="btn-download">
					<i class='bx bx-plus'></i>
					<span class="text">Create User</span>
				</a>
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
      <h3>Manage Users</h3>
        <div class="input-group">
          <input type="search" placeholder="Search Data...">
          <img src="../img/search.png" alt="">
        </div>
      </section>


      <section class="table__body filterable">
        <div class="filter-buttons">
          <div class="filter-button active" data-filter="applicants">Applicants</div>
          <div class="filter-button" data-filter="osa">OSA</div>
          <div class="filter-button" data-filter="registrar">Registrar</div>
        </div>

        <div id="applicantsSection">
          <table>
            <thead>
              <tr>
                <th>Id <span class="icon-arrow">&UpArrow;</span></th>
                <th>Full Name <span class="icon-arrow">&UpArrow;</span></th>
                <th>Email <span class="icon-arrow">&UpArrow;</span></th>
                <th>Manage <span class="icon-arrow">&UpArrow;</span></th>
              </tr>
            </thead>
            <tbody>
              <?php
              while ($row = mysqli_fetch_assoc($resultUser)) {
                $customId = $row['custom_id'];
                $fullName = $row['full_name'];
                $email = $row['email'];
                $image = $row['image'];

                echo '<tr>';
                echo '<td>' . $customId . '</td>';
                echo '<td><img src="../user_profiles/' . $image . '" alt="">' . $fullName . '</td>';
                echo '<td>' . $email . '</td>';
                echo '</tr>';
              }
              ?>
            </tbody>
          </table>
        </div>

        <div id="osaSection" style="display: none;">
          <table>
            <thead>
              <tr>
                <th>Id <span class="icon-arrow">&UpArrow;</span></th>
                <th>Full Name <span class="icon-arrow">&UpArrow;</span></th>
                <th>Email <span class="icon-arrow">&UpArrow;</span></th>
                <th>Role <span class="icon-arrow">&UpArrow;</span></th>
                <th>Status <span class="icon-arrow">&UpArrow;</span></th>
                <th>Manage <span class="icon-arrow">&UpArrow;</span></th>
              </tr>
            </thead>
            <tbody>
              <?php
              while ($row = mysqli_fetch_assoc($resultAdmin)) {
                $osaId = $row['admin_id'];
                $fullName = $row['full_name'];
                $email = $row['email'];
                $profile = $row['profile'];
                $role = $row['role'];
                $status = $row['is_active']; // Add this line to retrieve the status
              
                echo '<tr>';
                echo '<td>' . $osaId . '</td>';
                echo '<td><img src="../user_profiles/' . $profile . '" alt="">' . $fullName . '</td>';
                echo '<td>' . $email . '</td>';
                echo '<td>' . $role . '</td>';
                
                // Add "Activate" or "Deactivate" button based on the user's status
                if ($status == 0) {
                  echo '<td><button class="osa-status-button" data-id="' . $osaId . '" data-status="0">Activate</button></td>';
                } else {
                  echo '<td><button class="osa-status-button" data-id="' . $osaId . '" data-status="1" style="background-color: red; border: none;">Deactivate</button></td>';
                }
                
                echo '</tr>';
              }
              
              ?>
            </tbody>
          </table>
        </div>

        <div id="registrarSection" style="display: none;">
          <table>
            <thead>
              <tr>
                <th>Id <span class="icon-arrow">&UpArrow;</span></th>
                <th>Full Name <span class="icon-arrow">&UpArrow;</span></th>
                <th>Email <span class="icon-arrow">&UpArrow;</span></th>
                <th>Role <span class="icon-arrow">&UpArrow;</span></th>
                <th>Status <span class="icon-arrow">&UpArrow;</span></th>
                <th>Manage <span class="icon-arrow">&UpArrow;</span></th>
              </tr>
            </thead>
            <tbody>
              <?php
              while ($row = mysqli_fetch_assoc($resultRegistrar)) {
                $registrarId = $row['registrar_id'];
                $fullName = $row['full_name'];
                $email = $row['email'];
                $profile = $row['profile'];
                $role = $row['role'];
                $status = $row['is_active'];


                echo '<tr>';
                echo '<td>' . $registrarId . '</td>';
                echo '<td><img src="../user_profiles/' . $profile . '" alt="">' . $fullName . '</td>';
                echo '<td>' . $email . '</td>';
                echo '<td>' . $role . '</td>';
                // Add "Activate" or "Deactivate" button based on the user's status
                if ($status == 0) {
                  echo '<td><button class="reg-status-button" data-id="' . $registrarId . '" data-status="0">Activate</button></td>';
                } else {
                  echo '<td><button class="reg-status-button" data-id="' . $registrarId . '" data-status="1" style="background-color: red; border: none;">Deactivate</button></td>';
                }

                echo '</tr>';
                }
              ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>

    <script src="applicants.js"></script>
    <script>
// Add an event listener for button clicks using event delegation
document.addEventListener("click", function(event) {
  if (event.target.classList.contains("osa-status-button")) {
    const button = event.target;
    const osaId = button.getAttribute("data-id");
    const currentStatus = parseInt(button.getAttribute("data-status"));

    // Display a confirmation dialog
    Swal.fire({
      title: currentStatus === 1 ? "Activate Account" : "Deactivate Account",
      text: currentStatus === 1 ? "Are you sure you want to activate this OSA user's account?" : "Are you sure you want to deactivate this OSA user's account?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: currentStatus === 1 ? "Yes, activate" : "Yes, deactivate",
      cancelButtonText: "Cancel"
    }).then((result) => {
      if (result.isConfirmed) {
        // Send an AJAX request to update the user's status
        $.ajax({
          url: "update_osa_status.php", // Replace with the PHP file to handle status updates
          type: "POST",
          data: {
            osaId: osaId,
            status: currentStatus === 1 ? 0 : 1
          },
          success: function(response) {
            if (response === "success") {
              // Update the button text and data-status attribute
              button.textContent = currentStatus === 1 ? "Activate" : "Deactivate";
              button.setAttribute("data-status", currentStatus === 1 ? 0 : 1);
              // Change the background color of the button based on the new status
              button.style.backgroundColor = currentStatus === 1 ? "green" : "red";
              // Show a success message
              Swal.fire("Account Updated", "The OSA user's account has been updated.", "success");
            } else {
              // Show an error message
              Swal.fire("Error", "Failed to update the account. Please try again.", "error");
            }
          },
          error: function() {
            // Handle errors if the AJAX request fails
            Swal.fire("Error", "An error occurred while processing your request.", "error");
          }
        });
      }
    });
  }
});

// Add an event listener for button clicks using event delegation
document.addEventListener("click", function(event) {
  if (event.target.classList.contains("reg-status-button")) {
    const button = event.target;
    const registrarId = button.getAttribute("data-id");
    const currentStatus = parseInt(button.getAttribute("data-status"));

    // Display a confirmation dialog
    Swal.fire({
      title: currentStatus === 1 ? "Activate Account" : "Deactivate Account",
      text: currentStatus === 1 ? "Are you sure you want to activate this registrar user's account?" : "Are you sure you want to deactivate this registrar user's account?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: currentStatus === 1 ? "Yes, activate" : "Yes, deactivate",
      cancelButtonText: "Cancel"
    }).then((result) => {
      if (result.isConfirmed) {
        // Send an AJAX request to update the user's status
        $.ajax({
          url: "update_reg_status.php", // Replace with the PHP file to handle status updates for Registrar users
          type: "POST",
          data: {
            registrarId: registrarId,
            status: currentStatus === 1 ? 0 : 1
          },
          success: function(response) {
            if (response === "success") {
              // Update the button text and data-status attribute
              button.textContent = currentStatus === 1 ? "Activate" : "Deactivate";
              button.setAttribute("data-status", currentStatus === 1 ? 0 : 1);
              // Change the background color of the button based on the new status
              button.style.backgroundColor = currentStatus === 1 ? "green" : "red";
              // Show a success message
              Swal.fire("Account Updated", "The registrar user's account has been updated.", "success");
            } else {
              // Show an error message
              Swal.fire("Error", "Failed to update the account. Please try again.", "error");
            }
          },
          error: function() {
            // Handle errors if the AJAX request fails
            Swal.fire("Error", "An error occurred while processing your request.", "error");
          }
        });
      }
    });
  }
});



      document.addEventListener("DOMContentLoaded", function() {
      const filterButtons = document.querySelectorAll(".filter-button");
      const applicantsSection = document.getElementById("applicantsSection");
      const osaSection = document.getElementById("osaSection");
      const registrarSection = document.getElementById("registrarSection");

      // Initially, show Applicants section by default
      applicantsSection.style.display = "block";

      filterButtons.forEach(button => {
        button.addEventListener("click", function() {
          // Remove active class from all buttons
          filterButtons.forEach(btn => btn.classList.remove("active"));
          // Add active class to the clicked button
          button.classList.add("active");

          // Hide all sections
          applicantsSection.style.display = "none";
          osaSection.style.display = "none";
          registrarSection.style.display = "none";

          const selectedFilter = button.getAttribute("data-filter");

          // Show the selected section based on the filter
          if (selectedFilter === "applicants") {
            applicantsSection.style.display = "block";
          } else if (selectedFilter === "osa") {
            osaSection.style.display = "block";
          } else if (selectedFilter === "registrar") {
            registrarSection.style.display = "block";
          }
        });
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