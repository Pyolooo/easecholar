<?php
include '../include/connection.php';
session_name("RegistrarSession");
session_start();

if (isset($_POST['submit'])) {
  $username = $_POST["username"];
  $password = $_POST["password"];

  $stmt = mysqli_prepare($dbConn, "SELECT * FROM tbl_registrar WHERE username = ?");
  mysqli_stmt_bind_param($stmt, "s", $username);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($result);

  if ($row) {
      if ($row['is_active'] == 0) {
          if (password_verify($password, $row['password'])) {
              $_SESSION["registrar_id"] = $row["registrar_id"];
              $successMessage = "Login successfully!";
          } else {
              // Wrong password
              $incorrectMessage = 'Incorrect Username or Password!';
          }
      } else {
          // Account is deactivated
          $deactivatedMessage = 'Your account has been deactivated by the admin.' ;
      }
  } else {
      // User not registered
      $notRegistered = "User not registered";
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <!-- My CSS -->
  <link rel="stylesheet" href="css/registrar_login.css">

  <title>RegistrarModule</title>
  <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
</head>
<body>
  
    <div class="background">
        <div class="info-logo">
            <div class="logo">
                <img class="img-responsive" src="/EASE-CHOLAR/headerisu.png" alt="">
            </div>
            <div class="title">
                <span class="text">EASE-CHOLAR: A WEB-BASED SCHOLARSHIP APPLICATION MANAGEMENT SYSTEM</span>
            </div>
        </div>
    </div>

    <div class="log-in">
        <form class="form" action="" method="POST" enctype="multipart/form-data">
            <p class="form-title">ADMIN LOGIN</p>
            <?php
              if(isset($incorrectMessage)){
                echo '<script>
                  window.onload = function() {
                    swal("Incorrect", "'.$incorrectMessage.'", "error");
                  };
                </script>';
              }

              if(isset($notRegistered)){
                echo '<script>
                  window.onload = function() {
                    swal("Error", "'.$notRegistered.'", "error");
                  };
                </script>';
              }

              if(isset($successMessage)){
                echo '<script>
                  window.onload = function() {
                    swal({
                       title: "Success",
                       text: "'.$successMessage.'",
                       icon: "success",
                    }).then(function() {
                       window.location = "index.php";
                    });
                  };
                </script>';
              }
              if (isset($deactivatedMessage)) {
                echo '<script>
                  window.onload = function() {
                    swal("Account Deactivated", "'.$deactivatedMessage.'", "error");
                  };
                </script>';
            }
             ?>
            <div class="page-links">
                <a href="registrar_login.php" class="active">Login</a>
                <a href="registrar_register.php">Register</a>
            </div>
            <div class="input-container">
                <span class="input-container-addon">
                    <i class="fa fa-envelope-square"></i>
                </span>                                                                             
                <input class="input-style" name="username" type="text" placeholder="Enter your Username" required>
            </div>

            <div class="input-container">
                <span class="input-container-addon">
                    <i class="fa fa-lock"></i>
                </span>
                <input class="input-style" name="password" type="password" placeholder="Enter password" required>
            </div>

            <div class="button">
                <button type="submit" name="submit" class="submit">Login</button>
            </div>
        </form>
    </div>
</body>
</html>
