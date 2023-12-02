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
            $sql = "UPDATE tbl_userapp SET attachments = ? WHERE user_id = ? AND application_id = ?";
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
                    $source = 'tbl_userapp';

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

$sql = "SELECT * FROM tbl_userapp WHERE user_id = ? AND application_id = ?";
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
    $gender = $row['gender'];
    $email = $row['email'];
    $course = $row['course'];
    $year_lvl = $row['year_lvl'];
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
    $gross_income = $row['gross_income'];
    $num_siblings = $row['num_siblings'];
} else {
    die('User details not found');
}

    $sqlAdditionalInfo = "SELECT status, reasons, other_reason FROM tbl_userapp WHERE user_id = ? AND application_id = ?";
    $stmtAdditionalInfo = $dbConn->prepare($sqlAdditionalInfo);
    $stmtAdditionalInfo->bind_param("ii", $user_id, $application_id);
    $stmtAdditionalInfo->execute();
    $resultAdditionalInfo = $stmtAdditionalInfo->get_result();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/update_status.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
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
            <?php
if ($resultAdditionalInfo->num_rows > 0) {
    $rowAdditionalInfo = $resultAdditionalInfo->fetch_assoc();

    if ($rowAdditionalInfo['status'] == "Rejected") {
        echo '<div class="rejected-info">';
        echo '<img class="rejected-icon" src="../img/rejected-icon.png" alt="Rejected Image">';
        // echo '<h4>Status: ' . $rowAdditionalInfo['status'] . '</h4>';
        echo '<div class="rejected-reasons">';
        $reasonsArray = json_decode($rowAdditionalInfo['reasons'], true);
        echo '<p><span class="reason-label">Reasons: </span><span class="rejected-reason">' . implode(", ", $reasonsArray) . '</span></p>';
        
        if (!empty($rowAdditionalInfo['other_reason'])) {
            echo '<p><span class="reason-label">Other Reasons: </span><span class="rejected-reason">' . $rowAdditionalInfo['other_reason'] . '</span></p>';
        }
        
        echo '</div>';
        echo '</div>';
    }
}
?>

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
                                <label>Citizenship</label>
                                <input type="text" name="citizenship" value="<?php echo $citizenship; ?>" disabled>
                            </div>

                        </div>

                        <div class="fields">
                            <div class="input-field">
                                <label>School ID Number</label>
                                <input type="text" name="id_number" value="<?php echo $id_number; ?>" disabled>
                            </div>

                            <div class="input-field">
                                <label>Course</label>
                                <select name="course" disabled>
                                    <option value="BSIT" <?php if ($course === 'BSIT') echo 'selected'; ?>>BSIT</option>
                                    <option value="BSA" <?php if ($course === 'BSA') echo 'selected'; ?>>BSA</option>
                                </select>
                            </div>
                            <div class="input-field">
                                <label>Year level</label>
                                <input type="text" name="year_lvl" value="<?php echo $year_lvl; ?>" disabled>
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
                                <label>Name</label>
                                <input type="text" name="father_name" value="<?php echo $father_name; ?>" disabled>
                                <label>Address</label>
                                <input type="text" name="father_address" placeholder="Enter address" value="<?php echo $father_address; ?>" disabled>
                                <label>Occupation</label>
                                <input type="text" name="father_work" value="<?php echo $father_work; ?>" disabled>
                            </div>
                        </div>

                        <div class="form">
                            <div class="input-field">
                                <span class="title"> MOTHER </span>
                                <hr>
                                <label>Name</label>
                                <input type="text" name="mother_name" value="<?php echo $mother_name; ?>" disabled>
                                <label>Address</label>
                                <input type="text" name="mother_address" placeholder="Enter address" value="<?php echo $mother_address; ?>" disabled>
                                <label>Occupation</label>
                                <input type="text" name="mother_work" placeholder="Enter Occupation" value="<?php echo $mother_work; ?>" disabled>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>
                <div class="select-input-field">
                    <div class="input-field">
                        <label>Total Gross Income</label>
                        <input type="number" id="email" name="gross_income" value="<?php echo $gross_income; ?>" disabled>
                    </div>
                    <div class="input-field">
                        <label>No. of Siblings in the family</label>
                        <input type="number" id="mobile_num" name="num_siblings" value="<?php echo $num_siblings; ?>" disabled>
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
                        <div class="attachments-container">
                            <div class="attachments-label">
                                <?php
                                $attachmentsExist = false;

                                $sqlAttachmentMessages = "SELECT attach_files FROM tbl_user_messages WHERE user_id = ? AND application_id = ? AND source = 'tbl_userapp'";
                                $stmtAttachmentMessages = $dbConn->prepare($sqlAttachmentMessages);
                                $stmtAttachmentMessages->bind_param("ii", $user_id, $application_id);
                                $stmtAttachmentMessages->execute();
                                $resultAttachmentMessages = $stmtAttachmentMessages->get_result();

                                if ($resultAttachmentMessages->num_rows > 0) {
                                    while ($rowAttachment = $resultAttachmentMessages->fetch_assoc()) {
                                        $attach_files = $rowAttachment['attach_files'];

                                        if (!empty($attach_files)) {
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
                                echo '</div>';

                                echo '<div class="attachments-uploaded">';
                                $attachments = $row['attachments'];

                                if (!empty($attachments)) {
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
                            </div>
                        </div>

                        <h4 class="attach-label">Attach file or photo here</h4>
                        <input type="file" name="new_attachments[]" id="new_attachments" multiple>
                        <button class="upload-button" type="submit" name="submit"><i class='bx bx-upload'></i>Upload</button>
                        <div id="selected-files"></div>



                        <div class="btns_wrap">
                            <div class="common_btns form_3_btns">
                                <button class="cancel-button" type="button" onclick="window.location.href='application_status.php'">Back</button>
                            </div>
                        </div>
                    </div>
        </form>

    </div>

    <script>
     document.addEventListener('DOMContentLoaded', function () {
        const fileInput = document.getElementById('new_attachments');
        const selectedFilesContainer = document.getElementById('selected-files');

        fileInput.addEventListener('change', function (event) {
            selectedFilesContainer.innerHTML = '';

            const files = event.target.files;
            for (const file of files) {
                const fileName = file.name;
                const fileItem = createFileItem(fileName);
                selectedFilesContainer.appendChild(fileItem);
            }
        });

        selectedFilesContainer.addEventListener('click', function (event) {
    if (event.target.classList.contains('remove-selected-file')) {
        const fileName = event.target.getAttribute('data-filename');
        removeFile(fileName);
    }
});


        function createFileItem(fileName) {
            const fileItem = document.createElement('div');
            fileItem.innerHTML = `<span>${fileName}</span><button type="button" class="remove-selected-file" data-filename="${fileName}">X</button>`;
            return fileItem;
        }

        function removeFile(fileName) {
    const fileInput = document.getElementById('new_attachments');
    const files = fileInput.files;

    // Remove the file from the displayed list
    const fileItem = selectedFilesContainer.querySelector(`[data-filename="${fileName}"]`);
    if (fileItem) {
        fileItem.parentNode.removeChild(fileItem);
    }

    // Remove the file from the input
    const updatedFiles = Array.from(files).filter(file => file.name !== fileName);
    fileInput.value = '';
    updatedFiles.forEach(file => {
        fileInput.files.add(file);
    });

    if (updatedFiles.length === 0) {
        selectedFilesContainer.innerHTML = '';
    }
}


        const form = document.querySelector('form');
        let isFormDirty = false;

        function handleFormChange() {
            isFormDirty = true;
        }

        const formFields = form.querySelectorAll('input, select');
        formFields.forEach(field => {
            field.addEventListener('input', handleFormChange);
        });

        form.addEventListener('submit', function (event) {
            if (!isFormDirty) {
                event.preventDefault();
                Swal.fire({
                    icon: 'info',
                    title: 'No file selected',
                    text: 'Please attach a file or photo.',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        });
    });
    </script>

</body>

</html>