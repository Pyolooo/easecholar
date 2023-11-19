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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profile = $_FILES['profile'];
    $response = ''; // Initialize the response variable

    if (!empty($profile['name'])) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($profile['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_extensions)) {
            $response = 'Invalid file type. Allowed types: jpg, jpeg, png, gif';
        } else {
            $file_name = uniqid('profile_') . '.' . $file_extension;
            $upload_directory = $_SERVER['DOCUMENT_ROOT'] . '/user_profiles/' . $file_name;

            if (move_uploaded_file($profile['tmp_name'], $upload_directory)) {
                $profile_path = $file_name;
          
                // Move the profile update query into this block
                $updateSql = "UPDATE tbl_super_admin SET profile = ? WHERE super_admin_id = ?";
                $updateStmt = $dbConn->prepare($updateSql);
          
                if ($updateStmt) {
                    $updateStmt->bind_param("si", $profile_path, $super_admin_id);
                    if ($updateStmt->execute()) {
                        $response = 'Profile Updated Successfully';
                    } else {
                        $errors[] = 'Profile update query failed: ' . $updateStmt->error;
                    }
                    $updateStmt->close();
                } else {
                    $errors[] = 'Profile update statement preparation failed: ' . $dbConn->error;
                }
            } else {
                $errors[] = 'File upload failed.';
            }
        }
    } else {
        $response = 'No file selected.';
    }

    // You can return the response message and the updated profile image HTML
    echo $response;

    // Optionally, you can update the profile path in your database if needed
    // ...

    // Close any open database connections
    // ...
}
?>
