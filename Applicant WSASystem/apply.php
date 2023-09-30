<?php
session_name("ApplicantSession");
session_start();
include('../include/connection.php');

// Other variables
$last_name = $_POST['last_name'] ?? '';
$first_name = $_POST['first_name'] ?? '';
$middle_name = $_POST['middle_name'] ?? '';
$dob = $_POST['dob'] ?? '';
$pob = $_POST['pob'] ?? '';
$gender = $_POST['gender'] ?? '';
$email = $_POST['email'] ?? '';
$mobile_num = $_POST['mobile_num'] ?? '';
$citizenship = $_POST['citizenship'] ?? '';
$barangay = $_POST['barangay'] ?? '';
$town_city = $_POST['town_city'] ?? '';
$province = $_POST['province'] ?? '';
$zip_code = $_POST['zip_code'] ?? '';
$id_number = $_POST['id_number'] ?? '';
$father_name = $_POST['father_name'] ?? '';
$father_address = $_POST['father_address'] ?? '';
$father_work = $_POST['father_work'] ?? '';
$mother_name = $_POST['mother_name'] ?? '';
$mother_address = $_POST['mother_address'] ?? '';
$mother_work = $_POST['mother_work'] ?? '';
$date_submitted = date('Y-m-d');

// Variables for fetching scholarship requirements
$requirements = '';
$scholarshipId = '';

if (isset($_GET['id'])) {
    $scholarshipId = $_GET['id'];
    $sql = "SELECT * FROM tbl_scholarship WHERE scholarship_id = ?";
    $stmt = $dbConn->prepare($sql);
    $stmt->bind_param("i", $scholarshipId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $requirements = $row['requirements'];
        $scholarship = $row['scholarship'];
    }
}

$requirements = explode("\n", $requirements);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the user ID from the session
    $user_id = $_SESSION['user_id'];

    // Fetch the selected scholarship's ID based on the scholarship name
    $sql = "SELECT scholarship_id FROM tbl_scholarship WHERE scholarship = ?";
    $stmt = $dbConn->prepare($sql);
    $stmt->bind_param("s", $scholarship);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $scholarship_id = $row['scholarship_id'];
    } else {
        // Handle the case when the scholarship is not found
        die('Scholarship not found');
    }

    // Fetch the full name and image from the tbl_user table
    $sql = "SELECT full_name, image FROM tbl_user WHERE user_id = ?";
    $stmt = $dbConn->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $full_name = $row['full_name'];
        $image = $row['image'];
    } else {
        // Handle the case when user ID is not found in tbl_user table
        // You can display an error message or redirect the user to an error page
        die('User ID not found in tbl_user table');
    }

    // Check if the user has already applied for the selected scholarship
    $sql = "SELECT * FROM tbl_userapp WHERE applicant_name = ? AND scholarship_name = ?";
    $stmt = $dbConn->prepare($sql);
    $stmt->bind_param("ss", $full_name, $scholarship);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $alreadyApplied = "You have already applied for this scholarship.";
        exit();
    }

    if (isset($_FILES["file"])) {
        $file = $_FILES["file"];
        $fileCount = count($file["name"]);

        $validImageExtension = ['jpg', 'jpeg', 'png', 'pdf'];
        $uploadedFiles = array();

        // Specify the target directory using $_SERVER['DOCUMENT_ROOT']
        $targetDirectory = $_SERVER['DOCUMENT_ROOT'] . '/EASE-CHOLAR/file_uploads/';

        for ($i = 0; $i < $fileCount; $i++) {
            $fileName = $file["name"][$i];
            $tmpName = $file["tmp_name"][$i];
            $imageExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($imageExtension, $validImageExtension)) {
                echo "<script> alert('Invalid Image Extension'); </script>";
            } else {
                $newFileName = uniqid();
                $newFileName .= '.' . $imageExtension;

                // Upload the file to the specified target directory
                if (move_uploaded_file($tmpName, $targetDirectory . $newFileName)) {
                    $uploadedFiles[] = $newFileName;
                }
            }
        }

        // Convert the uploaded file names array to a comma-separated string
        $fileNamesString = implode(',', $uploadedFiles);
    }

    // Validate data on the server
    $isValid = true;

    // Server-side validation for required fields
    if (empty($last_name) || empty($first_name) || empty($middle_name) || empty($dob) || empty($pob) || empty($gender) || empty($email) || empty($mobile_num) || empty($citizenship) || empty($barangay) || empty($town_city) || empty($province) || empty($zip_code) || empty($id_number) || empty($father_name) || empty($father_address) || empty($father_work) || empty($mother_name) || empty($mother_address) || empty($mother_work)) {
        $isValid = false;
        echo "<script>alert('Please fill in all required fields.')</script>";
    }

    if ($isValid) {
        // Data is valid, proceed with database insertion

        // Insert into Database
        $sql = "INSERT INTO `tbl_userapp` (user_id, image, applicant_name, scholarship_name, last_name, first_name, middle_name, dob, pob, gender, email, mobile_num, citizenship, barangay, town_city, province, zip_code, id_number, father_name, father_address, father_work, mother_name, mother_address, mother_work, file, scholarship_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $dbConn->prepare($sql);

        if ($stmt->error) {
            die('Error in SQL query: ' . $stmt->error);
        }

        $stmt->bind_param(
            "issssssssssssssssssssssssi", // 'i' for integer variables
            $user_id, // 'i' because user_id is an integer
            $image,
            $full_name,
            $scholarship,
            $last_name,
            $first_name,
            $middle_name,
            $dob,
            $pob,
            $gender,
            $email,
            $mobile_num,
            $citizenship,
            $barangay,
            $town_city,
            $province,
            $zip_code,
            $id_number,
            $father_name,
            $father_address,
            $father_work,
            $mother_name,
            $mother_address,
            $mother_work,
            $fileNamesString,
            $scholarship_id
        );

        if (!$stmt->execute()) {
            $errorMessage = "Failed to insert application.";
            
        } else {
            // Get the image from 'tbl_user' table
            $sql = "SELECT image FROM tbl_user WHERE user_id = ?";
            $stmt = $dbConn->prepare($sql);
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $userImage = $row['image'];
            } else {
                $userImage = 'default.jpg';
            }
            $is_read = 'unread';

            // Insert notification with the image
            $notificationMessage = "New application has been submitted";
            $sql = "INSERT INTO tbl_notifications (user_id, message, image, is_read) VALUES (?, ?, ?, ?)";
            $stmt = $dbConn->prepare($sql);
            $stmt->bind_param("ssss", $user_id, $notificationMessage, $userImage, $is_read);
            $stmt->execute();

            // Insert notification with the image into tbl_reg_notifications
            $regNotificationMessage = "New application has been submitted"; // Change this message if needed
            $sqlRegNotifications = "INSERT INTO tbl_reg_notifications (user_id, message, image, is_read) VALUES (?, ?, ?, ?)";
            $stmtRegNotifications = $dbConn->prepare($sqlRegNotifications);
            $stmtRegNotifications->bind_param("ssss", $user_id, $regNotificationMessage, $userImage, $is_read);
            $stmtRegNotifications->execute();
                 // Trigger a SweetAlert2 modal based on success
                 $successMessage = "Application submitted successfully!";
                 
             }
         }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/apply.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    <title>Application Form</title>
</head>


<body>
    <?php include('../include/header.php') ?>
    <div class="wrapper">
    <?php
    if (isset($alreadyApplied)) {
        echo '<script>
             Swal.fire({
                 icon: "success",
                 title: "Success",
                 text: "'.$alreadyApplied.'",
                 confirmButtonText: "OK"
             }).then((result) => {
                 if (result.isConfirmed) {
                     window.location.href = "applicant_dashboard.php";
                 }
             });
         </script>';
    }
            if (isset($successMessage)) {
                echo '<script>
    Swal.fire({
        position: "center",
        icon: "success",
        title: "'.$successMessage.'",
        showConfirmButton: false,
        timer: 1500
    }).then((result) => {
        if (result.dismiss === Swal.DismissReason.timer) {
            window.location.href = "application_status.php";
        }
    });
</script>';
            }

            if (isset($errorMessage)) {
                echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "'.$errorMessage.'",
                    confirmButtonText: "OK"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "applicant_dashboard.php";
                    }
                });
            </script>';
            }
            ?>

        <div class="header">
            <ul>
                <li class="active form_1_progessbar">
                    <div>
                        <p><i class="fa fa-user"></i></p>
                        <span class="label">Personal Information</span>
                    </div>
                </li>
                <li class="form_2_progessbar">
                    <div>
                        <p><i class="fa fa-users" aria-hidden="true"></i></p>
                        <span class="label">Family Background</span>
                    </div>
                </li>
                <li class="form_3_progessbar">
                    <div>
                        <p><i class="fa fa-file" aria-hidden="true"></i></p>
                        <span class="label">File Upload</span>
                    </div>
                </li>
            </ul>
        </div>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="container">
                <div class="form first">
                    <h3 style="color:darkgreen">PERSONAL INFORMATION:</h3>
                    <br>
                    <div class="details personal">
                        <div class="fields">
                            <div class="input-field">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" placeholder="Enter your lastname" value="<?php echo $last_name; ?>" required>
                                <div class="validation-message" id="last_name-error"></div>
                            </div>
                            <div class="input-field">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" placeholder="Enter your firstname" value="<?php echo $first_name; ?>" required>
                                <div class="validation-message" id="first_name-error"></div>
                            </div>
                            <div class="input-field">
                                <label for="middle_name">Middle Name</label>
                                <input type="text" id="middle_name" name="middle_name" placeholder="Enter your middlename" value="<?php echo $middle_name; ?>" required>
                                <div class="validation-message" id="middle_name-error"></div>
                            </div>
                            <div class="input-field">
                                <label>Date of Birth</label>
                                <input type="date" id="dob" name="dob" placeholder="Enter birthdate" value="<?php echo $dob; ?>" required>
                                <div class="validation-message" id="date_birth-error"></div>
                            </div>
                            <div class="input-field">
                                <label>Place of Birth</label>
                                <input type="text" id="pob" name="pob" placeholder="Enter birthplace" value="<?php echo $pob; ?>" required>
                                <div class="validation-message" id="pob-error"></div>
                            </div>
                            <div class="input-field">
                                <label>Gender</label>
                                <select id="gender" name="gender" required>
                                    <option disabled selected>Select gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                                <div class="validation-message" id="gender-error"></div>
                            </div>
                        </div>

                        <div class="input-field">
                            <label>Email</label>
                            <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo $email; ?>" required>
                            <div class="validation-message" id="email-error"></div>
                        </div>
                        <div class="fields">
                            <div class="input-field">
                                <label>School ID Number</label>
                                <input type="number" id="id_number" name="id_number" placeholder="2XXXX21" value="<?php echo $id_number; ?>" required>
                                <div class="validation-message" id="id_number-error"></div>
                            </div>
                            <div class="input-field">
                                <label>Mobile Number</label>
                                <input type="number" id="mobile_num" name="mobile_num" placeholder="09XXXXXXXXX" value="<?php echo $mobile_num; ?>" required pattern="[0-9]{11}">
                                <div class="validation-message" id="mobile_num-error"></div>
                            </div>
                            <div class="input-field">
                                <label>Citizenship</label>
                                <input type="text" id="citizenship" name="citizenship" placeholder="Enter your citizenship" value="<?php echo $citizenship; ?>" required>
                                <div class="validation-message" id="citizenship-error"></div>
                            </div>
                        </div>
                        <hr>
                        <div class="input-field">
                            <h4>Permanent Address</h4>
                            <div class="address-inputs">
                                <div class="address-container">
                                    <input type="text" id="barangay" name="barangay" placeholder="Street & Barangay" value="<?php echo $barangay; ?>" required>
                                    <div class="validation-message" id="barangay-error"></div>
                                </div>

                                <div class="address-container">
                                    <input type="text" id="town_city" name="town_city" placeholder="Town/City/Municipality" value="<?php echo $town_city; ?>" required>
                                    <div class="validation-message" id="town_city-error"></div>
                                </div>

                                <div class="address-container">
                                    <input type="text" id="province" name="province" placeholder="Province" value="<?php echo $province; ?>" required>
                                    <div class="validation-message" id="province-error"></div>
                                </div>

                                <div class="address-container">
                                    <input type="number" id="zip_code" name="zip_code" placeholder="Zip Code" value="<?php echo $zip_code; ?>" required>
                                    <div class="validation-message" id="zip_code-error"></div>
                                </div>
                            </div>
                        </div>
                        <div class="btns_wrap">
                            <div class="common_btns form_1_btns">
                                <button type="button" class="btn_next">Next <span class="icon"><ion-icon name="arrow-forward-sharp"></ion-icon></span></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form_2 data_info" style="display: none;">
                    <h3 style="color:darkgreen">FAMILY BACKGROUND:</h3>
                    <div class="details family">
                        <div class="fields-info">
                            <div class="form">
                                <div class="input-field">
                                    <span class="title"> FATHER </span>
                                    <hr>
                                    <label>Name</label>
                                    <input type="text" id="father_name" name="father_name" placeholder="Enter father name" value="<?php echo $father_name; ?>" required>
                                    <div class="validation-message" id="father_name-error"></div>
                                    <label>Address</label>
                                    <input type="text" id="father_address" name="father_address" placeholder="Enter address" value="<?php echo $father_address; ?>" required>
                                    <div class="validation-message" id="father_address-error"></div>
                                    <label>Occupation</label>
                                    <input type="text" id="father_work" name="father_work" placeholder="Enter Occupation" value="<?php echo $father_work; ?>" required>
                                    <div class="validation-message" id="father_work-error"></div>
                                </div>
                            </div>

                            <div class="form">
                                <div class="input-field">
                                    <span class="title"> MOTHER </span>
                                    <hr>
                                    <label>Name</label>
                                    <input type="text" id="mother_name" name="mother_name" placeholder="Enter mother name" value="<?php echo $mother_name; ?>" required>
                                    <div class="validation-message" id="mother_name-error"></div>
                                    <label>Address</label>
                                    <input type="text" id="mother_address" name="mother_address" placeholder="Enter address" value="<?php echo $mother_address; ?>" required>
                                    <div class="validation-message" id="mother_address-error"></div>
                                    <label>Occupation</label>
                                    <input type="text" id="mother_work" name="mother_work" placeholder="Enter Occupation" value="<?php echo $mother_work; ?>" required>
                                    <div class="validation-message" id="mother_work-error"></div>
                                </div>
                            </div>
                            <div class="btns_wrap">
                                <div class="common_btns form_2_btns" style="display: none;">
                                    <button type="button" class="btn_back"><span class="icon"><ion-icon name="arrow-back-sharp"></ion-icon></span>Back</button>
                                    <button type="button" class="btn_next">Next <span class="icon"><ion-icon name="arrow-forward-sharp"></ion-icon></span></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form_3 data_info" style="display: none;">
                    <h3 style="color: darkgreen">Requirements Upload:</h3>
                    <hr>
                    <div class="details requirements">
                        <?php foreach ($requirements as $index => $requirement) { ?>
                            <div class="input-file">
                                <input id="checkbox-<?php echo $index; ?>" class="checkbox" type="checkbox" disabled>
                                <label class="requirement-label" for="file-input-<?php echo $index; ?>"><?php echo $requirement; ?></label>
                                <div class="requirement-validation" id="requirement-validation"></div>
                                <input id="file-input-<?php echo $index; ?>" class="file-input" type="file" name="file[]" required>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="btns_wrap">
                        <div class="common_btns form_3_btns" style="display: none;">
                            <button type="button" class="btn_back"><span class="icon"><ion-icon name="arrow-back-sharp"></ion-icon></span>Back</button>
                            <button type="submit" class="btn_done" name="submit">Done</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>



    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var form_1 = document.querySelector(".form.first");
            var form_2 = document.querySelector(".form_2.data_info");
            var form_3 = document.querySelector(".form_3.data_info");

            var form_1_btns = document.querySelector(".form_1_btns");
            var form_2_btns = document.querySelector(".form_2_btns");
            var form_3_btns = document.querySelector(".form_3_btns");

            var form_1_next_btn = document.querySelector(".form_1_btns .btn_next");
            var form_2_back_btn = document.querySelector(".form_2_btns .btn_back");
            var form_2_next_btn = document.querySelector(".form_2_btns .btn_next");
            var form_3_back_btn = document.querySelector(".form_3_btns .btn_back");

            var form_2_progessbar = document.querySelector(".form_2_progessbar");
            var form_3_progessbar = document.querySelector(".form_3_progessbar");

            var btn_done = document.querySelector(".btn_done");
            var modal_wrapper = document.querySelector(".modal_wrapper");
            var shadow = document.querySelector(".shadow");

            form_1_next_btn.addEventListener("click", function() {
                if (validationForm1(form_1)) {
                    form_1.style.display = "none";
                    form_2.style.display = "block";

                    form_1_btns.style.display = "none";
                    form_2_btns.style.display = "flex";

                    form_2_progessbar.classList.add("active");
                } else {

                }
            });
            form_2_back_btn.addEventListener("click", function() {
                form_2.style.display = "none";
                form_1.style.display = "block";

                form_2_btns.style.display = "none";
                form_1_btns.style.display = "flex";

                form_2_progessbar.classList.remove("active");
            });

            form_2_next_btn.addEventListener("click", function() {
                if (validationForm2(form_2)) {
                    form_2.style.display = "none";
                    form_3.style.display = "block";

                    form_2_btns.style.display = "none";
                    form_3_btns.style.display = "flex";

                    form_3_progessbar.classList.add("active");
                } else {

                }
            });

            form_3_back_btn.addEventListener("click", function() {
                form_3.style.display = "none";
                form_2.style.display = "block";

                form_3_btns.style.display = "none";
                form_2_btns.style.display = "flex";

                form_3_progessbar.classList.remove("active");
            });

            btn_done.addEventListener("click", function() {
                if (validationForm3(form_3)) {
                } else {

                }
            })

        });

        function validationForm1(form) {
            var last_name = document.getElementById('last_name');
            var last_name_error = document.getElementById('last_name-error');
            var first_name = document.getElementById('first_name');
            var first_name_error = document.getElementById('first_name-error');
            var middle_name = document.getElementById('middle_name');
            var middle_name_error = document.getElementById('middle_name-error');
            var pob = document.getElementById('pob');
            var pob_error = document.getElementById('pob-error');
            var gender = document.getElementById('gender');
            var gender_error = document.getElementById('gender-error');
            var email = document.getElementById('email');
            var email_error = document.getElementById('email-error');
            var id_number = document.getElementById('id_number');
            var id_number_error = document.getElementById('id_number-error');
            var mobile_num = document.getElementById('mobile_num');
            var mobile_num_error = document.getElementById('mobile_num-error');
            var citizenship = document.getElementById('citizenship');
            var citizenship_error = document.getElementById('citizenship-error');
            var barangay = document.getElementById('barangay');
            var barangay_error = document.getElementById('barangay-error');
            var province = document.getElementById('province');
            var province_error = document.getElementById('province-error');
            var town_city = document.getElementById('town_city');
            var town_city_error = document.getElementById('town_city-error');
            var zip_code = document.getElementById('zip_code');
            var zip_code_error = document.getElementById('zip_code-error');

            var dobInput = document.querySelector('input[name="dob"]');
            var dobError = document.getElementById('date_birth-error');

            var emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
            var mobileNumRegex = /^[0-9]{11}$/;
            var idNumberRegex = /^[0-9]{7}$/;
            var isValid = true;

            if (last_name.value.trim() === '') {
                last_name_error.textContent = 'Last Name is required';
                isValid = false;
            } else {
                last_name_error.textContent = '';
            }

            if (first_name.value.trim() === '') {
                first_name_error.textContent = 'First Name is required';
                isValid = false;
            } else {
                first_name_error.textContent = '';
            }
            if (middle_name.value.trim() === '') {
                middle_name_error.textContent = 'Middle Name is required';
                isValid = false;
            } else {
                middle_name_error.textContent = '';
            }


            var selectedDate = new Date(dobInput.value);
            var currentDate = new Date();

            if (isNaN(selectedDate)) {
                dobError.textContent = 'Please enter a valid date of birth.';
                isValid = false;
            } else if (selectedDate > currentDate) {
                dobError.textContent = 'Date of birth cannot be in the future.';
                isValid = false;
            } else {
                dobError.textContent = '';
            }

            if (pob.value.trim() === '') {
                pob_error.textContent = 'Place of birth is required';
                isValid = false;
            } else {
                pob_error.textContent = '';
            }

            var gender = document.getElementById('gender');
            var gender_error = document.getElementById('gender-error');
            var selectedGender = gender.value;

            if (selectedGender === 'Select gender') {
                gender_error.textContent = 'Please select a gender';
                isValid = false;
            } else {
                gender_error.textContent = '';
            }

            if (email.value.trim() === '') {
                email_error.textContent = 'Email is required';
                isValid = false;
            } else if (!emailRegex.test(email.value.trim())) {
                email_error.textContent = 'Please enter a valid email address';
                isValid = false;
            } else {
                email_error.textContent = '';
            }

            if (id_number.value.trim() === '') {
                id_number_error.textContent = 'Id Number is required';
                isValid = false;
            } else if (!idNumberRegex.test(id_number.value.trim())) {
                id_number_error.textContent = 'Please enter a valid 7-digit ID number';
                isValid = false;
            } else {
                id_number_error.textContent = '';
            }

            if (citizenship.value.trim() === '') {
                citizenship_error.textContent = 'Citizenship is required';
                isValid = false;
            } else {
                citizenship_error.textContent = '';
            }

            if (mobile_num.value.trim() === '') {
                mobile_num_error.textContent = 'Mobile number is required';
                isValid = false;
            } else if (!mobileNumRegex.test(mobile_num.value.trim())) {
                mobile_num_error.textContent = 'Please enter a valid 11-digit mobile number';
                isValid = false;
            } else {
                mobile_num_error.textContent = '';
            }

            if (barangay.value.trim() === '') {
                barangay_error.textContent = 'Barangay is required';
                isValid = false;
            } else {
                barangay_error.textContent = '';
            }

            if (province.value.trim() === '') {
                province_error.textContent = 'Province is required';
                isValid = false;
            } else {
                province_error.textContent = '';
            }

            if (town_city.value.trim() === '') {
                town_city_error.textContent = 'Town City is required';
                isValid = false;
            } else {
                town_city_error.textContent = '';
            }

            if (zip_code.value.trim() === '') {
                zip_code_error.textContent = 'Zip code is required';
                isValid = false;
            } else {
                zip_code_error.textContent = '';
            }


            return isValid;
        }


        function validationForm2(form) {
            var father_name = document.getElementById('father_name');
            var father_name_error = document.getElementById('father_name-error');
            var father_address = document.getElementById('father_address');
            var father_address_error = document.getElementById('father_address-error');
            var father_work = document.getElementById('father_work');
            var father_work_error = document.getElementById('father_work-error');
            var mother_name = document.getElementById('mother_name');
            var mother_name_error = document.getElementById('mother_name-error');
            var mother_address = document.getElementById('mother_address');
            var mother_address_error = document.getElementById('mother_address-error');
            var mother_work = document.getElementById('mother_work');
            var mother_work_error = document.getElementById('mother_work-error');

            var isValid = true;

            if (father_name.value.trim() === '') {
                father_name_error.textContent = 'Father name is required';
                isValid = false;
            } else {
                father_name_error.textContent = '';
            }

            if (father_address.value.trim() === '') {
                father_address_error.textContent = 'Address is required';
                isValid = false;
            } else {
                father_address_error.textContent = '';
            }
            if (father_work.value.trim() === '') {
                father_work_error.textContent = 'Occupation is required';
                isValid = false;
            } else {
                father_work_error.textContent = '';
            }
            if (mother_name.value.trim() === '') {
                mother_name_error.textContent = 'Mother Name is required';
                isValid = false;
            } else {
                mother_name_error.textContent = '';
            }
            if (mother_address.value.trim() === '') {
                mother_address_error.textContent = 'Address is required';
                isValid = false;
            } else {
                mother_address_error.textContent = '';
            }
            if (mother_work.value.trim() === '') {
                mother_work_error.textContent = 'Occupation is required';
                isValid = false;
            } else {
                mother_work_error.textContent = '';
            }

            return isValid;
        }

        function validationForm3(form) {

            var fileInputs = form.querySelectorAll('.file-input');
            var isValid = true;

            for (var i = 0; i < fileInputs.length; i++) {
                var fileInput = fileInputs[i];
                var requirementLabel = fileInput.previousElementSibling;
                var checkbox = fileInput.previousElementSibling.previousElementSibling;
                var requirementValidation = fileInput.parentElement.querySelector('.requirement-validation');

                fileInput.addEventListener('change', function() {
                    if (fileInput.value.trim() !== '') {
                        // Clear the validation message when a file is uploaded
                        requirementValidation.textContent = '';
                        requirementValidation.style.color = 'inherit';
                    }
                });

                if (fileInput.value.trim() === '') {
                    isValid = false;
                    // Display the validation message
                    requirementValidation.textContent = '*Must upload the photo'
                    requirementLabel.style.color = 'red';
                    // You can also uncheck the corresponding checkbox if needed
                    // checkbox.checked = false;
                } else {
                    // Reset the validation message
                    requirementValidation.textContent = '';
                    requirementLabel.style.color = 'inherit';
                }
            }

            return isValid;
        }


        // Get all file input elements
        const fileInputs = document.querySelectorAll('.file-input');

        fileInputs.forEach((fileInput, index) => {
            fileInput.addEventListener('change', () => {
                const checkbox = document.getElementById(`checkbox-${index}`);
                checkbox.disabled = true;
                checkbox.checked = true;
            });
        });
    </script>
</body>

</html>