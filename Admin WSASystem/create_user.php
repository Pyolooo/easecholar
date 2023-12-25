<?php
include '../include/connection.php';

if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($dbConn, $_POST['username']);
    $full_name = mysqli_real_escape_string($dbConn, $_POST['full_name']);
    $password = mysqli_real_escape_string($dbConn, $_POST['password']);
    $confirmpassword = mysqli_real_escape_string($dbConn, $_POST['confirmpassword']);
    $profile = $_FILES['profile']['name'];
    $image_size = $_FILES['profile']['size'];
    $image_tmp_name = $_FILES['profile']['tmp_name'];
    $image_folder = $_SERVER['DOCUMENT_ROOT'] . '/user_profiles/' . $profile;

    $role = 'OSA';

    if (strlen($password) < 8) {
        $passwordLengthMessage = "Password must be at least 8 characters long.";
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password) || !preg_match('/[!@#\$%^&*()\-_=+{};:,<.>]/', $password)) {
        $passwordComplexityMessage = "Password must contain at least one uppercase letter, one lowercase letter, one digit, and one special character.";
    }

    if (isset($_FILES['profile']) && $_FILES['profile']['error'] === UPLOAD_ERR_OK) {
        $profile = $_FILES['profile']['name'];
        move_uploaded_file($image_tmp_name, $image_folder);
    } else {
        $profile = 'default-avatar.png';
    }

    $query = mysqli_prepare($dbConn, "SELECT * FROM `tbl_admin` WHERE username = ? OR email = ?");
    mysqli_stmt_bind_param($query, "ss", $username, $email);
    mysqli_stmt_execute($query);
    $result = mysqli_stmt_get_result($query);

    if (mysqli_num_rows($result) > 0) {
        $emailExistsMessage = "Email or Username Already exists!";
    } else {
        if ($password != $confirmpassword) {
            $passwordMismatchMessage = "Confirm password does not match!";
        } elseif ($image_size > 2000000) {
            $largeImageMessage = "Image size is too large!";
        } elseif (isset($passwordLengthMessage) || isset($passwordComplexityMessage)) {
        } else {
            $insert = mysqli_query($dbConn, "INSERT INTO `tbl_admin` (username, full_name, email, password, role, profile) VALUES ('$username', '$full_name', '$email', '$password', '$role', '$profile')") or die('Query failed: ' . mysqli_error($dbConn));

            if ($insert) {
                move_uploaded_file($image_tmp_name, $image_folder);
                $successMessage = 'Registered successfully!';
            } else {
                $registrationFailedMessage = 'Registration failed!';
            }
        }
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
    <link rel="stylesheet" href="css/create_user.css">
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
            <p class="form-title">CREATE ACCOUNT</p>
            <?php
            if (isset($emailExistsMessage)) {
                echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Email Exists",
                    text: "' . $emailExistsMessage . '",
                    showConfirmButton: false,
                    timer: 2000
                })
            </script>';
            }
            if (isset($passwordLengthMessage)) {
                echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Short Password",
                    text: "' . $passwordLengthMessage . '",
                    showConfirmButton: false,
                    timer: 2000
                })
            </script>';
            }
            if (isset($passwordComplexityMessage)) {
                echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Password Not Secure",
                    text: "' . $passwordComplexityMessage . '",
                    showConfirmButton: true,
                   
                })
            </script>';
            }
            if (isset($passwordMismatchMessage)) {
                echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Password Mismatch",
                    text: "' . $passwordMismatchMessage . '",
                    showConfirmButton: false,
                    timer: 2000
                })
            </script>';
            }
            if (isset($largeImageMessage)) {
                echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Large Image",
                    text: "' . $largeImageMessage . '",
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
                    timer: 2500
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.timer) {
                        window.location.href = "manage_users.php";
                    }
                });
                </script>';
            }
            if (isset($registrationFailedMessage)) {
                echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Registration Failed",
                    text: "' . $registrationFailedMessage . '",
                    showConfirmButton: false,
                    timer: 2000
                })
            </script>';
            }
            ?>
            <div class="page-links">
                <a href="create_user.php" class="active">OSA</a>

            </div>

            <div class="selected-image-container">
                <div class="image-container">
                    <img id="selected-image" src="../user_profiles/default-avatar.png" alt="Selected Image">
                </div>
                <div class="round">
                    <input class="input-style" type="file" id="image-input" name="profile" placeholder="Profile pic" accept="image/jpg, image/jpeg, image/png">
                    <i class='bx bxs-camera'></i>
                </div>
            </div>

            <div class="input-container">
                <span class="input-container-addon">
                    <i class="fa fa-user"></i>
                </span>

                <input class="input-style" id="full_name" type="text" name="full_name" placeholder="First Name | Middle Name | Last Name" required <?php if (isset($_POST['full_name'])) echo 'value="' . htmlspecialchars($_POST['full_name']) . '"'; ?>>
            </div>

            <div class="input-container">
                <span class="input-container-addon">
                    <i class="fa fa-user"></i>
                </span>
                <input class="input-style" id="username" type="text" name="username" placeholder="Enter username" required <?php if (isset($_POST['username'])) echo 'value="' . htmlspecialchars($_POST['username']) . '"'; ?>>
            </div>

            <div class="input-container">
                <span class="input-container-addon">
                    <i class="fa fa-lock"></i>
                </span>
                <input class="input-style" id="password" type="password" name="password" placeholder="Enter password" required>
            </div>

            <div class="input-container">
                <input class="input-style" type="password" id="confirmpassword" name="confirmpassword" placeholder="Confirm password" required>
            </div>


            <div class="button">
                <button type="submit" name="submit" class="submit">Submit</button>
            </div>
        </form>
    </div>

    <script>
        // Function to display the selected image and control label visibility
        function displaySelectedImage() {
            var input = document.getElementById('image-input');
            var selectedImage = document.getElementById('selected-image');
            var imageLabel = document.getElementById('image-label');

            input.addEventListener('change', function() {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        selectedImage.src = e.target.result;
                        selectedImage.style.display = 'block'; // Show the selected image
                        imageLabel.style.display = 'none'; // Hide the label
                    };

                    reader.readAsDataURL(input.files[0]);
                } else {
                    selectedImage.src = ""; // Clear the selected image if no file is selected
                    selectedImage.style.display = 'none'; // Hide the selected image
                    imageLabel.style.display = 'block'; // Show the label
                }
            });
        }

        // Call the function to display the selected image
        displaySelectedImage();
    </script>
</body>

</html>