<?php
include '../include/connection.php';
session_name("OsaSession");
session_start();
$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
  header('location: osa_login.php');
  exit();
}

if (isset($_GET['logout'])) {
  unset($admin_id);
  session_destroy();
  header('location: osa_login.php');
  exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/preview_application1.css">
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
    <form action="" method="POST" enctype="multipart/form-data">
      <div class="container">
        <div class="form first">
          <h4 class="label1">PERSONAL INFORMATION:</h4>
          <br>
          <div class="details personal">
            <div class="fields">
              <div class="input-field">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" placeholder="Enter your lastname" disabled>
                <div class="validation-message" id="last_name-error"></div>
              </div>
              <div class="input-field">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" placeholder="Enter your firstname" disabled>
                <div class="validation-message" id="first_name-error"></div>
              </div>
              <div class="input-field">
                <label for="middle_name">Middle Name</label>
                <input type="text" id="middle_name" name="middle_name" placeholder="Enter your middlename" disabled>
                <div class="validation-message" id="middle_name-error"></div>
              </div>
              <div class="input-field">
                <label>Date of Birth</label>
                <input type="date" id="dob" name="dob" placeholder="Enter birthdate" disabled>
                <div class="validation-message" id="date_birth-error"></div>
              </div>
              <div class="input-field">
                <label>Place of Birth</label>
                <input type="text" id="pob" name="pob" placeholder="Enter birthplace" disabled>
                <div class="validation-message" id="pob-error"></div>
              </div>
              <div class="input-field">
                <label>Sex</label>
                <select id="gender" name="gender" disabled>
                  <option disabled selected>Select sex</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                </select>
                <div class="validation-message" id="gender-error"></div>
              </div>
            </div>

            <div class="fields">
              <div class="input-field">
                <label>Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" disabled>
                <div class="validation-message" id="email-error"></div>
              </div>
              <div class="input-field">
                <label>Mobile Number</label>
                <input type="number" id="mobile_num" name="mobile_num" placeholder="09XXXXXXXXX" disabled pattern="[0-9]{11}">
                <div class="validation-message" id="mobile_num-error"></div>
              </div>

              <div class="input-field">
                <label>Citizenship</label>
                <input type="text" id="citizenship" name="citizenship" placeholder="Enter your citizenship" disabled>
                <div class="validation-message" id="citizenship-error"></div>
              </div>
            </div>




            <div class="fields">
              <div class="input-field">
                <label>School ID Number</label>
                <input type="number" id="id_number" name="id_number" placeholder="2XXXX21" disabled>
                <div class="validation-message" id="id_number-error"></div>
              </div>

              <div class="input-field">
                <label>Course</label>
                <select id="course" name="course" disabled>
                  <option disabled selected>Select course</option>
                  <option value="BSIT">BSIT</option>
                  <option value="BSA">BSA</option>
                </select>
                <div class="validation-message" id="course-error"></div>
              </div>

              <div class="input-field">
                <label>Year Level</label>
                <input type="number" id="year_lvl" name="year_lvl" placeholder="Year level" disabled>
                <div class="validation-message" id="year_lvl-error"></div>
              </div>
            </div>
            <hr>
            <div class="input-field">
              <h4 class="label1">Permanent Address</h4>
              <div class="address-inputs">
                <div class="address-container">
                  <label for="region">Region</label>
                  <select id="region" name="region" disabled></select>
                  <div class="validation-message" id="region-error"></div>
                </div>

                <div class="address-container">
                  <label for="province">Province</label>
                  <select id="province" name="province" disabled></select>
                  <div class="validation-message" id="province-error"></div>
                </div>

                <div class="address-container">
                  <label for="town_city">Town City</label>
                  <select id="town_city" name="town_city" disabled></select>
                  <div class="validation-message" id="town_city-error"></div>
                </div>

                <div class="address-container">
                  <label for="barangay">Barangay</label>
                  <select id="barangay" name="barangay" disabled></select>
                  <div class="validation-message" id="barangay-error"></div>
                </div>

                <div class="address-container">

                  <label for="zip_code">Zip Code</label>
                  <input type="number" id="zip_code" name="zip_code" placeholder="Zip Code" disabled>
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
                  <label>Father Name</label>
                  <input type="text" id="father_name" name="father_name" placeholder="Enter father name" disabled>
                  <div class="validation-message" id="father_name-error"></div>
                  <label>Address</label>
                  <input type="text" id="father_address" name="father_address" placeholder="Enter address" disabled>
                  <div class="validation-message" id="father_address-error"></div>
                  <label>Occupation</label>
                  <input type="text" id="father_work" name="father_work" placeholder="Enter Occupation" disabled>
                  <div class="validation-message" id="father_work-error"></div>
                </div>
              </div>

              <div class="form">
                <div class="input-field">
                  <span class="title"> MOTHER </span>
                  <hr>
                  <label>Mother Name</label>
                  <input type="text" id="mother_name" name="mother_name" placeholder="Enter mother name" disabled>
                  <div class="validation-message" id="mother_name-error"></div>
                  <label>Address</label>
                  <input type="text" id="mother_address" name="mother_address" placeholder="Enter address" disabled>
                  <div class="validation-message" id="mother_address-error"></div>
                  <label>Occupation</label>
                  <input type="text" id="mother_work" name="mother_work" placeholder="Enter Occupation" disabled>
                  <div class="validation-message" id="mother_work-error"></div>
                </div>
              </div>
            </div>
          </div>
          <hr>
          <div class="select-input-field">
                <div class="input-field">
                <label>Total Gross Income</label>
                <input type="email" id="email" name="email" placeholder="Gross income" disabled>
                <div class="validation-message" id="email-error"></div>
              </div>
              <div class="input-field">
                <label>No. of Siblings in the family</label>
                <input type="number" id="mobile_num" name="mobile_num" placeholder="Number of siblings" disabled>
                <div class="validation-message" id="mobile_num-error"></div>
              </div>
              </div>
        </div>

        <div class="form_3 data_info">
          <h4 class="label1">Requirements Upload:</h4>
          <hr><br>
          <div class="details requirements">
            <div class="input-file">
              <input id="checkbox" class="checkbox" type="checkbox" disabled>
              <label class="requirement-label"> *List of requirements</label>
              <div class="requirement-validation" id="requirement-validation"></div>
              <input id="file-input" class="file-input" type="file" name="file[]" disabled>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</body>

</html>