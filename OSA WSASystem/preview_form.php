<?php
include('../include/connection.php');

// Check if the form parameter is set in the URL
if (isset($_GET['form'])) {
    $selectedFormTable = $_GET['form'];

    // Retrieve column names from the selected form table
    $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ?";
    $stmt = $dbConn->prepare($sql);
    $stmt->bind_param("s", $selectedFormTable);
    $stmt->execute();

    $result = $stmt->get_result();
    $fieldNames = array();

    while ($row = $result->fetch_assoc()) {
        $fieldNames[] = $row['COLUMN_NAME'];
    }
} else {
    // Handle the case where the form parameter is not set or is invalid
    // You can display an error message or redirect the user to a different page
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Application Form</title>
    <link rel="stylesheet" href="css/preview_application_form.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
        }

        .form-preview {
            width: 80%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }

        .form-field {
            margin: 10px 0;
        }

        label {
            display: block;
            font-weight: bold;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <h1>Preview Application Form</h1>

    <div class="form-preview">
        <?php
        foreach ($fieldNames as $fieldName) {
            // Display the form fields based on column names
            echo '<div class="form-field">';
            echo "<label>$fieldName:</label> <input type='text' name='$fieldName' />";
            echo '</div>';
        }
        ?>
    </div>
</body>

</html>

