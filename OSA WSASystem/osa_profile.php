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

        $username = $row['username'];
        $full_name = $row['full_name'];
        $email = $row['email'];
        $phone_num = $row['phone_num'];
        $profile_path = $row['profile'];
    }

    $stmt->close();
}

$conn->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone_num = $_POST['phone_num'];

    $errors = array();

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email address is required.';
    }

    if (!empty($phone_num) && !preg_match('/^\d{11}$/', $phone_num)) {
        $errors[] = 'Phone number must have exactly 11 digits.';
    }

    $profile = $_FILES['profile'];

    if (!empty($profile['name'])) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($profile['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_extensions)) {
            $errors[] = 'Invalid file type. Allowed types: jpg, jpeg, png, gif';
        }

        $file_name = uniqid('profile_') . '.' . $file_extension;
        $upload_directory = $_SERVER['DOCUMENT_ROOT'] . '/user_profiles/' . $file_name;

        if (move_uploaded_file($profile['tmp_name'], $upload_directory)) {
            // Only update $profile_path if the move operation was successful
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

        $sql = "UPDATE tbl_admin SET email = ?, phone_num = ?, profile = ?, username = ?, full_name = ? WHERE admin_id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            // Bind parameters
            $stmt->bind_param("sssssi", $email, $phone_num, $profile_path, $username, $full_name, $admin_id);

            if ($stmt->execute()) {
                $success_message = "Profile updated successfully.";
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
        }
    }
}
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


    <link rel="stylesheet" href="css/osa_profile.css">
    <title>Your Profile</title>
</head>

<body>

    <form method="POST" action="" enctype="multipart/form-data">
        <section>
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
                        window.location.href = "osa_dashboard.php";
                    }
                });
                </script>';
            }
            ?>
            <h2 style="font-size:25px; color: #636363">PROFILE</h2>
            <div class="profile-container">
                <div class="container">
                    <div class="info-container">

                        <div class="label-container">
                            <i class='bx bxs-user-rectangle' ></i>
                            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required>
                        </div>

                        <div class="label-container">
                        <i class='bx bxs-user-detail' ></i>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                        </div>
                        

                        <div class="label-container">
                             <i class='bx bxs-envelope'></i>
                            <input type="email" id="email" name="email" placeholder="Provide your email address" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>

                        <div class="label-container">
                        <i class='bx bxs-phone' ></i>
                            <input type="text" id="phone_num" name="phone_num" placeholder="Provide your phone number" value="<?php echo htmlspecialchars($phone_num); ?>" required>
                        </div>

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
                    <button class="cancel-button" type="button" onclick="window.location.href='osa_dashboard.php'">Cancel</button>
                    <button class="update-button" type="submit" value="Update Profile">Update </button>
                </div>
                <?php
if (isset($errors)) {
    foreach ($errors as $error) {
        echo '<p style="color: red; text-align: center;">' . $error . '</p>';
    }
}
?>
                <?php
                if (isset($success_message)) {
                    echo '<p style="color: green; text-align:center">' . $success_message . '</p>';
                }
                ?>
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
                        // Check if the response contains the success message
                        if (response.includes('Profile Updated Successfully')) {
                            // Display the success message
                            $('#success-message').text(response);
                        }

                        // Update the profile image
                        $('#updated-profile-image').html(response);
                    }
                });

                $('form').submit();
            });
        });
        
    </script>
</body>

</html>