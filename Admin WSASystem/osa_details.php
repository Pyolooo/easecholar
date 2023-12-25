<?php
include '../include/connection.php';

// Check if the ID parameter is set in the URL
if (isset($_GET['id'])) {
  $osaId = mysqli_real_escape_string($dbConn, $_GET['id']);

  // Fetch OSA user details based on the provided ID
  $sql = "SELECT * FROM tbl_admin WHERE admin_id = '$osaId' AND role = 'OSA'";
  $result = mysqli_query($dbConn, $sql);

  if ($result && mysqli_num_rows($result) > 0) {
    $osaUser = mysqli_fetch_assoc($result);
  } else {
    echo '<p>No OSA user found with the provided ID.</p>';
  }
} else {
  echo '<p>No user ID provided.</p>';
}

$sqlAdmin = "SELECT * FROM tbl_admin WHERE role = 'OSA'";
$resultAdmin = mysqli_query($dbConn, $sqlAdmin);

if (!$resultAdmin) {
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
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <link rel="stylesheet" href="css/osa_details.css">
  <title>Your Profile</title>
</head>

<body>
  <form method="POST" action="" enctype="multipart/form-data">

    <section>
      <h2 style="font-size:25px; text-align: center; color: #636363">OSA Information</h2>
      <div class="profile-container">
        <div class="container">
          <div class="info-container">

            <div class="label-container">
            <i class='bx bxs-user-rectangle' ></i>
              <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($osaUser['full_name']); ?>" disabled>
            </div>

            <div class="label-container">
            <i class='bx bxs-user-detail' ></i>
              <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($osaUser['username']); ?>" disabled>
            </div>

            <div class="label-container">
            <i class='bx bxs-envelope'></i>
              <input type="email" id="email" name="email" placeholder="------------" value="<?php echo htmlspecialchars($osaUser['email']); ?>" disabled>
            </div>

            <div class="label-container">
            <i class='bx bxs-lock-alt' ></i>
              <input type="text" id="phone_num" name="phone_num" value="<?php echo htmlspecialchars($osaUser['password']); ?>">
            </div>

            <div class="label-container">
            <i class='bx bxs-phone' ></i>
              <input type="text" id="phone_num" placeholder="------------" name="phone_num" value="<?php echo htmlspecialchars($osaUser['phone_num']); ?>" disabled>
            </div>
            <div class="label-container">
            <i class='bx bxs-time' ></i>
              <input type="text" id="username" name="username" value="<?php echo htmlspecialchars(formatExpireDate($osaUser['created_at'])); ?>" disabled>
            </div>

          </div>
          <div class="image-container">
            <div id="updated-profile-image">
              <?php
              if (!empty($osaUser['profile'])) {
                echo "<img src='../user_profiles/" . $osaUser['profile'] . "' alt='User Image' width='250' height='250'>";
              }
              ?>
            </div>
          </div>

          


        </div>
        <div class="update-container">
          <button class="cancel-button" type="button" onclick="window.location.href='manage_users.php'">Back</button>
          <?php
          $status = $osaUser['is_active'];

          if ($status == 0) {
            echo '<button type="button" class="osa-status-button" data-id="' . $osaId . '" data-status="0">Activate</button>';
          } else {
            echo '<button type="button" class="osa-status-button" data-id="' . $osaId . '" data-status="1" style="background-color: red; border: none;">Deactivate</button>';
          }
          ?>
        </div>
        <?php
        if (isset($success_message)) {
          echo '<p style="color: green; text-align:center">' . $success_message . '</p>';
        }
        ?>
      </div>
    </section>
  </form>

  <script>
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
  </script>
</body>

</html>