<?php
session_name("ApplicantSession");
session_start();
include('../include/connection.php');

$last_name = $_POST['last_name'] ?? '';
$first_name = $_POST['first_name'] ?? '';
$middle_name = $_POST['middle_name'] ?? '';
$dob = $_POST['dob'] ?? '';
$pob = $_POST['pob'] ?? '';
$age = $_POST['age'] ?? '';
$gender = $_POST['gender'] ?? '';
$email = $_POST['email'] ?? '';
$course = $_POST['course'] ?? '';
$mobile_num = $_POST['mobile_num'] ?? '';
$citizenship = $_POST['citizenship'] ?? '';
$civil_status = $_POST['civil_status'] ?? '';
$religion = $_POST['religion'] ?? '';
$barangay = $_POST['barangay'] ?? '';
$town_city = $_POST['town_city'] ?? '';
$province = $_POST['province'] ?? '';
$zip_code = $_POST['zip_code'] ?? '';
$id_number = $_POST['id_number'] ?? '';
$father_lname = $_POST['father_lname'] ?? '';
$father_fname = $_POST['father_fname'] ?? '';
$father_mname = $_POST['father_mname'] ?? '';
$father_work = $_POST['father_work'] ?? '';
$mother_sname = $_POST['mother_sname'] ?? '';
$mother_fname = $_POST['mother_fname'] ?? '';
$mother_mname = $_POST['mother_mname'] ?? '';
$mother_work = $_POST['mother_work'] ?? '';
$primary_school = $_POST['primary_school'] ?? '';
$secondary_school = $_POST['secondary_school'] ?? '';
$tertiary_school = $_POST['tertiary_school'] ?? '';
$prim_year_grad = $_POST['prim_year_grad'] ?? '';
$sec_year_grad = $_POST['sec_year_grad'] ?? '';
$ter_year_grad = $_POST['ter_year_grad'] ?? '';
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

  $sql = "SELECT * FROM tbl_scholarship_1_form WHERE applicant_name = ? AND scholarship_name = ?";
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

        if (move_uploaded_file($tmpName, $targetDirectory . $newFileName)) {
          $uploadedFiles[] = $newFileName;
        }
      }
    }

    $fileNamesString = implode(',', $uploadedFiles);
  }

  $isValid = true;

  if (empty($last_name) || empty($first_name) || empty($middle_name) || empty($dob) || empty($pob) || empty($age) || empty($gender) || empty($email) || empty($course) || empty($mobile_num) || empty($citizenship) || empty($civil_status) || empty($religion) || empty($barangay) || empty($town_city) || empty($province) || empty($zip_code) || empty($id_number) || empty($father_lname) || empty($father_fname) || empty($father_mname) || empty($father_work) || empty($mother_sname) || empty($mother_fname) || empty($mother_mname) || empty($mother_work) || empty($primary_school) || empty($prim_year_grad) || empty($secondary_school) || empty($sec_year_grad) || empty($tertiary_school) || empty($ter_year_grad)) {

    $isValid = false;
    echo "<script>alert('Please fill in all required fields.')</script>";
  }

  if ($isValid) {
    $sql = "INSERT INTO `tbl_scholarship_1_form` (user_id, image, applicant_name, scholarship_name, last_name, first_name, middle_name, dob, pob, age, gender, email, course, mobile_num, citizenship, civil_status, religion, barangay, town_city, province, zip_code, id_number, father_lname, father_fname, father_mname, father_work, mother_sname, mother_fname, mother_mname, mother_work, primary_school, prim_year_grad, secondary_school, sec_year_grad, tertiary_school, ter_year_grad, file, scholarship_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $dbConn->prepare($sql);

    if ($stmt->error) {
      die('Error in SQL query: ' . $stmt->error);
    }

    $stmt->bind_param(
      "issssssssssssssssssssssssssssssssssssi",
      $user_id,
      $image,
      $full_name,
      $scholarship,
      $last_name,
      $first_name,
      $middle_name,
      $dob,
      $pob,
      $age,
      $gender,
      $email,
      $course,
      $mobile_num,
      $citizenship,
      $civil_status,
      $religion,
      $barangay,
      $town_city,
      $province,
      $zip_code,
      $id_number,
      $father_lname,
      $father_fname,
      $father_mname,
      $father_work,
      $mother_sname,
      $mother_fname,
      $mother_mname,
      $mother_work,
      $primary_school,
      $prim_year_grad,
      $secondary_school,
      $sec_year_grad,
      $tertiary_school,
      $ter_year_grad,
      $fileNamesString,
      $scholarship_id
    );

    if (!$stmt->execute()) {
      $errorMessage = "Failed to insert application.";
    } else {

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

      $notificationMessage = "New application has been submitted";
      $sql = "INSERT INTO tbl_notifications (user_id, message, image, is_read) VALUES (?, ?, ?, ?)";
      $stmt = $dbConn->prepare($sql);
      $stmt->bind_param("ssss", $user_id, $notificationMessage, $userImage, $is_read);
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
                <label for="zip_code">Age</label>
                <input type="number" id="age" name="age" placeholder="Age" value="<?php echo $age; ?>" required>
                <div class="validation-message" id="age-error"></div>
              </div>

              <div class="input-field">
                <label>Citizenship</label>
                <input type="text" id="citizenship" name="citizenship" placeholder="Enter your citizenship" value="<?php echo $citizenship; ?>" required>
                <div class="validation-message" id="citizenship-error"></div>
              </div>

              <div class="input-field">
                <label>Civil Status</label>
                <select id="civil_status" name="civil_status" required>
                  <option disabled selected>Select status</option>
                  <option value="SINGLE">SINGLE</option>
                  <option value="WIDOWED">WIDOWED</option>
                  <option value="SEPERATED">SEPERATED</option>
                  <option value="MARRIED">MARRIED</option>
                </select>
                <div class="validation-message" id="civil_status-error"></div>
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
                <label>Religion</label>
                <input type="text" id="religion" name="religion" placeholder="Enter your religion" value="<?php echo $religion; ?>" required>
                <div class="validation-message" id="religion-error"></div>
              </div>

            </div>




            <div class="fields">
              <div class="input-field">
                <label>School ID Number</label>
                <input type="number" id="id_number" name="id_number" placeholder="2XXXX21" value="<?php echo $id_number; ?>" required>
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
                <label>Year Level</label>
                <select id="year_lvl" name="year_lvl" required>
                  <option disabled selected>Select year level</option>
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
                  <label>Last Name</label>
                  <input type="text" id="father_lname" name="father_lname" placeholder="Enter your father's lastname" value="<?php echo $father_lname; ?>" required>
                  <div class="validation-message" id="father_lname-error"></div>

                  <label>First Name</label>
                  <input type="text" id="father_fname" name="father_fname" placeholder="Enter your father's firstname" value="<?php echo $father_fname; ?>" required>
                  <div class="validation-message" id="father_fname-error"></div>

                  <label>MIddle Name</label>
                  <input type="text" id="father_mname" name="father_mname" placeholder="Enter your father's middlename" value="<?php echo $father_mname; ?>" required>
                  <div class="validation-message" id="father_mname-error"></div>

                  <label>Occupation</label>
                  <input type="text" id="father_work" name="father_work" placeholder="Enter Occupation" value="<?php echo $father_work; ?>" required>
                  <div class="validation-message" id="father_work-error"></div>
                </div>
              </div>

              <div class="form">
                <div class="input-field">
                  <span class="title"> MOTHER </span>
                  <hr>
                  <label>Surname</label>
                  <input type="text" id="mother_sname" name="mother_sname" placeholder="Enter mother's surname" value="<?php echo $mother_sname; ?>" required>
                  <div class="validation-message" id="mother_sname-error"></div>

                  <label>First Name</label>
                  <input type="text" id="mother_fname" name="mother_fname" placeholder="Enter mother's firstname" value="<?php echo $mother_fname; ?>" required>
                  <div class="validation-message" id="mother_fname-error"></div>

                  <label>Middle Name</label>
                  <input type="text" id="mother_mname" name="mother_mname" placeholder="Enter mother's middlename" value="<?php echo $mother_mname; ?>" required>
                  <div class="validation-message" id="mother_mname-error"></div>

                  <label>Occupation</label>
                  <input type="text" id="mother_work" name="mother_work" placeholder="Enter Occupation" value="<?php echo $mother_work; ?>" required>
                  <div class="validation-message" id="mother_work-error"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="form first">
          <h4 class="label1">EDUCATIONAL BACKGROUND:</h4>
          <br>
          <div class="select-input-field">
            <div class="input-field">
              <label>Primary School</label>
              <input type="text" id="primary_school" name="primary_school" placeholder="Name of your Primary School" value="<?php echo $primary_school; ?>" required>
              <div class="validation-message" id="primary_school-error"></div>
            </div>
            <div class="input-field">
              <label>Year Graduated</label>
              <input type="number" id="prim_year_grad" name="prim_year_grad" placeholder="Primary year graduated" value="<?php echo $prim_year_grad; ?>" required>
              <div class="validation-message" id="prim_year_grad-error"></div>
            </div>

            <div class="input-field">
              <label>Secondary School</label>
              <input type="text" id="secondary_school" name="secondary_school" placeholder="Name of your Secondary School" value="<?php echo $secondary_school; ?>" required>
              <div class="validation-message" id="secondary_school-error"></div>
            </div>
            <div class="input-field">
              <label>Year Graduated</label>
              <input type="number" id="sec_year_grad" name="sec_year_grad" placeholder="Primary year graduated" value="<?php echo $sec_year_grad; ?>" required>
              <div class="validation-message" id="sec_year_grad-error"></div>
            </div>


            <div class="input-field">
              <label>Tertiary School</label>
              <input type="text" id="tertiary_school" name="tertiary_school" placeholder="Name of your Tertiary School" value="<?php echo $tertiary_school; ?>" required>
              <div class="validation-message" id="tertiary_school-error"></div>
            </div>
            <div class="input-field">
              <label>Year Graduated</label>
              <input type="number" id="ter_year_grad" name="ter_year_grad" placeholder="Primary year graduated" value="<?php echo $ter_year_grad; ?>" required>
              <div class="validation-message" id="ter_year_grad-error"></div>
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
    document.addEventListener("DOMContentLoaded", function() {
      const form = document.querySelector("form");
      const doneButton = document.querySelector(".btn_done");

      doneButton.addEventListener("click", function(event) {
        let isValid = true;

        // Reset validation messages
        const validationMessages = document.querySelectorAll(".validation-message");
        validationMessages.forEach(function(message) {
          message.textContent = "";
        });

        // Add validation logic here for each field
        if (form.last_name.value.trim() === "") {
          isValid = false;
          document.getElementById("last_name-error").textContent = "Last Name is required.";
        }
        if (form.first_name.value.trim() === "") {
          isValid = false;
          document.getElementById("first_name-error").textContent = "First Name is required.";
        }
        if (form.middle_name.value.trim() === "") {
          isValid = false;
          document.getElementById("middle_name-error").textContent = 'Middle Name is required';
        }

        var dobInput = document.querySelector('input[name="dob"]');
        var dobError = document.getElementById('date_birth-error');

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

        if (form.pob.value.trim() === "") {
          isValid = false;
          document.getElementById("pob-error").textContent = "Place of birth is required.";
        }

        if (form.age.value.trim() === "") {
          isValid = false;
          document.getElementById("age-error").textContent = "Age is required.";
        }

        if (form.citizenship.value.trim() === "") {
          isValid = false;
          document.getElementById("citizenship-error").textContent = "Citizenship is required.";
        }


        var civil_status = document.getElementById('civil_status');
        var civil_status_error = document.getElementById('civil_status-error');
        var selectedCivilStatus = civil_status.value;

        if (selectedCivilStatus === 'Select status') {
          civil_status_error.textContent = 'Please select a civil status.';
          isValid = false;
        } else {
          civil_status_error.textContent = '';
        }

        var gender = document.getElementById('gender');
        var gender_error = document.getElementById('gender-error');
        var selectedGender = gender.value;

        if (selectedGender === 'Select sex') {
          gender_error.textContent = 'Please select a gender.';
          isValid = false;
        } else {
          gender_error.textContent = '';
        }

        var emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
        var mobileNumRegex = /^[0-9]{11}$/;
        var idNumberRegex = /^[0-9]{7}$/;

        if (email.value.trim() === '') {
          document.getElementById("email-error").textContent = 'Email is required';
          isValid = false;
        } else if (!emailRegex.test(email.value.trim())) {
          document.getElementById("email-error").textContent = 'Please enter a valid email address';
          isValid = false;
        }

        if (form.mobile_num.value.trim() === '') {
          document.getElementById("mobile_num-error").textContent = "Mobile number is required.";
          isValid = false;
        } else if (!mobileNumRegex.test(mobile_num.value.trim())) {
          document.getElementById("mobile_num-error").textContent = 'Please enter a valid 11-digit mobile number';
          isValid = false;
        }

        if (form.religion.value.trim() === "") {
          isValid = false;
          document.getElementById("religion-error").textContent = "Religion is required.";
        }

        var course = document.getElementById('course');
        var course_error = document.getElementById('course-error');
        var selectedCourse = course.value;

        if (selectedCourse === 'Select course') {
          document.getElementById("course-error").textContent = 'Please select a course';
          isValid = false;
        }

        var year_lvl = document.getElementById('year_lvl');
        var year_lvl_error = document.getElementById('year_lvl-error');
        var selectedYearLevel = year_lvl.value;

        if (selectedYearLevel === 'Select year level') {
          document.getElementById("year_lvl-error").textContent = 'Please select your year level';
          isValid = false;
        }

        if (id_number.value.trim() === '') {
          document.getElementById("id_number-error").textContent = 'Id Number is required';
          isValid = false;
        } else if (!idNumberRegex.test(id_number.value.trim())) {
          document.getElementById("id_number-error").textContent = 'Please enter a valid 7-digit ID number';
          isValid = false;
        }


        if (form.region.value.trim() === "") {
          isValid = false;
          document.getElementById("region-error").textContent = "Region is required.";
        }

        if (form.barangay.value.trim() === "") {
          isValid = false;
          document.getElementById("barangay-error").textContent = "Barangay is required.";
        }

        if (form.province.value.trim() === "") {
          isValid = false;
          document.getElementById("province-error").textContent = "Province is required.";
        }

        if (form.town_city.value.trim() === "") {
          isValid = false;
          document.getElementById("town_city-error").textContent = "Town city is required.";
        }

        if (form.zip_code.value.trim() === "") {
          isValid = false;
          document.getElementById("zip_code-error").textContent = "Zip code is required.";
        }

        if (form.father_lname.value.trim() === "") {
          isValid = false;
          document.getElementById("father_lname-error").textContent = "Father' lastname is required.";
        }

        if (form.father_fname.value.trim() === "") {
          isValid = false;
          document.getElementById("father_fname-error").textContent = "Father' firstname is required.";
        }

        if (form.father_mname.value.trim() === "") {
          isValid = false;
          document.getElementById("father_mname-error").textContent = "Father' middlename is required.";
        }

        if (form.father_work.value.trim() === "") {
          isValid = false;
          document.getElementById("father_work-error").textContent = "Occupation is required.";
        }

        if (form.mother_sname.value.trim() === "") {
          isValid = false;
          document.getElementById("mother_sname-error").textContent = "Mother's surname is required.";
        }

        if (form.mother_fname.value.trim() === "") {
          isValid = false;
          document.getElementById("mother_fname-error").textContent = "Mother's firstname is required.";
        }

        if (form.mother_mname.value.trim() === "") {
          isValid = false;
          document.getElementById("mother_mname-error").textContent = "Mother's middlename is required.";
        }

        if (form.mother_work.value.trim() === "") {
          isValid = false;
          document.getElementById("mother_work-error").textContent = "Occupation is required.";
        }

        if (form.primary_school.value.trim() === "") {
          isValid = false;
          document.getElementById("primary_school-error").textContent = "Primary School is required.";
        }

        if (form.prim_year_grad.value.trim() === "") {
          isValid = false;
          document.getElementById("prim_year_grad-error").textContent = "Primary Year Graduated is required.";
        }

        if (form.secondary_school.value.trim() === "") {
          isValid = false;
          document.getElementById("secondary_school-error").textContent = "Secondary School is required.";
        }

        if (form.sec_year_grad.value.trim() === "") {
          isValid = false;
          document.getElementById("sec_year_grad-error").textContent = "Secondary Year Graduated is required.";
        }

        if (form.tertiary_school.value.trim() === "") {
          isValid = false;
          document.getElementById("tertiary_school-error").textContent = "Tertiary School is required.";
        }

        if (form.ter_year_grad.value.trim() === "") {
          isValid = false;
          document.getElementById("ter_year_grad-error").textContent = "Tertiary Year Graduated is required.";
        }



        var fileInputs = form.querySelectorAll('.file-input');

        for (var i = 0; i < fileInputs.length; i++) {
          var fileInput = fileInputs[i];
          var requirementLabel = fileInput.previousElementSibling;
          var checkbox = fileInput.previousElementSibling.previousElementSibling;
          var requirementValidation = fileInput.parentElement.querySelector('.requirement-validation');

          fileInput.addEventListener('change', function() {
            if (fileInput.value.trim() !== '') {
              requirementValidation.textContent = '';
              requirementValidation.style.color = 'inherit';
            }
          });

          if (fileInput.value.trim() === '') {
            isValid = false;
            requirementValidation.textContent = '*Must upload the photo'
            requirementLabel.style.color = 'red';
          } else {
            requirementValidation.textContent = '';
            requirementLabel.style.color = 'inherit';
          }
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
          event.preventDefault();
        }
      });
    });

    document.addEventListener("DOMContentLoaded", function() {
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

            provinceDropdown.innerHTML = '';
            for (const provinceName in provinceData) {
              const option = document.createElement('option');
              option.value = provinceName;
              option.textContent = provinceName;
              provinceDropdown.appendChild(option);
            }

            townCityDropdown.innerHTML = '';
            barangayDropdown.innerHTML = '';
          });

          provinceDropdown.addEventListener('change', () => {
            const selectedRegionCode = regionDropdown.value;
            const selectedProvince = provinceDropdown.value;
            const townCityData = data[selectedRegionCode].province_list[selectedProvince].municipality_list;

            townCityDropdown.innerHTML = '';
            for (const townCityName in townCityData) {
              const option = document.createElement('option');
              option.value = townCityName;
              option.textContent = townCityName;
              townCityDropdown.appendChild(option);
            }

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