<?php
include '../include/connection.php';
session_name("OsaSession");
session_start();

if (isset($_POST['submit'])) {
    $usernameOrEmail = mysqli_real_escape_string($dbConn, $_POST['username_or_email']);
    $password = mysqli_real_escape_string($dbConn, $_POST['password']);

    $stmt = mysqli_prepare($dbConn, "SELECT * FROM tbl_admin WHERE (username = ? OR email = ?) AND role = 'OSA'");
    mysqli_stmt_bind_param($stmt, "ss", $usernameOrEmail, $usernameOrEmail);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        if ($row['is_active'] == 0) {
            if (password_verify($password, $row['password'])) {
                $_SESSION["admin_id"] = $row["admin_id"];
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
  <link rel="stylesheet" href="css/osa_login.css">

  <title>AdminModule</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "'.$incorrectMessage.'",
                    showConfirmButton: false,
                    timer: 2000
                })
            </script>';
              }

              if(isset($notRegistered)){
                echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "'.$notRegistered.'",
                    showConfirmButton: false,
                    timer: 2000
                })
            </script>';
              }

              if(isset($successMessage)){
                echo '<script>
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "'.$successMessage.'",
                    showConfirmButton: false,
                    timer: 1500
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.timer) {
                        window.location.href = "osa_dashboard.php";
                    }
                });
            </script>';
              }
              
              if (isset($deactivatedMessage)) {
                echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Account Deactivated",
                    text: "'.$deactivatedMessage.'",
                    showConfirmButton: false,
                    timer: 2000
                })
            </script>';
              }
             ?>
            <div class="page-links">
                <a href="osa_login.php" class="active">Login</a>
                <a href="osa_register.php">Register</a>
            </div>
            <div class="input-container">
                <span class="input-container-addon">
                    <i class="fa fa-user"></i>
                </span>                                                                             
                <input class="input-style" name="username_or_email" type="text" placeholder="Username or Email" required>
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
