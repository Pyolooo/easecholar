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
$course = $_POST['course'] ?? '';
$year_lvl = $_POST['year_lvl'] ?? '';
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
$gross_income = $_POST['gross_income'] ?? '';
$num_siblings = $_POST['num_siblings'] ?? '';
$date_submitted = date('Y-m-d');

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
    $user_id = $_SESSION['user_id'];

    $sql = "SELECT scholarship_id FROM tbl_scholarship WHERE scholarship = ?";
    $stmt = $dbConn->prepare($sql);
    $stmt->bind_param("s", $scholarship);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $scholarship_id = $row['scholarship_id'];
    } else {
        die('Scholarship not found');
    }

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
        die('User ID not found in tbl_user table');
    }


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

        $fileNamesString = implode(',', $uploadedFiles);
    }

    $isValid = true;

    // Server-side validation for required fields
    if (empty($last_name) || empty($first_name) || empty($middle_name) || empty($dob) || empty($pob) || empty($gender) || empty($email) || empty($course) || empty($year_lvl) || empty($mobile_num) || empty($citizenship) || empty($barangay) || empty($town_city) || empty($province) || empty($zip_code) || empty($id_number) || empty($father_name) || empty($father_address) || empty($father_work) || empty($mother_name) || empty($mother_address) || empty($mother_work) || empty($gross_income) || empty($num_siblings)) {
        $isValid = false;
        echo "<script>alert('Please fill in all required fields.')</script>";
    }

    if ($isValid) {
        $sql = "INSERT INTO `tbl_userapp` (user_id, image, applicant_name, scholarship_name, last_name, first_name, middle_name, dob, pob, gender, email, course, year_lvl, mobile_num, citizenship, barangay, town_city, province, zip_code, id_number, father_name, father_address, father_work, mother_name, mother_address, mother_work, gross_income, num_siblings, file, scholarship_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?)";

        $stmt = $dbConn->prepare($sql);

        if ($stmt->error) {
            die('Error in SQL query: ' . $stmt->error);
        }

        $stmt->bind_param(
            "issssssssssssssssssssssssssssi", // 'i' for integer variables
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
            $course,
            $year_lvl,
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
            $gross_income,
            $num_siblings,
            $fileNamesString,
            $scholarship_id
        );

        if (!$stmt->execute()) {
            $errorMessage = "Failed to insert application.";
        } else {

            $application_id = $stmt->insert_id;
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
            $source = 'tbl_userapp';

            $notificationMessage = "New application has been submitted";
            $sql = "INSERT INTO tbl_notifications (user_id, message, image, is_read, source, application_id) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $dbConn->prepare($sql);
            $stmt->bind_param("issssi", $user_id, $notificationMessage, $userImage, $is_read, $source, $application_id);
            $stmt->execute();


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
    <style>
        .select-option {
            display: grid;
            grid-template-columns: 1fr;
        }
    </style>
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
                 text: "' . $alreadyApplied . '",
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
        title: "' . $successMessage . '",
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
                    text: "' . $errorMessage . '",
                    confirmButtonText: "OK"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "applicant_dashboard.php";
                    }
                });
            </script>';
        }
        ?>


        <form action="" method="POST" enctype="multipart/form-data">
            <div class="container">
                <div class="form first">
                    <h4 class="label1">PERSONAL INFORMATION:</h4>
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
                                <label>Sex</label>
                                <select id="gender" name="gender" required>
                                    <option disabled selected>Select sex</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                                <div class="validation-message" id="gender-error"></div>
                            </div>

                            <div class="input-field">
                                <label>Email</label>
                                <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo $email; ?>" required>
                                <div class="validation-message" id="email-error"></div>
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


                        <div class="fields">
                            <div class="input-field">
                                <label>School ID Number</label>
                                <input type="text" id="id_number" name="id_number" placeholder="2XXXXX1" value="<?php echo $id_number; ?>" oninput="formatIdNumber(this)" required>
                                <div class="validation-message" id="id_number-error"></div>
                            </div>

                            <div class="input-field">
                                <label>Course</label>
                                <select id="course" name="course" required>
                                    <option disabled selected>Select course</option>
                                    <option value="BSIT">BSIT</option>
                                    <option value="BSA">BSA</option>
                                </select>
                                <div class="validation-message" id="course-error"></div>
                            </div>

                            <div class="input-field">
                                <label>Year level</label>
                                <select id="year_lvl" name="year_lvl" required>
                                    <option disabled selected>Select year</option>
                                    <option value="1st">1st Year</option>
                                    <option value="2nd">2nd Year</option>
                                    <option value="3rd">3rd Year</option>
                                    <option value="4th">4th Year</option>
                                </select>
                                <div class="validation-message" id="year_lvl-error"></div>
                            </div>
                        </div>
                        <hr>
                        <div class="input-field">
                            <h4 class="label1">Permanent Address</h4>
                            <div class="address-inputs">
                                <div class="address-container">
                                    <label for="region">Region</label>
                                    <select id="region" name="region" required></select>
                                    <div class="validation-message" id="region-error"></div>
                                </div>

                                <div class="address-container">
                                    <label for="province">Province</label>
                                    <select id="province" name="province" required></select>
                                    <div class="validation-message" id="province-error"></div>
                                </div>

                                <div class="address-container">
                                    <label for="town_city">Town City</label>
                                    <select id="town_city" name="town_city" required></select>
                                    <div class="validation-message" id="town_city-error"></div>
                                </div>

                                <div class="address-container">
                                    <label for="barangay">Barangay</label>
                                    <select id="barangay" name="barangay" required></select>
                                    <div class="validation-message" id="barangay-error"></div>
                                </div>

                                <div class="address-container">

                                    <label for="zip_code">Zip Code</label>
                                    <input type="number" id="zip_code" name="zip_code" placeholder="Zip Code" value="<?php echo $zip_code; ?>" required>
                                    <div class="validation-message" id="zip_code-error"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form_2 data_info">
                    <h4 class="label1">FAMILY BACKGROUND:</h4>
                    <br>
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
                        </div>
                    </div>

                    <hr>
                    <div class="select-input-field">
                        <div class="input-field">
                            <label>Total Gross Income</label>
                            <input type="number" id="gross_income" name="gross_income" placeholder="Gross income" value="<?php echo $gross_income; ?>" required>
                            <div class="validation-message" id="gross_income-error"></div>
                        </div>
                        <div class="input-field">
                            <label>No. of Siblings in the family</label>
                            <input type="number" id="num_siblings" name="num_siblings" placeholder="Number of siblings" value="<?php echo $num_siblings; ?>" required>
                            <div class="validation-message" id="num_siblings-error"></div>
                        </div>
                    </div>
                </div>

                <div class="form_3 data_info">
                    <h4 class="label1">Requirements Upload:</h4>
                    <hr><br>
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
                        <div class="common_btns form_3_btns">
                            <button type="button" class="btn_back"><span class="icon"><ion-icon name="arrow-back-sharp"></ion-icon></span>Back</button>
                            <button type="submit" class="btn_done" name="submit">Done</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>



    </div>
    <script>
        function formatIdNumber(input) {
            let formattedValue = input.value.replace(/-/g, '');

            if (formattedValue.length >= 2) {
                formattedValue = formattedValue.slice(0, 2) + '-' + formattedValue.slice(2);
            }
            input.value = formattedValue;
        }

        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector("form");
            const lastNameInput = form.last_name;
            const firstNameInput = form.first_name;
            const middleNameInput = form.middle_name;
            const pobInput = form.pob;
            const provinceInput = form.province;
            const townCityInput = form.town_city;
            const barangayInput = form.barangay;
            const zipCodeInput = form.zip_code;
            const citizenshipInput = form.citizenship;
            const fatherNameInput = form.father_name;
            const fatherAddressInput = form.father_address;
            const fatherWorkInput = form.father_work;
            const motherNameInput = form.mother_name;
            const motherAddressInput = form.mother_address;
            const motherWorkInput = form.mother_work;
            const grossIncomeInput = form.gross_income;
            const noSiblingsInput = form.num_siblings;
            const courseDropdown = document.getElementById('course');
            const yearLevelDropdown = document.getElementById('year_lvl');
            const doneButton = document.querySelector(".btn_done");

            courseDropdown.addEventListener('change', function() {
                document.getElementById("course-error").textContent = '';

                const selectedCourse = courseDropdown.value;

                yearLevelDropdown.innerHTML = '<option disabled selected>Select year</option>';

                if (selectedCourse === 'BSA') {
                    yearLevelDropdown.innerHTML += '<option value="1st">1st Year</option>';
                    yearLevelDropdown.innerHTML += '<option value="2nd">2nd Year</option>';
                } else if (selectedCourse === 'BSIT') {
                    yearLevelDropdown.innerHTML += '<option value="1st">1st Year</option>';
                    yearLevelDropdown.innerHTML += '<option value="2nd">2nd Year</option>';
                    yearLevelDropdown.innerHTML += '<option value="3rd">3rd Year</option>';
                    yearLevelDropdown.innerHTML += '<option value="4th">4th Year</option>';
                }
            });

            yearLevelDropdown.addEventListener('change', function() {
                const selectedYear = yearLevelDropdown.value;
                const year_lvl_error = document.getElementById('year_lvl-error');

                if (selectedYear === 'Select year') {
                    year_lvl_error.textContent = 'Please select a year';
                    isValid = false;
                } else {
                    year_lvl_error.textContent = '';
                }
            });

            doneButton.addEventListener("click", function(event) {
                let isValid = true;

                const validationMessages = document.querySelectorAll(".validation-message");
                validationMessages.forEach(function(message) {
                    message.textContent = "";
                });


                if (lastNameInput.value.trim() === "") {
                    isValid = false;
                    document.getElementById("last_name-error").textContent = "Last Name is required.";

                    lastNameInput.addEventListener('input', function() {
                        document.getElementById("last_name-error").textContent = "";
                    });
                }

                if (firstNameInput.value.trim() === "") {
                    isValid = false;
                    document.getElementById("first_name-error").textContent = "First Name is required.";

                    firstNameInput.addEventListener('input', function() {
                        document.getElementById("first_name-error").textContent = "";
                    });
                }

                if (middleNameInput.value.trim() === "") {
                    isValid = false;
                    document.getElementById("middle_name-error").textContent = 'Middle Name is required';

                    middleNameInput.addEventListener('input', function() {
                        document.getElementById("middle_name-error").textContent = "";
                    });
                }

                // Add validation logic for date of birth
                var dobInput = document.querySelector('input[name="dob"]');
                var dobError = document.getElementById('date_birth-error');

                function validateDateOfBirth() {
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
                }

                validateDateOfBirth();

                dobInput.addEventListener('input', function() {
                    validateDateOfBirth();
                });


                if (pobInput.value.trim() === "") {
                    isValid = false;
                    document.getElementById("pob-error").textContent = "Place of birth is required.";

                    pobInput.addEventListener('input', function() {
                        document.getElementById("pob-error").textContent = "";
                    });
                }

                // Add validation logic for gender
                var gender = document.getElementById('gender');
                var gender_error = document.getElementById('gender-error');

                function validateGender() {
                    var selectedGender = gender.value;

                    if (selectedGender === 'Select sex') {
                        gender_error.textContent = 'Please select a gender';
                        isValid = false;
                    } else {
                        gender_error.textContent = '';
                    }
                }

                // Initial validation
                validateGender();

                gender.addEventListener('change', function() {
                    validateGender();
                });

                // Add validation logic for email
                var emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
                var email = form.email; // Assuming form.email is the input element

                function validateEmail() {
                    if (email.value.trim() === '') {
                        document.getElementById("email-error").textContent = 'Email is required';
                        isValid = false;
                    } else if (!emailRegex.test(email.value.trim())) {
                        document.getElementById("email-error").textContent = 'Please enter a valid email address';
                        isValid = false;
                    } else {
                        document.getElementById("email-error").textContent = ''; // Clear the validation message
                    }
                }

                // Initial validation
                validateEmail();

                // Add an event listener to clear the validation message when the user types
                email.addEventListener('input', function() {
                    validateEmail();
                });

                var mobileNumRegex = /^[0-9]{11}$/;
                var mobileNum = form.mobile_num;

                function validateMobileNum() {
                    if (form.mobile_num.value.trim() === '') {
                        document.getElementById("mobile_num-error").textContent = "Mobile number is required.";
                        isValid = false;
                    } else if (!mobileNumRegex.test(mobile_num.value.trim())) {
                        document.getElementById("mobile_num-error").textContent = 'Please enter a valid 11-digit mobile number';
                        isValid = false;
                    } else {
                        document.getElementById("mobile_num-error").textContent = '';
                    }
                }

                validateMobileNum();

                // Add an event listener to clear the validation message when the user types
                mobileNum.addEventListener('input', function() {
                    validateMobileNum();
                });


                var course = document.getElementById('course');
                var course_error = document.getElementById('course-error');
                var selectedCourse = course.value;

                if (selectedCourse === 'Select course') {
                    document.getElementById("course-error").textContent = 'Please select a course';
                    isValid = false;
                }

                const year_lvl = document.getElementById('year_lvl');
                const year_lvl_error = document.getElementById('year_lvl-error');
                const selectedYear = year_lvl.value;

                if (selectedYear === 'Select year') {
                    year_lvl_error.textContent = 'Please select a year';
                    isValid = false;
                } else {
                    year_lvl_error.textContent = ''; // Clear the validation message
                }

                var idNumberRegex = /^\d+-\d{5}$/;
                // Initial validation when the form is submitted or loaded
                if (id_number.value.trim() === '') {
                    document.getElementById("id_number-error").textContent = 'ID Number is required';
                    isValid = false;
                } else if (!idNumberRegex.test(id_number.value.trim())) {
                    document.getElementById("id_number-error").textContent = 'Please enter a valid ID number in the format XX-XXXX';
                    isValid = false;
                }

                // Attach the input event listener
                document.getElementById('id_number').addEventListener('input', function() {
                    formatIdNumber(this); // Format the ID number

                    // Validate the formatted ID number
                    if (this.value.trim() === '') {
                        document.getElementById("id_number-error").textContent = 'ID Number is required';
                        isValid = false;
                    } else if (!idNumberRegex.test(this.value.trim())) {
                        document.getElementById("id_number-error").textContent = 'Please enter a valid ID number in the format XX-XXXX';
                        isValid = false;
                    } else {
                        document.getElementById("id_number-error").textContent = ''; // Clear the validation message
                        isValid = true; // Update isValid when the input is valid
                    }
                });




                if (citizenshipInput.value.trim() === "") {
                    isValid = false;
                    document.getElementById("citizenship-error").textContent = "Citizenship is required.";

                    citizenshipInput.addEventListener('input', function() {
                        document.getElementById("citizenship-error").textContent = "";
                    });
                }



                if (form.region.value.trim() === "") {
                    isValid = false;
                    document.getElementById("region-error").textContent = "Region is required.";
                }

                if (form.barangay.value.trim() === "") {
                    isValid = false;
                    document.getElementById("barangay-error").textContent = "Barangay is required.";

                    citizenshipInput.addEventListener('input', function() {
                        document.getElementById("citizenship-error").textContent = "";
                    });
                }

                if (form.province.value.trim() === "") {
                    isValid = false;
                    document.getElementById("province-error").textContent = "Province is required.";
                }

                if (form.town_city.value.trim() === "") {
                    isValid = false;
                    document.getElementById("town_city-error").textContent = "Town city is required.";
                }

                if (zipCodeInput.value.trim() === "") {
                    isValid = false;
                    document.getElementById("zip_code-error").textContent = "Zip code is required.";

                    zipCodeInput.addEventListener('input', function() {
                        document.getElementById("zip_code-error").textContent = "";
                    });
                }

                if (fatherNameInput.value.trim() === "") {
                    isValid = false;
                    document.getElementById("father_name-error").textContent = "Father name is required.";

                    fatherNameInput.addEventListener('input', function() {
                        document.getElementById("father_name-error").textContent = "";
                    });
                }

                if (fatherAddressInput.value.trim() === "") {
                    isValid = false;
                    document.getElementById("father_address-error").textContent = "Address is required.";

                    fatherAddressInput.addEventListener('input', function() {
                        document.getElementById("father_address-error").textContent = "";
                    });
                }

                if (fatherWorkInput.value.trim() === "") {
                    isValid = false;
                    document.getElementById("father_work-error").textContent = "Occupation is required.";

                    fatherWorkInput.addEventListener('input', function() {
                        document.getElementById("father_work-error").textContent = "";
                    });
                }

                if (motherNameInput.value.trim() === "") {
                    isValid = false;
                    document.getElementById("mother_name-error").textContent = "Mother name is required.";

                    motherNameInput.addEventListener('input', function() {
                        document.getElementById("mother_name-error").textContent = "";
                    });
                }

                if (motherAddressInput.value.trim() === "") {
                    isValid = false;
                    document.getElementById("mother_address-error").textContent = "Address is required.";

                    motherAddressInput.addEventListener('input', function() {
                        document.getElementById("mother_address-error").textContent = "";
                    });
                }

                if (motherWorkInput.value.trim() === "") {
                    isValid = false;
                    document.getElementById("mother_work-error").textContent = "Occupation is required.";

                    motherWorkInput.addEventListener('input', function() {
                        document.getElementById("mother_work-error").textContent = "";
                    });
                }

                if (grossIncomeInput.value.trim() === "") {
                    isValid = false;
                    document.getElementById("gross_income-error").textContent = "Gross income is required.";

                    grossIncomeInput.addEventListener('input', function() {
                        document.getElementById("gross_income-error").textContent = "";
                    });
                }

                if (noSiblingsInput.value.trim() === "") {
                    isValid = false;
                    document.getElementById("num_siblings-error").textContent = "Number of siblings is required.";

                    noSiblingsInput.addEventListener('input', function() {
                        document.getElementById("num_siblings-error").textContent = "";
                    });
                }


                var fileInputs = form.querySelectorAll('.file-input');

                for (var i = 0; i < fileInputs.length; i++) {
                    (function() {
                        var fileInput = fileInputs[i];
                        var requirementLabel = fileInput.previousElementSibling;
                        var checkbox = fileInput.previousElementSibling.previousElementSibling;
                        var requirementValidation = fileInput.parentElement.querySelector('.requirement-validation');

                        fileInput.addEventListener('change', function() {
                            if (fileInput.value.trim() !== '') {
                                var fileName = fileInput.value;
                                var validExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                                var fileExtension = fileName.split('.').pop().toLowerCase();

                                // Check if the file extension is valid
                                if (validExtensions.indexOf(fileExtension) === -1) {
                                    isValid = false;
                                    requirementValidation.textContent = 'Invalid file format. Supported formats: PDF, JPG, JPEG, PNG.';
                                    requirementValidation.style.color = 'red';
                                } else {
                                    // Clear the validation message when a valid file is uploaded
                                    requirementValidation.textContent = '';
                                    requirementValidation.style.color = 'inherit';
                                }
                            }
                        });

                        if (fileInput.value.trim() === '') {
                            isValid = false;
                            // Display the validation message
                            requirementValidation.textContent = '*Must upload a valid file (PDF, JPG, JPEG, PNG)';
                            requirementLabel.style.color = 'red';
                            // You can also uncheck the corresponding checkbox if needed
                            // checkbox.checked = false;
                        } else {
                            // Reset the validation message
                            requirementValidation.textContent = '';
                            requirementLabel.style.color = 'inherit';
                        }
                    })();
                }

                const checkboxes = form.querySelectorAll('.file-input-checkbox');

                fileInputs.forEach((fileInput, index) => {
                    fileInput.addEventListener('change', () => {
                        const checkbox = document.getElementById(`checkbox-${index}`);
                        checkbox.disabled = true;
                        checkbox.checked = true;
                    });
                });




                if (!isValid) {
                    event.preventDefault(); // Prevent form submission if validation fails
                }
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            // Your existing code...

            fetch('philippine_provinces_cities_municipalities_and_barangays_2019v2.json')
                .then(response => response.json())
                .then(data => {
                    const regionDropdown = document.getElementById('region');
                    const provinceDropdown = document.getElementById('province');
                    const townCityDropdown = document.getElementById('town_city');
                    const barangayDropdown = document.getElementById('barangay');

                    for (const regionCode in data) {
                        const region = data[regionCode];
                        const option = document.createElement('option');
                        option.value = regionCode;
                        option.textContent = region.region_name + ' - ' + regionCode;
                        regionDropdown.appendChild(option);
                    }

                    regionDropdown.addEventListener('change', () => {
                        const selectedRegionCode = regionDropdown.value;
                        const provinceData = data[selectedRegionCode].province_list;

                        // Populate the province dropdown based on the selected region
                        provinceDropdown.innerHTML = '';
                        for (const provinceName in provinceData) {
                            const option = document.createElement('option');
                            option.value = provinceName;
                            option.textContent = provinceName;
                            provinceDropdown.appendChild(option);
                        }

                        // Clear the town/city and barangay dropdowns
                        townCityDropdown.innerHTML = '';
                        barangayDropdown.innerHTML = '';
                    });

                    provinceDropdown.addEventListener('change', () => {
                        const selectedRegionCode = regionDropdown.value;
                        const selectedProvince = provinceDropdown.value;
                        const townCityData = data[selectedRegionCode].province_list[selectedProvince].municipality_list;

                        // Populate the town/city dropdown based on the selected province
                        townCityDropdown.innerHTML = '';
                        for (const townCityName in townCityData) {
                            const option = document.createElement('option');
                            option.value = townCityName;
                            option.textContent = townCityName;
                            townCityDropdown.appendChild(option);
                        }

                        // Clear the barangay dropdown
                        barangayDropdown.innerHTML = '';
                    });

                    townCityDropdown.addEventListener('change', () => {
                        const selectedRegionCode = regionDropdown.value;
                        const selectedProvince = provinceDropdown.value;
                        const selectedTownCity = townCityDropdown.value;
                        const barangayData = data[selectedRegionCode].province_list[selectedProvince].municipality_list[selectedTownCity].barangay_list;

                        barangayDropdown.innerHTML = '';
                        for (const barangayName of barangayData) {
                            const option = document.createElement('option');
                            option.value = barangayName;
                            option.textContent = barangayName;
                            barangayDropdown.appendChild(option);
                        }
                    });
                })
                .catch(error => {
                    console.error('Error loading JSON file:', error);
                });
        });
    </script>
</body>

</html>