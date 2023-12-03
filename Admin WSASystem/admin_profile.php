<?php
include '../include/connection.php';
session_name("AdminSession");
session_start();
$super_admin_id = $_SESSION['super_admin_id'];

if (!isset($super_admin_id)) {
    header('location: admin_login.php');
    exit();
}

if (isset($_GET['logout'])) {
    unset($super_admin_id);
    session_destroy();
    header('location: admin_login.php');
    exit();
}

$profile_path = '';

$sql = "SELECT * FROM tbl_super_admin WHERE super_admin_id = ?";
$stmt = $dbConn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $super_admin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        $profile_path = $row['profile'];
    }

    $stmt->close();
}

function compressImage($source, $destination, $quality) {
    if (empty($source)) {
        return false;
    }

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $profile = $_FILES['profile'];

    if (!empty($profile['name'])) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($profile['name'], PATHINFO_EXTENSION));
    
        if (!in_array($file_extension, $allowed_extensions)) {
            $errors[] = 'Invalid file type. Allowed types: jpg, jpeg, png, gif';
        }
    
        $file_name = uniqid('profile_') . '.' . $file_extension;
        $upload_directory = $_SERVER['DOCUMENT_ROOT'] . '/user_profiles/' . $file_name;
    
        $compressedPath = compressImage($profile['tmp_name'], $upload_directory, 10);
    
        if ($compressedPath) {
            $profile_path = $file_name;
        } else {
            $errors[] = 'Image compression failed.';
        }
    }


    if (empty($errors)) {

        $sql = "UPDATE tbl_super_admin SET profile = ? WHERE super_admin_id = ?";
        $stmt = $dbConn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("si", $profile_path, $super_admin_id);

            if ($stmt->execute()) {
                $success_message = "Profile updated successfully.";
            } else {
                echo "Profile update failed.";
            }

            $stmt->close();
        } else {
            echo "Statement preparation failed.";
        }
    }
}

$dbConn->close();
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    <link rel="stylesheet" href="css/admin_profile.css">
    <title>Your Profile</title>
</head>

<body>
    <form method="POST" action="" enctype="multipart/form-data">
    <?php
    if (isset($success_message)) {
                echo '<script>
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "' . $success_message . '",
                    showConfirmButton: false,
                    timer: 2500
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.timer) {
                        window.location.href = "admin_dashboard.php";
                    }
                });
                </script>';
            }
            ?>
        <section>
            <h2 style="font-size: 25px; color: #636363; text-align:center;">Update Profile</h2>
            <div class="profile-container">
                <div class="container">
                    <div class="info-container">
                        <div id="success-message" style="color: green; text-align: center;"></div>
                    </div>
                    <div class="image-container">
                        <div id="updated-profile-image">
                            <?php
                            if (!empty($profile_path)) {
                                echo "<img src='../user_profiles/{$profile_path}' width='250' height='250'>";
                            }
                            ?>
                        </div>

                        <div class="round">
                            <input type="file" id="profile" name="profile" accept=".jpg, .jpeg, .png">
                            <i class='bx bxs-camera'></i>
                        </div>
                    </div>
                </div>
                <div class="update-container">
                    <button class="cancel-button" type="button" onclick="window.location.href='admin_dashboard.php'">Cancel</button>
                    <button class="update-button" type="submit" value="Update Profile">Update</button>
                </div>
            </div>
        </section>
    </form>

    <script>
        $(document).ready(function() {
            $('#profile').on('change', function() {
                var formData = new FormData($('form')[0]);

                $.ajax({
                    type: 'POST',
                    url: 'pdate_profile.php',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.includes('Profile Updated Successfully')) {
                            $('#success-message').text(response);
                        }
                        $('#updated-profile-image').html(response);
                    }
                });

                $('form').submit();
            });
        });
    </script>

</body>

</html>