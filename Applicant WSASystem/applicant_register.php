<?php
require 'connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST['submit'])) {
    $full_name = mysqli_real_escape_string($dbConn, $_POST['full_name']);
    $email = mysqli_real_escape_string($dbConn, $_POST['email']);
    $student_num = mysqli_real_escape_string($dbConn, $_POST['student_num']);
    $password = mysqli_real_escape_string($dbConn, $_POST['password']);
    $image = $_FILES['image']['name'];
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_type = $_FILES['image']['type'];
    $maxImageSize = 1024 * 1024;

    $query = mysqli_prepare($dbConn, "SELECT * FROM `tbl_user` WHERE email = ? OR student_num = ?");
    mysqli_stmt_bind_param($query, "ss", $email, $student_num);
    mysqli_stmt_execute($query);
    $result = mysqli_stmt_get_result($query);

    if (mysqli_num_rows($result) > 0) {
        $userExistsMessage = 'Email or student number already exists!';
    } elseif (!in_array($image_type, array('image/jpeg', 'image/jpg', 'image/png'))) {
        $imageTypeMessage = 'Only JPEG, JPG, and PNG images are allowed.';
    } elseif ($image_size > $maxImageSize) {
        $largeImageMessage = 'Image size is too large!';
    } else {
        $filename = uniqid() . '_' . $image;

        $targetDirectory = $_SERVER['DOCUMENT_ROOT'] . '/EASE-CHOLAR/user_profiles/';

        $targetPath = $targetDirectory . $filename;

        if (move_uploaded_file($image_tmp_name, $targetPath)) {
            if (file_exists($targetPath)) {

                $custom_id = 'ISU_' . sprintf("%03d", rand(1, 999));

                // Generate a verification token
                $verification_token = bin2hex(random_bytes(16));

                $insert = mysqli_prepare($dbConn, "INSERT INTO `tbl_user` (custom_id, full_name, student_num, email, password, image, verification_token) VALUES(?, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($insert, "sssssss", $custom_id, $full_name, $student_num, $email, $password, $filename, $verification_token);

                if (mysqli_stmt_execute($insert)) {
                    $base_url = '';

                    if ($_SERVER['HTTP_HOST'] === 'localhost') {

                        $base_url = 'http://localhost/EASE-CHOLAR/Applicant%20WSASystem/';
                    } else {

                        $base_url = 'https://easecholarship.me/';
                    }

                    $verification_link = $base_url . 'verify.php?email=' . urlencode($email) . '&token=' . $verification_token;


                    require '/wamp64/www/EASE-CHOLAR/PHPMailer-master/src/Exception.php';
                    require '/wamp64/www/EASE-CHOLAR/PHPMailer-master/src/PHPMailer.php';
                    require '/wamp64/www/EASE-CHOLAR/PHPMailer-master/src/SMTP.php';

                    $mail = new PHPMailer(true);

                    try {
                        // SMTP server settings
                        $mail->SMTPDebug = 0;
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'easecholar@gmail.com';
                        $mail->Password = 'benz pupq lkxj amje';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
                        $mail->Port = 587;
        
        

                        // Sender and recipient
                        $mail->setFrom('easecholar@gmail.com', 'EASE-CHOLAR');
                        $mail->addAddress($email, $full_name);

                        // Email content
                        $mail->isHTML(true);
                        $mail->Subject = 'Verify Your Email';
                        $mail->Body = "Click the following link to verify your email: $verification_link";

                        // Send the email
                        $mail->send();

                        $successMessage = 'Registered successfully! Please check your email to verify your account.';
                    } catch (Exception $e) {
                        unlink($targetPath);
                        $registrationFailedMessage = 'Registration succeeded, but the email could not be sent. Please contact support.';
                    }
                } else {
                    unlink($targetPath);
                    $registrationFailedMessage = 'Registration failed!';
                }
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
    <link rel="stylesheet" href="css/applicant_register.css">

    <title>ApplicantModule</title>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <style>
        .selected-image-container {
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .image-container {
            display: flex;
            justify-content: center;

        }

        #image-label {
            display: block;
            color: white;
            font-style: italic;

        }

        #selected-image {
            width: 60px;
            height: 60px;
            border-radius: 30px;
        }
    </style>
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
            <p class="form-title">STUDENT REGISTRATION</p>
            <?php
            if (isset($userExistsMessage)) {
                echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Email Exists",
                    text: "' . $userExistsMessage . '",
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

            if (isset($imageTypeMessage)) {
                echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Invalid Image Type",
                    text: "' . $imageTypeMessage . '",
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
                        window.location.href = "applicant_login.php";
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
                <a href="applicant_login.php">Login </a>
                <a href="applicant_register.php" class="active">Register</a>
            </div>

            <div class="selected-image-container">
                <div class="image-container">
                    <img id="selected-image" src="/EASE-CHOLAR/default-avatar.png" alt="Selected Image">
                </div>
                <label id="image-label">*Select your profile picture</label>
            </div>

            <div class="input-container">
                <span class="input-container-addon">
                    <i class="fa fa-image"></i>
                </span>
                <input class="input-style" id="image-input" type="file" name="image" placeholder="Profile" accept="image/jpg, image/jpeg, image/png" required>
            </div>

            <div class="input-container">
                <span class="input-container-addon">
                    <i class="fa fa-user"></i>
                </span>
                <input class="input-style" id="full_name" type="text" name="full_name" placeholder="First Name |  Middle Initial | Last Name" required>
            </div>

            <div class="input-container">
                <span class="input-container-addon">
                    <i class="fa fa-address-card"></i>
                </span>
                <input class="input-style" id="student_num" type="number" name="student_num" placeholder="Student ID number (2000XXX)" required>
            </div>


            <div class="input-container">
                <span class="input-container-addon">
                    <i class="fa fa-envelope-square"></i>
                </span>
                <input class="input-style" id="email" type="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="input-container">
                <span class="input-container-addon">
                    <i class="fa fa-lock"></i>
                </span>
                <input class="input-style" id="password" type="password" name="password" placeholder="LRN's number" required>
            </div>

            <div class="button">
                <button type="submit" name="submit" class="submit">Submit</button>
            </div>
        </form>
    </div>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script>
        document.querySelector('.form').addEventListener('submit', function(event) {
            var studentNum = document.getElementById('student_num').value;
            var lrnNumber = document.getElementById('password').value;

            if (studentNum.length !== 7 || isNaN(studentNum)) {
                event.preventDefault(); // Prevent form submission
                swal("Invalid Student ID", "Student ID number must be exactly 7 digits long.", "error");
            }
            // Check if the LRN number is exactly 12 digits long and contains only digits
            if (lrnNumber.length !== 12 || isNaN(lrnNumber)) {
                event.preventDefault(); // Prevent form submission
                swal("Invalid LRN Number", "LRN number must be exactly 12 digits long and contain only digits.", "error");
            }
        });
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