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

$profile_path = '';
$email = '';
$phone_num = '';

// Check if the user profile data exists in the database
$dbHost = "localhost"; // Replace with your database host
$dbUser = "root"; // Replace with your database username
$dbPass = ""; // Replace with your database password
$dbName = "easecholar"; // Replace with your database name

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM tbl_admin WHERE admin_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        // Assign values to variables
        $email = $row['email'];
        $phone_num = $row['phone_num'];
        $profile_path = $row['profile'];
    }

    $stmt->close();
}

$conn->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $phone_num = $_POST['phone_num'];

    $errors = array();

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email address is required.';
    }

    $profile = $_FILES['profile'];

    if (!empty($profile['name'])) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($profile['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_extensions)) {
            $errors[] = 'Invalid file type. Allowed types: jpg, jpeg, png, gif';
        }

        $file_name = uniqid('profile_') . '.' . $file_extension;
        $upload_directory = $_SERVER['DOCUMENT_ROOT'] . '/EASE-CHOLAR/user_profiles/' . $file_name;

        if (move_uploaded_file($profile['tmp_name'], $upload_directory)) {
            // Store only the file name in the database
            $profile_path = $file_name;
        } else {
            $errors[] = 'File upload failed.';
        }
    }

    if (empty($errors)) {
        $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "UPDATE tbl_admin SET email = ?, phone_num = ?, profile = ? WHERE admin_id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            // Bind parameters
            $stmt->bind_param("sssi", $email, $phone_num, $profile_path, $admin_id);

            if ($stmt->execute()) {
                echo "Profile updated successfully.";
            } else {
                echo "Profile update failed.";
            }

            $stmt->close();
        } else {
            echo "Statement preparation failed.";
        }

        $conn->close();
    } else {
        foreach ($errors as $error) {
            echo "<p>{$error}</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Your Profile</title>
    <link rel="stylesheet" href="css/osa_profile.css">
</head>

<body>
    <h1>Your Profile</h1>
    <form method="POST" action="" enctype="multipart/form-data">

    <section>
        <div class="profile-container">
            <div class="container">
                <div class="info-container">
                    <div class="email-container">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>

                    <div class="phone-container">
                        <label for="phone_num">Phone Number:</label>
                        <input type="text" id="phone_num" name="phone_num" value="<?php echo htmlspecialchars($phone_num); ?>">
                    </div>
                </div>
                <div class="image-container">
                    <?php

                    if (!empty($profile_path)) {
                        echo "<img src='/EASE-CHOLAR/user_profiles/{$profile_path}' width='150' height='150'>";
                    }
                    ?>
                    <label for="profile">Profile Picture:</label>
                    <input type="file" id="profile" name="profile">
                </div>
            </div>
                    <div class="update-container">
            <button class="update-button" tupe="submit" value="Update Profile">
            </div>
        </div>
    </section>
    </form>
</body>

</html>