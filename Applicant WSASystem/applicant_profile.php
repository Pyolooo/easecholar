<?php
include '../include/connection.php';
session_name("ApplicantSession");
session_start();
$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
  header('location:applicant_login.php');
  exit();
}

if (isset($_GET['logout'])) {
  unset($user_id);
  session_destroy();
  header('location:applicant_login.php');
  exit();
}

$image_path = '';


$sql = "SELECT * FROM tbl_user WHERE user_id = ?";
$stmt = $dbConn->prepare($sql);

if ($stmt) {
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();

    $userId = $row['user_id'];
    $fullName = $row['full_name'];
    $studentNum = $row['student_num'];
    $lrnNum = $row['password'];
    $email = $row['email'];
    $image_path = $row['image'];
  }

  $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $full_name = $_POST['full_name'];
  $email = $_POST['email'];
  $password = $_POST['password'];

  // Check if $_POST['student_num'] is set before trying to access it
  if (isset($_POST['student_num'])) {
    $student_num = $_POST['student_num'];
  } else {
    $student_num = ''; // You can set a default value or leave it empty as needed
  }

  $errors = array();

  if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email address is required.';
  }

  $profile = $_FILES['profile'];

  if (!empty($profile['name'])) {
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $file_extension = strtolower(pathinfo($profile['name'], PATHINFO_EXTENSION));

    if (!in_array($file_extension, $allowed_extensions)) {
      $errors[] = 'Invalid file type. Allowed types: jpg, jpeg, png, gif';
    }

    $file_name = uniqid('profile_') . '.' . $file_extension;
    $upload_directory = $_SERVER['DOCUMENT_ROOT'] . '/user_profiles/' . $file_name;

    if (move_uploaded_file($profile['tmp_name'], $upload_directory)) {
      // Store only the file name in the database
      $image_path = $file_name;
    } else {
      $errors[] = 'File upload failed.';
    }
  }

  // Update the user's profile in the database
  $sql = "UPDATE tbl_user SET full_name = ?, email = ?, student_num = ?, password = ?, image = ? WHERE user_id = ?";
  $stmt = $dbConn->prepare($sql);

  if ($stmt) {
    $stmt->bind_param("sssssi", $full_name, $email, $student_num, $password, $image_path, $user_id);

    if ($stmt->execute()) {
      $success_message = "Profile updated successfully.";
    } else {
      $errors[] = "Profile update failed.";
    }

    $stmt->close();
  } else {
    $errors[] = "Statement preparation failed: " . $dbConn->error;
  }
      
    } else {
      $errors[] = 'File upload failed.';
    }


  if (empty($errors)) {
    // Construct the updated profile image HTML
    $updatedProfileImageHTML = '';
    if (!empty($image_path)) {
      $updatedProfileImageHTML = "<img src='../user_profiles/{$image_path}' width='250' height='250'>";
    }
  }
$dbConn->close();
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Boxicons -->
  <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

  <link rel="stylesheet" href="css/applicant_profile.css">
  <title>Your Profile</title>
</head>

<body>

  <form method="POST" action="" enctype="multipart/form-data">
    <section>

    <?php
    if (isset($success_message)) {
                echo '<script>
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "' . $success_message . '",
                    showConfirmButton: false,
                    timer: 2500
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.timer) {
                        window.location.href = "applicant_dashboard.php";
                    }
                });
                </script>';
            }
            ?>

      <h2 style="font-size: 25px; color: #636363">PROFILE</h2>
      <div class="profile-container">
        <div class="container">
          <div class="info-container">

            <div class="label-container">
              <i class='bx bxs-user-rectangle'></i>
              <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($fullName); ?>">
            </div>

            <div class="label-container">
              <i class='bx bxs-envelope'></i>
              <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
            </div>
            <span id="email-error" style="color: red; text-align: center;"></span>

            <div class="label-container">
              <i class='bx bxs-id-card'></i>
              <input type="text" id="id_number" name="student_num" value="<?php echo htmlspecialchars($studentNum); ?>">
            </div>

            <div class="label-container">
              <i class='bx bxs-user-detail'></i>
              <input type="text" id="lrn_number" name="password" value="<?php echo htmlspecialchars($lrnNum); ?>">
            </div>

          </div>
          <div class="image-container">
            <div id="updated-profile-image">
              <?php
              if (!empty($updatedProfileImageHTML)) {
                echo $updatedProfileImageHTML;
              } elseif (!empty($image_path)) {
                echo "<img src='../user_profiles/{$image_path}' width='250' height='250'>";
              }
              ?>
            </div>

            <div class="round">
              <input type="file" id="profile" name="profile" accept=".jpg, .jpeg, .png">
              <i class='bx bxs-camera'></i>
            </div>

          </div>
        </div>
        <div class="update-container">
          <button class="cancel-button" type="button" onclick="window.location.href='applicant_dashboard.php'">Back</button>
          <button class="update-button" type="submit" value="Update Profile">Update </button>
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
      document.getElementById('email').addEventListener('blur', function() {
    var emailInput = this.value;
    var emailError = document.getElementById('email-error');

    // Check if the email input ends with "@gmail.com"
    if (!emailInput.toLowerCase().endsWith('@gmail.com')) {
      emailError.textContent = 'Invalid Gmail address';
      this.focus();
    } else {
      emailError.textContent = '';
    }
  });

  // Add event listener to the form submission
  document.querySelector('form').addEventListener('submit', function(event) {
    var emailInput = document.getElementById('email').value;

    // Check the email format again before allowing the form submission
    if (!emailInput.toLowerCase().endsWith('@gmail.com')) {
      event.preventDefault(); // Prevent form submission
      document.getElementById('email-error').textContent = 'Invalid Gmail address.';
      document.getElementById('email').focus();
    }
  });

    $(document).ready(function() {
      $('#profile').on('change', function() {
        var formData = new FormData($('form')[0]);

        $.ajax({
          type: 'POST',
          url: 'pload_profile_image.php', // Use the same file for handling updates
          data: formData,
          contentType: false,
          processData: false,
          success: function(response) {
            // Check if the response contains the success message
            if (response.includes('Profile Updated Successfully')) {
              // Display the success message
              $('#success-message').text(response);
              console.log('Response:', response);
              
              // Update form fields with the new data
              var data = JSON.parse(response);
              $('#full_name').val(data.full_name);
              $('#email').val(data.email);
              $('#id_number').val(data.student_num);
              $('#lrn_number').val(data.password);
            }

            // Update the profile image
            $('#updated-profile-image').html(response);
          }
        });

        $('form').submit();
      });
    });
  </script>
</body>

</html>
