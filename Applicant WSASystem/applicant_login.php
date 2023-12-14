<?php
include '../include/connection.php';
session_name("ApplicantSession");
session_start();

if (isset($_POST['submit'])) {
    $emailOrStudentNum = mysqli_real_escape_string($dbConn, $_POST['email_or_student_num']);
    $password = mysqli_real_escape_string($dbConn, $_POST['password']);

    $query = mysqli_prepare($dbConn, "SELECT * FROM tbl_user WHERE (email = ? OR student_num = ?)");
    mysqli_stmt_bind_param($query, "ss", $emailOrStudentNum, $emailOrStudentNum);
    mysqli_stmt_execute($query);
    $result = mysqli_stmt_get_result($query);

    if (isset($_POST['remember_me'])) {
        setcookie('remember_user', $emailOrStudentNum, time() + (30 * 24 * 3600), '/');
        setcookie('remember_password', $password, time() + (30 * 24 * 3600), '/');
    }

    if ($row = mysqli_fetch_assoc($result)) {
        if ($password === $row['password']) {
            $_SESSION['user_id'] = $row['user_id'];
            $successMessage = 'Login Successfully';
        } else {
            $incorrectMessage = 'Please double-check your Learner Reference Number and try again.';
        }
    } else {
            $notRegisteredMessage = 'Sorry, we couldn\'t find your account.';
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
    <link rel="stylesheet" href="css/applicant_login.css">

    <title>ApplicantModule</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</head>

<body>

    <div class="background">
        <div class="info-logo">
            <div class="logo">
                <img class="img-responsive" src="../img/headerisu.png" alt="Header">
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
            if (isset($incorrectMessage)) {
                echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Incorrect LRN",
                    text: "' . $incorrectMessage . '",
                    showConfirmButton: false,
                    timer: 2000
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "applicant_login.php";
                    }
                });
            </script>';
            }

            if (isset($notRegisteredMessage)) {
                echo '<script>
              Swal.fire({
                  icon: "error",
                  title: "Account not registered",
                  text: "' . $notRegisteredMessage . '",
                  showConfirmButton: false,
                  timer: 2000
              }).then((result) => {
                  if (result.isConfirmed) {
                      window.location.href = "applicant_login.php";
                  }
              });
          </script>';
            }

            if (isset($successMessage)) {
                echo '<script>
            Swal.fire({
                position: "center",
                icon: "success",
                title: "' . $successMessage . '",
                showConfirmButton: false,
                timer: 1500
            }).then((result) => {
                if (result.dismiss === Swal.DismissReason.timer) {
                    window.location.href = "applicant_dashboard.php";
                }
            });
        </script>';
            }
            if (isset($notVerifiedMessage)) {
                echo '<script>
                      Swal.fire({
                          icon: "error",
                          title: "Error",
                          text: "' . $notVerifiedMessage . '",
                          showConfirmButton: false,
                          timer: 2000
                      }).then((result) => {
                          if (result.isConfirmed) {
                              window.location.href = "applicant_login.php";
                          }
                      });
                      </script>';
            }
            ?>
            <div class="page-links">
                <a href="applicant_login.php" class="active">Login</a>
            </div>
            <div class="input-container">
                <span class="input-container-addon">
                    <i class="fa fa-envelope-square"></i>
                </span>
                <input class="input-style" name="email_or_student_num" type="text" placeholder="Email or Student Number" required <?php if (isset($_POST['email_or_student_num'])) echo 'value="' . htmlspecialchars($_POST['email_or_student_num']) . '"'; ?> value="<?php echo isset($_COOKIE['remember_user']) ? htmlspecialchars($_COOKIE['remember_user']) : ''; ?>">
            </div>


            <div class="input-container">
                <span class="input-container-addon">
                    <i class="fa fa-lock"></i>
                </span>
                <input class="input-style" id="password" name="password" type="password" placeholder="LRN's number" required <?php if (isset($_POST['password'])) echo 'value="' . htmlspecialchars($_POST['password']) . '"'; ?> value="<?php echo isset($_COOKIE['remember_password']) ? htmlspecialchars($_COOKIE['remember_password']) : ''; ?>">
            </div>

            <label class="show-password" for="show-password">
                <input type="checkbox" id="show-password"> Show LRN's Number
            </label>
            <label class="show-password" for="remember-me">
                <input type="checkbox" id="remember-me" name="remember_me"> Remember Me
            </label>


            <div class="button">
                <button type="submit" name="submit" class="submit">Login</button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById("show-password").addEventListener("change", function() {
            var passwordInput = document.getElementById("password");
            if (this.checked) {
                passwordInput.type = "text";
            } else {
                passwordInput.type = "password";
            }
        });
    </script>
</body>

</html>