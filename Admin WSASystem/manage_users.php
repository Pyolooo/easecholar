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
$sqlUser = "SELECT * FROM tbl_user";
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

$sqlsuperAdmin = "SELECT * FROM tbl_super_admin";
$resultsuperAdmin = mysqli_query($dbConn, $sqlsuperAdmin);

if (!$resultsuperAdmin) {
  die("Query failed: " . mysqli_error($dbConn));
}

function formatExpireDate($dbExpireDate)
{
  $dateTimeObject = new DateTime($dbExpireDate);
  $formatted_date = $dateTimeObject->format('F j, Y');
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
  <link rel="stylesheet" href="css/manage_users.css">

  <title>AdminModule</title>
</head>

<body>
  <!-- SIDEBAR -->
  <section id="sidebar">
    <a href="#" class="brand">
      <img src="../img/isulogo.png">
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
      <li class="active">
        <a href="#">
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
                        $select_admin = mysqli_query($conn, "SELECT * FROM `tbl_super_admin` WHERE super_admin_id = '$super_admin_id'") or die('query failed');
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
        <a href="create_user.php" class="btn-download" id="createUserButton">
          <i class='bx bxs-user-plus'></i>
        </a>
      </div>



      <div class="table-data">
        <div class="order">
          <section class="table__header">
            <h3>Manage System Users</h3>
            <div class="input-group">
              <input type="search" placeholder="Search Data...">
              <img src="../img/search.png" alt="">
            </div>
          </section>


          <section class="table__body filterable">
            <div class="filter-buttons">
              <div class="filter-button active" data-filter="applicants">Students</div>
              <div class="filter-button" data-filter="osa">OSA</div>
              <div class="filter-button" data-filter="superAdmin">Admin</div>
            </div>

            <div id="applicantsSection">
              <table>
                <thead>
                  <tr>
                    <th>Id</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Manage</th>
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
                    echo '<td><a class= "view-link" href="student_details.php?id=' . $customId . '">View</a></td>';

                    echo '</tr>';
                  }
                  ?>
                </tbody>
              </table>
              <div class="pagination"></div>
            </div>

            <div id="osaSection" style="display: none;">
              <table>
                <thead>
                  <tr>
                    <th>Id</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Manage</th>
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
                    $status = $row['is_active'];

                    echo '<tr>';
                    echo '<td>' . $osaId . '</td>';
                    echo '<td><img src="../user_profiles/' . $profile . '" alt="">' . $fullName . '</td>';
                    echo '<td>' . $email . '</td>';
                    echo '<td>' . $role . '</td>';

                    if ($status == 0) {
                      echo '<td> <span class="active-status">Active</span> </td>';
                    } else {
                      echo '<td> <span class="deactivated-status">Deactivated</span> </td>';
                    }
                    echo '<td><a class= "view-link" href="osa_details.php?id=' . $osaId . '">View</a></td>';

                    echo '</tr>';
                  }

                  ?>
                </tbody>
              </table>
            </div>

            <div id="superAdminSection" style="display: none;">
              <table>
                <thead>
                  <tr>
                    <th>Id</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Created At</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  while ($row = mysqli_fetch_assoc($resultsuperAdmin)) {
                    $superAdminId = $row['super_admin_id'];
                    $userName = $row['username'];
                    $password = $row['password'];
                    $profile = $row['profile'];
                    $createdAt = $row['created_at'];



                    echo '<tr>';
                    echo '<td>' . $superAdminId . '</td>';
                    echo '<td><img src="../user_profiles/' . $profile . '" alt="">' . $userName . '</td>';
                    echo '<td>' . $password . '</td>';
                    echo '<td>' . formatExpireDate($createdAt) . '</td>';
                    echo '</tr>';
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </section>
        </div>
    </main>

    <script src="js/applicants.js"></script>
    <script>
      document.addEventListener("click", function(event) {
        if (event.target.classList.contains("reg-status-button")) {
          const button = event.target;
          const superAdminId = button.getAttribute("data-id");
          const currentStatus = parseInt(button.getAttribute("data-status"));

          // Display a confirmation dialog
          Swal.fire({
            title: currentStatus === 1 ? "Activate Account" : "Deactivate Account",
            text: currentStatus === 1 ? "Are you sure you want to activate this superAdmin user's account?" : "Are you sure you want to deactivate this superAdmin user's account?",
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
                url: "update_reg_status.php", // Replace with the PHP file to handle status updates for superAdmin users
                type: "POST",
                data: {
                  superAdminId: superAdminId,
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
                    Swal.fire("Account Updated", "The superAdmin user's account has been updated.", "success");
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
        const superAdminSection = document.getElementById("superAdminSection");
        const createUserButton = document.getElementById("createUserButton"); // Get the "Create User" button

        // Initially, show Applicants section by default
        applicantsSection.style.display = "block";
        createUserButton.style.display = "none"; // Hide the button by default

        filterButtons.forEach(button => {
          button.addEventListener("click", function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove("active"));
            // Add active class to the clicked button
            button.classList.add("active");

            // Hide all sections
            applicantsSection.style.display = "none";
            osaSection.style.display = "none";
            superAdminSection.style.display = "none";

            const selectedFilter = button.getAttribute("data-filter");

            // Show the selected section based on the filter
            if (selectedFilter === "applicants") {
              applicantsSection.style.display = "block";
              createUserButton.style.display = "none"; // Hide the button for applicants
            } else if (selectedFilter === "osa") {
              osaSection.style.display = "block";
              createUserButton.style.display = ""; // Show the button for OSA
            } else if (selectedFilter === "superAdmin") {
              superAdminSection.style.display = "block";
              createUserButton.style.display = "none"; // Hide the button for superAdmin
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