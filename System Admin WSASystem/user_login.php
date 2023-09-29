<?php
include 'connection.php';
session_start();

if (isset($_POST['submit'])) {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $row = mysqli_fetch_assoc(mysqli_query($dbConn, "SELECT * FROM tbl_user WHERE email = '$email'"));

    if ($row && password_verify($password, $row['password'])) {
        $_SESSION["user_id"] = $row["user_id"];
        $successMessage = "Login successfully!";
    } elseif (isset($row)) {
        // User exists, but wrong password
        $incorrectMessage = 'Incorrect Email or Password!';
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
  <link rel="stylesheet" href="user_login.css">

  <title>AdminModule</title>
  <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
</head>
<body>
  
    <div class="background">
        <div class="info-logo">
            <div class="logo">
                <img class="img-responsive" src="img/headerisu.png" alt="">
            </div>
            <div class="title">
                <span class="text">EASE-CHOLAR: A WEB-BASED SCHOLARSHIP APPLICATION MANAGEMENT SYSTEM</span>
            </div>
        </div>
    </div>

    <div class="log-in">
        <form class="form" action="" method="POST" enctype="multipart/form-data">
            <p class="form-title">STUDENT LOGIN</p>
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
             ?>
            <div class="page-links">
                <a href="user_login.php" class="active">Login</a>
                <a href="user_register.php">Register</a>
            </div>
            <div class="input-container">
                <span class="input-container-addon">
                    <i class="fa fa-envelope-square"></i>
                </span>                                                                             
                <input class="input-style" name="email" type="email" placeholder="Enter your email" required>
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

            <p class="pass-link">Forget Password?<a href="change_password.php">Reset Password</a></p>
        </form>
    </div>
</body>
</html>
