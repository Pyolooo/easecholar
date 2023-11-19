<?php
session_name("ApplicantSession");
session_start();
include('../include/connection.php');

$user_id = $_SESSION['user_id'];
$application_id = $_GET['id'];

$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $application_id = $_POST['application_id'];

    if (isset($_FILES['new_attachments']) && !empty($_FILES['new_attachments']['name'][0])) {
        $newAttachmentNames = [];

        foreach ($_FILES['new_attachments']['name'] as $index => $attachmentName) {
            if ($_FILES['new_attachments']['error'][$index] === 0) {
                $newAttachmentTmpName = $_FILES['new_attachments']['tmp_name'][$index];
                $newAttachmentPath = '../file_uploads/' . $attachmentName;

                if (move_uploaded_file($newAttachmentTmpName, $newAttachmentPath)) {
                    $newAttachmentNames[] = $attachmentName;
                }
            }
        }

        if (!empty($newAttachmentNames)) {
            $attachmentsString = implode(',', $newAttachmentNames);
            $sql = "UPDATE tbl_scholarship_1_form SET attachments = ? WHERE user_id = ? AND application_id = ?";
            $stmt = $dbConn->prepare($sql);
            $stmt->bind_param("sii", $attachmentsString, $user_id, $application_id);

            if ($stmt->execute()) {
                $successMessage = 'Attachments updated successfully';

                $sqlFetchImage = "SELECT image FROM tbl_user WHERE user_id = ?";
                $stmtFetchImage = $dbConn->prepare($sqlFetchImage);
                $stmtFetchImage->bind_param("i", $user_id);
                $stmtFetchImage->execute();
                $resultImage = $stmtFetchImage->get_result();

                if ($resultImage->num_rows > 0) {
                    $rowImage = $resultImage->fetch_assoc();
                    $userImage = $rowImage['image'];

                    $message = 'A new file has been uploaded by an applicant.';
                    $is_read = 'unread';
                    $source = 'tbl_scholarship_1_form';

                    $sql = "INSERT INTO tbl_notifications (user_id, application_id, image, message, is_read, source) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $dbConn->prepare($sql);
                    $stmt->bind_param("iissss", $user_id, $application_id, $userImage, $message, $is_read, $source);

                    if ($stmt->execute()) {
                        $successMessage = 'Details updated successfully';
                    } else {
                        echo "Error sending notification: " . $stmt->error;
                    }
                } else {
                    echo "User's image not found.";
                }
            } else {
                echo "Error updating attachments: " . $stmt->error;
            }
        } else {
            echo "Attachment upload failed";
        }
    } else {
    }
}

$sql = "SELECT * FROM tbl_scholarship_1_form WHERE user_id = ? AND application_id = ?";
$stmt = $dbConn->prepare($sql);
$stmt->bind_param("ii", $user_id, $application_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $last_name = $row['last_name'];
    $first_name = $row['first_name'];
    $middle_name = $row['middle_name'];
    $dob = $row['dob'];
    $pob = $row['pob'];
    $age = $row['age'];
    $gender = $row['gender'];
    $email = $row['email'];
    $course = $row['course'];
    $year_lvl = $row['year_lvl'];
    $mobile_num = $row['mobile_num'];
    $religion = $row['religion'];
    $citizenship = $row['citizenship'];
    $civil_status = $row['civil_status'];
    $barangay = $row['barangay'];
    $town_city = $row['town_city'];
    $province = $row['province'];
    $zip_code = $row['zip_code'];
    $id_number = $row['id_number'];
    $father_lname = $row['father_lname'];
    $father_fname = $row['father_fname'];
    $father_mname = $row['father_mname'];
    $father_work = $row['father_work'];
    $mother_sname = $row['mother_sname'];
    $mother_fname = $row['mother_fname'];
    $mother_mname = $row['mother_mname'];
    $mother_work = $row['mother_work'];
    $primary_school = $row['primary_school'];
    $prim_year_grad = $row['prim_year_grad'];
    $secondary_school = $row['secondary_school'];
    $sec_year_grad = $row['sec_year_grad'];
    $tertiary_school = $row['tertiary_school'];
    $ter_year_grad = $row['ter_year_grad'];
} else {
    die('User details not found');
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/update_status.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    <title>Application Form</title>
</head>

<body>
    <?php include('../include/header.php') ?>
    <div class="wrapper">

        <?php
        if (!empty($successMessage)) {
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
        ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="container">
                <div class="form first">
                    <h4 class="form-label">PERSONAL INFORMATION:</h4>
                    <br>
                    <div class="details personal">
                        <input type="hidden" name="application_id" value="<?php echo $application_id; ?>">

                        <div class="fields">
                            <div class="input-field">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo $last_name; ?>" disabled>
                            </div>
                            <div class="input-field">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo $first_name; ?>" disabled>
                            </div>
                            <div class="input-field">
                                <label for="middle_name">Middle Name</label>
                                <input type="text" id="middle_name" name="middle_name" value="<?php echo $middle_name; ?>" disabled>
                            </div>
                            <div class="input-field">
                                <label>Date of Birth</label>
                                <input type="date" name="dob" value="<?php echo $dob; ?>" disabled>
                            </div>
                            <div class="input-field">
                                <label>Place of Birth</label>
                                <input type="text" name="pob" placeholder="Enter birth date" value="<?php echo $pob; ?>" disabled>
                            </div>

                            <div class="input-field">
                                <label for="zip_code">Age</label>
                                <input type="number" id="age" name="age" placeholder="Age" value="<?php echo $age; ?>" disabled>
                                <div class="validation-message" id="age-error"></div>
                            </div>

                            <div class="input-field">
                                <label>Citizenship</label>
                                <input type="text" name="citizenship" value="<?php echo $citizenship; ?>" disabled>
                            </div>

                            <div class="input-field">
                                <label>Civil Status</label>
                                <select id="civil_status" name="civil_status" disabled>
                                    <option><?php echo $civil_status; ?></option>
                                </select>
                                <div class="validation-message" id="civil_status-error"></div>
                            </div>
                            <div class="input-field">
                                <label>Sex</label>
                                <select name="gender" disabled>
                                    <option value="Male" <?php if ($gender === 'Male') echo 'selected'; ?>>Male</option>
                                    <option value="Female" <?php if ($gender === 'Female') echo 'selected'; ?>>Female</option>
                                </select>
                            </div>

                            <div class="input-field">
                                <label>Email</label>
                                <input type="email" name="email" value="<?php echo $email; ?>" disabled>
                            </div>
                            <div class="input-field">
                                <label>Mobile Number</label>
                                <input type="number" name="mobile_num" value="<?php echo $mobile_num; ?>" disabled>
                            </div>

                            <div class="input-field">
                                <label>Religion</label>
                                <input type="text" id="religion" name="religion" placeholder="Enter your religion" value="<?php echo $religion; ?>" disabled>
                                <div class="validation-message" id="religion-error"></div>
                            </div>

                            <div class="input-field">
                                <label>School ID Number</label>
                                <input type="text" name="id_number" value="<?php echo $id_number; ?>" disabled>
                            </div>

                            <div class="input-field">
                                <label>Course</label>
                                <select name="course" disabled>
                                    <option><?php echo $course; ?></option>
                                </select>
                            </div>

                            <div class="input-field">
                                <label>Year Level</label>
                                <select id="year_lvl" name="year_lvl" disabled>
                                    <option><?php echo $year_lvl; ?></option>
                                </select>
                                <div class="validation-message" id="year_lvl-error"></div>
                            </div>
                        </div>
                        <br>
                        <div class="input-field">
                            <h4 class="form-label">PERMANENT ADDRESS</h4>
                            <div class="address-inputs">
                                <input type="text" name="barangay" value="<?php echo $barangay; ?>" disabled>
                                <input type="text" name="town_city" value="<?php echo $town_city; ?>" disabled>
                                <input type="text" name="province" value="<?php echo $province; ?>" disabled>
                                <input type="number" name="zip_code" value="<?php echo $zip_code; ?>" disabled>
                            </div>
                        </div>
                    </div>
                </div>

                <br>
                <h4 class="form-label">FAMILY BACKGROUND:</h4>
                <div class="details family">
                    <div class="fields-info">
                        <div class="form">
                            <div class="input-field">
                                <span class="title"> FATHER </span>
                                <hr>
                                <label>Last Name</label>
                                <input type="text" id="father_lname" name="father_lname" placeholder="Enter your father's lastname" value="<?php echo $father_lname; ?>" disabled>
                                <div class="validation-message" id="father_lname-error"></div>

                                <label>First Name</label>
                                <input type="text" id="father_fname" name="father_fname" placeholder="Enter your father's firstname" value="<?php echo $father_fname; ?>" disabled>
                                <div class="validation-message" id="father_fname-error"></div>

                                <label>MIddle Name</label>
                                <input type="text" id="father_mname" name="father_mname" placeholder="Enter your father's middlename" value="<?php echo $father_mname; ?>" disabled>
                                <div class="validation-message" id="father_mname-error"></div>

                                <label>Occupation</label>
                                <input type="text" id="father_work" name="father_work" placeholder="Enter Occupation" value="<?php echo $father_work; ?>" disabled>
                                <div class="validation-message" id="father_work-error"></div>
                            </div>
                        </div>

                        <div class="form">
                            <div class="input-field">
                                <span class="title"> MOTHER </span>
                                <hr>
                                <label>Surname</label>
                                <input type="text" id="mother_sname" name="mother_sname" placeholder="Enter mother's surname" value="<?php echo $mother_sname; ?>" disabled>
                                <div class="validation-message" id="mother_sname-error"></div>

                                <label>First Name</label>
                                <input type="text" id="mother_fname" name="mother_fname" placeholder="Enter mother's firstname" value="<?php echo $mother_fname; ?>" disabled>
                                <div class="validation-message" id="mother_fname-error"></div>

                                <label>Middle Name</label>
                                <input type="text" id="mother_mname" name="mother_mname" placeholder="Enter mother's middlename" value="<?php echo $mother_mname; ?>" disabled>
                                <div class="validation-message" id="mother_mname-error"></div>

                                <label>Occupation</label>
                                <input type="text" id="mother_work" name="mother_work" placeholder="Enter Occupation" value="<?php echo $mother_work; ?>" disabled>
                                <div class="validation-message" id="mother_work-error"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form first">
                    <h3 style="color: darkgreen;">EDUCATIONAL BACKGROUND:</h4>
                        <br>
                        <div class="select-input-field">
                            <div class="input-field">
                                <label>Primary School</label>
                                <input type="text" id="primary_school" name="primary_school" placeholder="Name of your Primary School" value="<?php echo $primary_school; ?>" disabled>
                                <div class="validation-message" id="primary_school-error"></div>
                            </div>
                            <div class="input-field">
                                <label>Year Graduated</label>
                                <input type="number" id="prim_year_grad" name="prim_year_grad" placeholder="Primary year graduated" value="<?php echo $prim_year_grad; ?>" disabled>
                                <div class="validation-message" id="prim_year_grad-error"></div>
                            </div>

                            <div class="input-field">
                                <label>Secondary School</label>
                                <input type="text" id="secondary_school" name="secondary_school" placeholder="Name of your Secondary School" value="<?php echo $secondary_school; ?>" disabled>
                                <div class="validation-message" id="secondary_school-error"></div>
                            </div>
                            <div class="input-field">
                                <label>Year Graduated</label>
                                <input type="number" id="sec_year_grad" name="sec_year_grad" placeholder="Primary year graduated" value="<?php echo $sec_year_grad; ?>" disabled>
                                <div class="validation-message" id="sec_year_grad-error"></div>
                            </div>


                            <div class="input-field">
                                <label>Tertiary School</label>
                                <input type="text" id="tertiary_school" name="tertiary_school" placeholder="Name of your Tertiary School" value="<?php echo $tertiary_school; ?>" disabled>
                                <div class="validation-message" id="tertiary_school-error"></div>
                            </div>
                            <div class="input-field">
                                <label>Year Graduated</label>
                                <input type="number" id="ter_year_grad" name="ter_year_grad" placeholder="Tertiary year graduated" value="<?php echo $ter_year_grad; ?>" disabled>
                                <div class="validation-message" id="ter_year_grad-error"></div>
                            </div>
                        </div>

                        <h4 class="form-label">REQUIREMENTS UPLOADED</h4>
                        <div class="attachments-container">
                            <div class="files-column">
                                <h4 class="files-label">Files Uploaded</h4>
                                <?php
                                if (!empty($row['file'])) {
                                    $fileNames = explode(',', $row['file']);
                                    foreach ($fileNames as $fileName) {
                                        $filePath = '../file_uploads/' . $fileName;
                                        // Check if the file exists on the server
                                        if (file_exists($filePath)) {
                                            // Display a link to the file
                                            echo '<p>File: <a href="' . $filePath . '" target="_blank">' . $fileName . '</a></p>';
                                        } else {
                                            // Handle the case where the file is missing
                                            echo '<p>File not found: ' . $fileName . '</p>';
                                        }
                                    }
                                } else {
                                    echo '<p>No files uploaded</p>';
                                }
                                ?>
                            </div>

                            <div class="attachments-column">
                                <h4 class="files-label">Lack of Documents</h4>
                                <?php
                                $attachmentsExist = false;

                                // Fetch attachment files from the tbl_user_messages table based on user_id
                                $sqlAttachmentMessages = "SELECT attach_files FROM tbl_user_messages WHERE user_id = ? AND application_id = ? AND source = 'tbl_scholarship_1_form'";
                                $stmtAttachmentMessages = $dbConn->prepare($sqlAttachmentMessages);
                                $stmtAttachmentMessages->bind_param("ii", $user_id, $application_id);
                                $stmtAttachmentMessages->execute();
                                $resultAttachmentMessages = $stmtAttachmentMessages->get_result();

                                if ($resultAttachmentMessages->num_rows > 0) {
                                    while ($rowAttachment = $resultAttachmentMessages->fetch_assoc()) {
                                        $attach_files = $rowAttachment['attach_files'];

                                        if (!empty($attach_files)) {
                                            // Display the attachments if they exist
                                            $attachmentNames = explode(',', $attach_files);
                                            foreach ($attachmentNames as $attachmentName) {
                                                $attachmentPath = '../file_uploads/' . $attachmentName;
                                                if (file_exists($attachmentPath)) {
                                                    echo '<p>Attachment: <a href="' . $attachmentPath . '" target="_blank">' . $attachmentName . '</a></p>';
                                                } else {
                                                    echo '<p>' . $attachmentName . '</p>';
                                                }
                                                $attachmentsExist = true;
                                            }
                                        }
                                    }
                                }

                                // Fetch attachment files from the tbl_userapp table
                                $attachments = $row['attachments'];

                                if (!empty($attachments)) {
                                    // Display the attachments from tbl_userapp if they exist
                                    $attachmentNames = explode(',', $attachments);
                                    foreach ($attachmentNames as $attachmentName) {
                                        $attachmentPath = '../file_uploads/' . $attachmentName;
                                        if (file_exists($attachmentPath)) {
                                            echo '<p><a href="' . $attachmentPath . '" target="_blank">' . $attachmentName . '</a></p>';
                                        } else {
                                            echo '<p>' . $attachmentName . '</p>';
                                        }
                                        $attachmentsExist = true;
                                    }
                                }

                                if (!$attachmentsExist) {
                                    echo '<p>No attachments uploaded</p>';
                                }
                                ?>
                            </div>



                        </div>

                        <h4 class="attach-label">Attach file or photo here</h4>
                        <input type="file" name="new_attachments[]" id="new_attachments" multiple>



                        <div class="btns_wrap">
                            <div class="common_btns form_3_btns">
                                <button class="cancel-button" type="button" onclick="window.location.href='application_status.php'">Back</button>
                                <button class="update-button" type="submit" class="btn_done" name="submit">Update Details</button>
                            </div>
                        </div>
                </div>
        </form>

    </div>

    <!-- Add this JavaScript code within your HTML file -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            let isFormDirty = false;

            // Function to mark the form as dirty when a form field is changed
            function handleFormChange() {
                isFormDirty = true;
            }

            // Add an event listener to each form field to detect changes
            const formFields = form.querySelectorAll('input, select');
            formFields.forEach(field => {
                field.addEventListener('input', handleFormChange);
            });

            // Add a submit event listener to the form to prevent submission if the form is not dirty
            form.addEventListener('submit', function(event) {
                if (!isFormDirty) {
                    // Form is not dirty; prevent submission
                    event.preventDefault();
                    Swal.fire({
                        icon: 'info',
                        title: 'No changes made',
                        text: 'Please update some data.',
                        showConfirmButton: false,
                        timer: 2000 // Close the alert after 2 seconds
                    });
                }
            });
        });
    </script>

</body>

</html>