<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_name("ApplicantSession");
session_start();
include('connection.php');

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve updated details from the form
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $dob = $_POST['dob'];
    $pob = $_POST['pob'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $mobile_num = $_POST['mobile_num'];
    $citizenship = $_POST['citizenship'];
    $barangay = $_POST['barangay'];
    $town_city = $_POST['town_city'];
    $province = $_POST['province'];
    $zip_code = $_POST['zip_code'];
    $id_number = $_POST['id_number'];
    $father_name = $_POST['father_name'];
    $father_address = $_POST['father_address'];
    $father_work = $_POST['father_work'];
    $mother_name = $_POST['mother_name'];
    $mother_address = $_POST['mother_address'];
    $mother_work = $_POST['mother_work'];

    // Perform the database update
    $sql = "UPDATE tbl_userapp SET
            last_name = ?,
            first_name = ?,
            middle_name = ?,
            dob = ?,
            pob = ?,
            gender = ?,
            email = ?,
            mobile_num = ?,
            citizenship = ?,
            barangay = ?,
            town_city = ?,
            province = ?,
            zip_code = ?,
            id_number = ?,
            father_name = ?,
            father_address = ?,
            father_work = ?,
            mother_name = ?,
            mother_address = ?,
            mother_work = ?
            WHERE user_id = ?";
    
    $stmt = $dbConn->prepare($sql);
    $stmt->bind_param(
        "ssssssssssssssssssssi",
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
        $user_id
    );

    if ($stmt->execute()) {
        echo "<script>
            alert('Details updated successfully');
            window.location.href = 'application_status.php'; // Redirect to the desired page
        </script>";
    } else {
        echo "Error updating details: " . $stmt->error;
    }
}

// Fetch the current details from the database
$sql = "SELECT * FROM tbl_userapp WHERE user_id = ?";
$stmt = $dbConn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // Assign the fetched values to variables
    $last_name = $row['last_name'];
    $first_name = $row['first_name'];
    $middle_name = $row['middle_name'];
    $dob = $row['dob'];
    $pob = $row['pob'];
    $gender = $row['gender'];
    $email = $row['email'];
    $mobile_num = $row['mobile_num'];
    $citizenship = $row['citizenship'];
    $barangay = $row['barangay'];
    $town_city = $row['town_city'];
    $province = $row['province'];
    $zip_code = $row['zip_code'];
    $id_number = $row['id_number'];
    $father_name = $row['father_name'];
    $father_address = $row['father_address'];
    $father_work = $row['father_work'];
    $mother_name = $row['mother_name'];
    $mother_address = $row['mother_address'];
    $mother_work = $row['mother_work'];
} else {
    // Handle the case when user details are not found in the database
    die('User details not found');
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
</head>
        
<body>
    <?php include('header.php') ?>
    <div class="wrapper">

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="container">
                <div class="form first">
                    <h3 style="color:darkgreen">PERSONAL INFORMATION:</h3>
                    <br>
                    <div class="details personal">
                        <div class="fields">
                            <div class="input-field">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo $last_name; ?>" required>
                            </div>
                            <div class="input-field">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo $first_name; ?>" required>
                            </div>
                            <div class="input-field">
                                <label for="middle_name">Middle Name</label>
                                <input type="text" id="middle_name" name="middle_name" value="<?php echo $middle_name; ?>" required>
                            </div>
                            <div class="input-field">
                                <label>Date of Birth</label>
                                <input type="date" name="dob" value="<?php echo $dob; ?>" required>
                            </div>
                            <div class="input-field">
                                <label>Place of Birth</label>
                                <input type="text" name="pob" placeholder="Enter birth date" value="<?php echo $pob; ?>" required>
                            </div>
                            <div class="input-field">
                                <label>Gender</label>
                                <select name="gender" required>
                                <option value="Male" <?php if ($gender === 'Male') echo 'selected'; ?>>Male</option>
                                <option value="Female" <?php if ($gender === 'Female') echo 'selected'; ?>>Female</option>
                                </select>
                            </div>
                        </div>

                        <div class="input-field">
                            <label>Email</label>
                            <input type="email" name="email" value="<?php echo $email; ?>" required>
                        </div>
                        <div class="fields">
                            <div class="input-field">
                                <label>School ID Number</label>
                                <input type="text" name="id_number" value="<?php echo $id_number; ?>" required>
                            </div>
                            <div class="input-field">
                                <label>Mobile Number</label>
                                <input type="number" name="mobile_num" value="<?php echo $mobile_num; ?>" required>
                            </div>
                            <div class="input-field">
                                <label>Citizenship</label>
                                <input type="text" name="citizenship" value="<?php echo $citizenship; ?>" required>
                            </div>
                        </div>
                        <hr>
                        <div class="input-field">
                            <h4>Permanent Address</h4>
                            <div class="address-inputs">
                            <input type="text" name="barangay" value="<?php echo $barangay; ?>" required>
                            <input type="text" name="town_city" value="<?php echo $town_city; ?>" required>
                            <input type="text" name="province" value="<?php echo $province; ?>" required>
                            <input type="number" name="zip_code" value="<?php echo $zip_code; ?>" required>
                            </div>
                        </div>
                    </div>
                </div>

                
                    <h3 style="color:darkgreen">FAMILY BACKGROUND:</h3>
                    <div class="details family">
                        <div class="fields-info">
                            <div class="form">
                                <div class="input-field">
                                    <span class="title"> FATHER </span>
                                    <hr>
                                    <label>Name</label>
                                    <input type="text" name="father_name" value="<?php echo $father_name; ?>" required>
                                    <label>Address</label>
                                    <input type="text" name="father_address" placeholder="Enter address" value="<?php echo $father_address; ?>" required>
                                    <label>Occupation</label>
                                    <input type="text" name="father_work" value="<?php echo $father_work; ?>" required>
                                </div>
                            </div>

                            <div class="form">
                                <div class="input-field">
                                    <span class="title"> MOTHER </span>
                                    <hr>
                                    <label>Name</label>
                                    <input type="text" name="mother_name" value="<?php echo $mother_name; ?>" required>
                                    <label>Address</label>
                                    <input type="text" name="mother_address" placeholder="Enter address" value="<?php echo $mother_address; ?>" required>
                                    <label>Occupation</label>
                                    <input type="text" name="mother_work" placeholder="Enter Occupation" value="<?php echo $mother_work; ?>" required>
                                </div>
                            </div>
                        </div>
                    </div> 
                    <div class="btns_wrap">
                        <div class="common_btns form_3_btns">
                            <button type="submit" class="btn_done" name="submit">Update Details</button>
                        </div> 
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
    </script>
</body>
</html>
