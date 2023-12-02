<?php
require '../include/connection.php';

function compressImage($source, $destination, $quality) {
    $info = getimagesize($source);

    if ($info['mime'] == 'image/jpeg') {
        $image = imagecreatefromjpeg($source);
    } elseif ($info['mime'] == 'image/png') {
        $image = imagecreatefrompng($source);
    } else {
        return false;
    }

    $success = imagejpeg($image, $destination, $quality);

    imagedestroy($image);

    return $success ? $destination : false;
}


if (isset($_POST['submit'])) {
    $full_name = mysqli_real_escape_string($dbConn, $_POST['full_name']);
    $email = mysqli_real_escape_string($dbConn, $_POST['email']);
    $student_num = mysqli_real_escape_string($dbConn, $_POST['student_num']);
    $password = mysqli_real_escape_string($dbConn, $_POST['password']);
    $image = $_FILES['image']['name'];
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_type = $_FILES['image']['type'];
    $maxImageSize = 1024 * 1024 * 5;

    $filename = 'default-avatar.png';

    $query = mysqli_prepare($dbConn, "SELECT * FROM `tbl_user` WHERE email = ?");
    mysqli_stmt_bind_param($query, "s", $email);
    mysqli_stmt_execute($query);
    $result = mysqli_stmt_get_result($query);

    if (mysqli_num_rows($result) > 0) {
        $userExistsMessage = 'Email already exists!';
    } else {
        $userPasswordExistQuery = mysqli_prepare($dbConn, "SELECT * FROM `tbl_user` WHERE password = ?");
        mysqli_stmt_bind_param($userPasswordExistQuery, "s", $password);
        mysqli_stmt_execute($userPasswordExistQuery);
        $userPasswordExistResult = mysqli_stmt_get_result($userPasswordExistQuery);

        if (mysqli_num_rows($userPasswordExistResult) > 0) {
            $userPasswordExist = 'LRN already exists!';
        } else {
            $userStudentNumExistQuery = mysqli_prepare($dbConn, "SELECT * FROM `tbl_user` WHERE student_num = ?");
            mysqli_stmt_bind_param($userStudentNumExistQuery, "s", $student_num);
            mysqli_stmt_execute($userStudentNumExistQuery);
            $userStudentNumExistResult = mysqli_stmt_get_result($userStudentNumExistQuery);

            if (mysqli_num_rows($userStudentNumExistResult) > 0) {
                $userStudentNumExist = 'Student ID already exists!!';
            } else {
                if (!empty($_FILES['image']['name'])) {
                    if (!in_array($image_type, array('image/jpeg', 'image/jpg', 'image/png'))) {
                        $imageTypeMessage = 'Only JPEG, JPG, and PNG images are allowed.';
                    } elseif ($image_size > $maxImageSize) {
                        $largeImageMessage = 'Image size is too large!';
                    } else {
                        $filename = uniqid() . '_' . $image;
                        $targetDirectory = $_SERVER['DOCUMENT_ROOT'] . '/user_profiles/';
                        $targetPath = $targetDirectory . $filename;
                
                        // Compress the image before uploading
                        $compressedPath = compressImage($image_tmp_name, $targetPath, 20);
                
                        if ($compressedPath) {
                            if (rename($compressedPath, $targetPath)) {
                                if (!file_exists($targetPath)) {
                                    $registrationFailedMessage = 'Error moving the uploaded image.';
                                } else {
                                    
                                    $custom_id = 'ISU_' . sprintf("%03d", rand(1, 999));
                                    $insert = mysqli_prepare($dbConn, "INSERT INTO `tbl_user` (custom_id, full_name, student_num, email, password, image) VALUES(?, ?, ?, ?, ?, ?)");
                                    mysqli_stmt_bind_param($insert, "ssssss", $custom_id, $full_name, $student_num, $email, $password, $filename);
                
                                    if (mysqli_stmt_execute($insert)) {
                                        $successMessage = 'Registered successfully!';
                                    } else {
                                        error_log("Error executing insert query: " . mysqli_error($dbConn));
                                        $registrationFailedMessage = 'Registration failed!';
                                    }
                                }
                            } else {
                                $registrationFailedMessage = 'Error moving the compressed image.';
                            }
                        } else {
                            $registrationFailedMessage = 'Error compressing the image.';
                        }
                    }
                } else {
                    $custom_id = 'ISU_' . sprintf("%03d", rand(1, 999));
                    $insert = mysqli_prepare($dbConn, "INSERT INTO `tbl_user` (custom_id, full_name, student_num, email, password, image) VALUES(?, ?, ?, ?, ?, ?)");
                    mysqli_stmt_bind_param($insert, "ssssss", $custom_id, $full_name, $student_num, $email, $password, $filename);
                
                    if (mysqli_stmt_execute($insert)) {
                        $successMessage = 'Registered successfully!';
                    } else {
                        error_log("Error executing insert query: " . mysqli_error($dbConn));
                        $registrationFailedMessage = 'Registration failed!';
                    }
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

            if (isset($userPasswordExist)) {
                echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "LRN Exists",
                    text: "' . $userPasswordExist . '",
                    showConfirmButton: false,
                    timer: 2000
                })
            </script>';
            }

            if (isset($userStudentNumExist)) {
                echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Student ID Exists",
                    text: "' . $userStudentNumExist . '",
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
            if (isset($invalidStudentNumMessage)) {
                echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Invalid Student Id Number",
                    text: "' . $invalidStudentNumMessage . '",
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
                <a href="applicant_register.php" class="active">Register</a>
            </div>

            <div class="selected-image-container">
                <div class="image-container">
                    <img id="selected-image" src="../user_profiles/default-avatar.png" alt="Selected Image">
                </div>
                <div class="round">
                    <input class="input-style" id="image-input" type="file" name="image" placeholder="Profile" accept="image/jpg, image/jpeg, image/png">
                    <i class='bx bxs-camera'></i>
                </div>
            </div>
            <label id="image-label">*Select your profile picture</label>

            <div class="input-container">
                <span class="input-container-addon">
                    <i class="fa fa-user"></i>
                </span>
                <input class="input-style" id="full_name" type="text" name="full_name" placeholder="First Name |  Middle Initial | Last Name" required <?php echo (isset($_POST['full_name'])) ? 'value="' . htmlspecialchars($_POST['full_name']) . '"' : 'value=""'; ?>>
            </div>

            <div class="input-container">
                <span class="input-container-addon">
                    <i class="fa fa-envelope-square"></i>
                </span>
                <input class="input-style" id="email" type="email" name="email" placeholder="Enter your email" required <?php echo (isset($_POST['email'])) ? 'value="' . htmlspecialchars($_POST['email']) . '"' : 'value=""'; ?>>
            </div>

            <div class="input-container">
                <span class="input-container-addon">
                    <i class="fa fa-address-card"></i>
                </span>
                <input class="input-style" id="student_num" type="text" name="student_num" placeholder="Student ID number (20-00XXX)" required <?php echo (isset($_POST['student_num'])) ? 'value="' . htmlspecialchars($_POST['student_num']) . '"' : 'value=""'; ?>>
            </div>

            <div class="input-container">
                <span class="input-container-addon">
                    <i class="fa fa-lock"></i>
                </span>
                <input class="input-style" id="password" type="number" name="password" placeholder="LRN's number" required <?php echo (isset($_POST['password'])) ? 'value="' . htmlspecialchars($_POST['password']) . '"' : 'value=""'; ?>>
            </div>


            <label class="show-password" for="show-password">
                <input type="checkbox" id="show-password"> Show Password
            </label>

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

            if (studentNum.length !== 8 || studentNum.indexOf('-') !== 2 || isNaN(studentNum.replace('-', ''))) {
                event.preventDefault();
                swal("Invalid Student ID", "Student ID number must be exactly 8 characters long and formatted as 'XX-XXXXX', where X represents digits.", "error");
            }

            if (lrnNumber.length !== 12 || isNaN(lrnNumber)) {
                event.preventDefault(); 
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
                        selectedImage.style.display = ''; 
                        imageLabel.style.display = 'none'; 
                    };

                    reader.readAsDataURL(input.files[0]);
                } else {
                    selectedImage.src = ""; 
                    selectedImage.style.display = 'none';
                    imageLabel.style.display = 'block';
                }
            });
        }

        displaySelectedImage();

        document.getElementById("show-password").addEventListener("change", function() {
            var passwordInput = document.getElementById("password");
            if (this.checked) {
                passwordInput.type = "number";
            } else {
                passwordInput.type = "password";
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            function formatStudentNumInput() {
                var studentNumInput = document.getElementById('student_num');
                var inputValue = studentNumInput.value.replace(/[^0-9]/g, '');
                var formattedValue = inputValue.replace(/^(\d{2})?(\d{5})?$/, '$1-$2');
                studentNumInput.value = formattedValue;
            }

            var studentNumInput = document.getElementById('student_num');
            studentNumInput.addEventListener('input', formatStudentNumInput);
            studentNumInput.addEventListener('blur', formatStudentNumInput);
        });
    </script>


</body>

</html>