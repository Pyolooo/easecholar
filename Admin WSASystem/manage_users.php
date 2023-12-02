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

$rowsPerPage = isset($_GET['rowsPerPage']) ? intval($_GET['rowsPerPage']) : 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = max(0, ($currentPage - 1) * $rowsPerPage);

$itemsPerPage = $rowsPerPage;

$sqlUser = "SELECT * FROM tbl_user LIMIT $offset, $itemsPerPage";
$resultUser = mysqli_query($dbConn, $sqlUser);

if (!$resultUser) {
  die("Query failed: " . mysqli_error($dbConn));
}

// Count the total number of rows
$totalRows = mysqli_num_rows(mysqli_query($dbConn, "SELECT * FROM tbl_user"));

// Calculate the total number of pages
$totalPages = ceil($totalRows / $itemsPerPage);

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
        <span class="school-name">EASE-CHOLAR</span>
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
          <div class="rowsPerpage">
            <label for="rowsPerPage">Number of Rows:</label>
            <select id="rowsPerPage" onchange="changeRowsPerPage(<?php echo $currentPage; ?>)">
              <option value="10" <?php if ($rowsPerPage == 10) echo 'selected'; ?>>10</option>
              <option value="20" <?php if ($rowsPerPage == 20) echo 'selected'; ?>>20</option>
              <option value="50" <?php if ($rowsPerPage == 50) echo 'selected'; ?>>50</option>
              <option value="100" <?php if ($rowsPerPage == 100) echo 'selected'; ?>>100</option>
            </select>
          </div>

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
                  $number = 1;
                  while ($row = mysqli_fetch_assoc($resultUser)) {
                    $customId = $row['custom_id'];
                    $fullName = $row['full_name'];
                    $email = $row['email'];
                    $image = $row['image'];



                    echo '<tr>';
                    echo '<td>' . $number . '</td>';
                    echo '<td><img src="../user_profiles/' . $image . '" alt="">' . $fullName . '</td>';
                    echo '<td>' . $email . '</td>';
                    echo '<td><a class= "view-link" href="student_details.php?id=' . $customId . '">View</a></td>';

                    echo '</tr>';

                    $number++;
                  }
                  ?>
                </tbody>
              </table>
              <div class="entries-range">
                Showing <?php echo min(($currentPage - 1) * $rowsPerPage + 1, $totalRows); ?> to <?php echo min($currentPage * $rowsPerPage, $totalRows); ?> of <?php echo $totalRows; ?> entries
              </div>
              <div class="pagination">
                <?php
                if ($currentPage > 1) {
                  echo '<button class="pagination-button" data-page="' . ($currentPage - 1) . '">&lt; Prev</button>';
                }

                for ($i = 1; $i <= $totalPages; $i++) {
                  if ($totalPages > 5 && $i > 2 && $i < ($totalPages - 1) && ($i < ($currentPage - 1) || $i > ($currentPage + 1))) {
                    if ($i == 3 && $currentPage > 4) {
                      echo '<span class="pagination-ellipsis">...</span>';
                    }
                    continue;
                  }

                  echo '<button class="pagination-button' . ($currentPage == $i ? ' active' : '') . '" data-page="' . $i . '">' . $i . '</button>';
                }

                if ($totalPages > 5 && $currentPage < ($totalPages - 2)) {
                  echo '<span class="pagination-ellipsis">...</span>';
                }

                if ($totalRows > $itemsPerPage && $currentPage < $totalPages) {
                  echo '<button class="pagination-button" data-page="' . ($currentPage + 1) . '">Next &gt;</button>';
                }
                ?>
              </div>

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
    <script src="js/admin_logout.js"></script>
    <script src="js/toggle_sidebar.js"></script>
    
    <script>
      function changeRowsPerPage(page) {
        const selectedRows = document.getElementById('rowsPerPage').value;
        window.location.href = 'manage_users.php?rowsPerPage=' + selectedRows + '&page=' + page;
      }



      document.addEventListener("DOMContentLoaded", function() {
        const paginationButtons = document.querySelectorAll('.pagination-button');

        paginationButtons.forEach(button => {
          button.addEventListener('click', function() {
            const page = this.getAttribute('data-page');
            changeRowsPerPage(page);
          });
        });
      });


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
              $.ajax({
                url: "update_reg_status.php",
                type: "POST",
                data: {
                  superAdminId: superAdminId,
                  status: currentStatus === 1 ? 0 : 1
                },
                success: function(response) {
                  if (response === "success") {
                    button.textContent = currentStatus === 1 ? "Activate" : "Deactivate";
                    button.setAttribute("data-status", currentStatus === 1 ? 0 : 1);
                    button.style.backgroundColor = currentStatus === 1 ? "green" : "red";
                    Swal.fire("Account Updated", "The superAdmin user's account has been updated.", "success");
                  } else {
                    Swal.fire("Error", "Failed to update the account. Please try again.", "error");
                  }
                },
                error: function() {
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
        const createUserButton = document.getElementById("createUserButton");

        applicantsSection.style.display = "block";
        createUserButton.style.display = "none";

        filterButtons.forEach(button => {
          button.addEventListener("click", function() {
            filterButtons.forEach(btn => btn.classList.remove("active"));
            button.classList.add("active");

            // Hide all sections
            applicantsSection.style.display = "none";
            osaSection.style.display = "none";
            superAdminSection.style.display = "none";

            const selectedFilter = button.getAttribute("data-filter");

            // Show the selected section based on the filter
            if (selectedFilter === "applicants") {
              applicantsSection.style.display = "block";
              createUserButton.style.display = "none";
            } else if (selectedFilter === "osa") {
              osaSection.style.display = "block";
              createUserButton.style.display = ""; 
            } else if (selectedFilter === "superAdmin") {
              superAdminSection.style.display = "block";
              createUserButton.style.display = "none"; 
            }
          });
        });
      });




      $(document).ready(function() {

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