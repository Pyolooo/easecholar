<?php
include '../include/connection.php';

if (isset($_GET['id'])) {
  $customId = $_GET['id'];

  $sql = "SELECT * FROM tbl_user WHERE custom_id = ?";
  $stmt = $dbConn->prepare($sql);
  $stmt->bind_param("s", $customId);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    $userId = $row['user_id'];
    $fullName = $row['full_name'];
    $studentNum = $row['student_num'];
    $lrnNum = $row['password'];
    $email = $row['email'];
    $image = $row['image'];
  } else {
    echo "User not found.";
  }

  $stmt->close();
} else {
  echo "Invalid request.";
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Boxicons -->
  <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


  <link rel="stylesheet" href="css/student_details.css">
  <title>Your Profile</title>
</head>

<body>
  <form method="POST" action="" enctype="multipart/form-data">

    <section>
      <h2 style="font-size: 25px; text-align: center; color: #636363">Student Information</h2>
      <div class="profile-container">
        <div class="container">
          <div class="info-container">

            <div class="label-container">
            <i class='bx bxs-user-rectangle' ></i>
              <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($fullName); ?>" disabled>
            </div>

            <div class="label-container">
            <i class='bx bxs-envelope'></i>
              <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" disabled>
            </div>

            <div class="label-container">
            <i class='bx bxs-id-card'></i>
              <input type="text" id="id_number" name="id_number" value="<?php echo htmlspecialchars($studentNum); ?>" disabled>
            </div>

            <div class="label-container">
            <i class='bx bxs-user-detail' ></i>
              <input type="text" id="lrn_number" name="lrn_number" value="<?php echo htmlspecialchars($lrnNum); ?>" disabled>
            </div>

          </div>
          <div class="image-container">
            <div id="updated-profile-image">
              <?php
              if (!empty($image)) {
                echo "<img src='../user_profiles/{$image}' width='250' height='250'>";
              }
              ?>
            </div>

          </div>
        </div>
        <div class="update-container">
          <button class="cancel-button" type="button" onclick="window.location.href='manage_users.php'">Back</button>
         
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
    $(document).ready(function() {
      $('#profile').on('change', function() {
        var formData = new FormData($('form')[0]);

        $.ajax({
          type: 'POST',
          url: 'update_profile.php',
          data: formData,
          contentType: false,
          processData: false,
          success: function(response) {
            if (response.includes('Profile Updated Successfully')) {
              $('#success-message').text(response);
            }

            $('#updated-profile-image').html(response);
          }
        });

        $('form').submit();
      });
    });
  </script>
</body>

</html>