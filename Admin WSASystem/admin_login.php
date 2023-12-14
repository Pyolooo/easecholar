<?php
include '../include/connection.php';
session_name("AdminSession");
session_start();

if (isset($_POST['submit'])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $row = mysqli_fetch_assoc(mysqli_query($dbConn, "SELECT * FROM tbl_super_admin WHERE username = '$username'"));

    if (isset($_POST['remember_me'])) {
        setcookie('admin_remember_user', $username, time() + (30 * 24 * 3600), '/');
        setcookie('admin_remember_password', $password, time() + (30 * 24 * 3600), '/');
    }

    if ($row && $password == $row['password']) {
        $_SESSION["super_admin_id"] = $row["super_admin_id"];
        $successMessage = "Login successfully!";
    } elseif (isset($row)) {
        $incorrectMessage = 'Please ensure you entered the correct password and try again.';
    } else {
        $notRegistered = "Please ensure you entered the correct login credentials and try again";
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
    <link rel="stylesheet" href="css/admin_login.css">

    <title>AdminModule</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</head>

<body>

    <div class="background">
        <div class="info-logo">
            <div class="logo">
                <img class="img-responsive" src="../img/headerisu.png" alt="">
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
            if (isset($incorrectMessage)) {
                echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "' . $incorrectMessage . '",
                    showConfirmButton: false,
                    timer: 2000
                })
            </script>';
            }


            if (isset($notRegistered)) {
                echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "' . $notRegistered . '",
                    showConfirmButton: false,
                    timer: 2000
                })
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
                        window.location.href = "admin_dashboard.php";
                    }
                });
            </script>';
            }
            ?>
            <div class="page-links">
                <a href="user_login.php" class="active">Login</a>
            </div>
            <div class="input-container">
                <span class="input-container-addon">
                    <i class="fa fa-envelope-square"></i>
                </span>
                <input class="input-style" name="username" type="text" placeholder="Enter your Username" required <?php if (isset($_POST['username'])) echo 'value="' . htmlspecialchars($_POST['username']) . '"'; ?> value="<?php echo isset($_COOKIE['admin_remember_user']) ? htmlspecialchars($_COOKIE['admin_remember_user']) : ''; ?>">
            </div>

            <div class="input-container">
                <span class="input-container-addon">
                    <i class="fa fa-lock"></i>
                </span>
                <input class="input-style" id="password" name="password" type="password" placeholder="Enter password" required <?php if (isset($_POST['password'])) echo 'value="' . htmlspecialchars($_POST['password']) . '"'; ?> value="<?php echo isset($_COOKIE['admin_remember_password']) ? htmlspecialchars($_COOKIE['admin_remember_password']) : ''; ?>">
            </div>
            <label class="show-password" for="show-password">
                <input type="checkbox" id="show-password"> Show Password
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