<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_name("ApplicantSession");
session_start();
include('connection.php');

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
        echo "<script>alert('You have already applied for this scholarship.')</script>";
        echo "<script>window.location.href = 'index.php';</script>";
        exit();
    }

    if (isset($_FILES["file"])) {
        $file = $_FILES["file"];
        $fileCount = count($file["name"]);

        $validImageExtension = ['jpg', 'jpeg', 'png'];
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


    // ...

    if (!$stmt->execute()) {
        echo "<script>
            alert('Failed to insert application');
            window.location.href = 'index.php';
        </script>";
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
            // Handle the case when the user's image is not found in tbl_user table
            // You can display an error message or set a default image
            $userImage = 'default.jpg'; // Set a default image here
        }
        // Set 'is_read' to 'unread'
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

        echo "<script>
            alert('Successfully Added');
            window.location.href = 'index.php';
        </script>";
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

    <title>Application Form</title>
    <style>
        .checkbox {
    width: 16px; /* Adjust the width as needed */
    height: 16px; /* Adjust the height as needed */
}
    </style>
</head>


<body>
    <?php include('header.php') ?>
    <div class="wrapper">
        <div class="header">
            
        </div>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="container">
                <div class="form first">
                <h3 style="color: darkgreen">Requirements Upload:</h3>
                    <hr>
                    <div class="details requirements">
                        <?php foreach ($requirements as $index => $requirement) { ?>
                            <div class="input-file">
                            <input id="checkbox-<?php echo $index; ?>" class="checkbox" type="checkbox" disabled>
                            <label class="requirement-label" for="file-input-<?php echo $index; ?>"><?php echo $requirement; ?></label>
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
                        
                        <div class="input-field">
                            <h4>Permanent Address</h4>
                            <div class="address-inputs">
                                <input type="text" name="barangay" placeholder="Street & Barangay" value="<?php echo $barangay; ?>" required>
                                <input type="text" name="town_city" placeholder="Town/City/Municipality" value="<?php echo $town_city; ?>" required>
                                <input type="text" name="province" placeholder="Province" value="<?php echo $province; ?>" required>
                                <input type="number" name="zip_code" placeholder="Zip Code" value="<?php echo $zip_code; ?>" required>
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
                                    <input type="text" name="father_name" placeholder="Enter father name" value="<?php echo $father_name; ?>" required>
                                    <label>Address</label>
                                    <input type="text" name="father_address" placeholder="Enter address" value="<?php echo $father_address; ?>" required>
                                    <label>Occupation</label>
                                    <input type="text" name="father_work" placeholder="Enter Occupation" value="<?php echo $father_work; ?>" required>
                                </div>
                            </div>

                            <div class="form">
                                <div class="input-field">
                                    <span class="title"> MOTHER </span>
                                    <hr>
                                    <label>Name</label>
                                    <input type="text" name="mother_name" placeholder="Enter mother name" value="<?php echo $mother_name; ?>" required>
                                    <label>Address</label>
                                    <input type="text" name="mother_address" placeholder="Enter address" value="<?php echo $mother_address; ?>" required>
                                    <label>Occupation</label>
                                    <input type="text" name="mother_work" placeholder="Enter Occupation" value="<?php echo $mother_work; ?>" required>
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

                <
                    
                </div>
            </div>
        </form>

        <div class="modal_wrapper">
            <div class="shadow"></div>
            <div class="success_wrap">
                <span class="modal_icon"><ion-icon name="checkmark-sharp"></ion-icon></span>
                <p>You have successfully completed the process.</p>
            </div>
        </div>
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
                if (validateForm(form_1)) {
                    form_1.style.display = "none";
                    form_2.style.display = "block";

                    form_1_btns.style.display = "none";
                    form_2_btns.style.display = "flex";

                    form_2_progessbar.classList.add("active");
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
                if (validateForm(form_2)) {
                    form_2.style.display = "none";
                    form_3.style.display = "block";

                    form_2_btns.style.display = "none";
                    form_3_btns.style.display = "flex";

                    form_3_progessbar.classList.add("active");
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
                if (validateForm(form_3)) {
                    modal_wrapper.classList.add("active");
                } else {
                    alert("Please fill in all required fields!");
                }
            });

            shadow.addEventListener("click", function() {
                modal_wrapper.classList.remove("active");
            });

            // Form validation function
            function validateForm(form) {
                // Get all the input fields within the specified form
                var inputs = form.querySelectorAll('input, select');

                // Iterate over each input field and perform validation checks
                for (var i = 0; i < inputs.length; i++) {
                    var input = inputs[i];

                    // Check if the input field is required and empty
                    if (input.hasAttribute('required') && input.value.trim() === '') {
                        // Display an error message or highlight the input field
                        alert('Please fill in all required fields!');
                        return false; // Form is not valid
                    }
                }

                return true; // Form is valid
            }
            // Get all file input elements
            const fileInputs = document.querySelectorAll('.file-input');

            fileInputs.forEach((fileInput, index) => {
                fileInput.addEventListener('change', () => {
                    const checkbox = document.getElementById(`checkbox-${index}`);
                    checkbox.disabled = false;
                    checkbox.checked = true;
                });
            });

        });
    </script>
</body>

</html>