<?php
include('connection.php');

$scholarship = "";
$details = "";
$requirements = array();
$benefits = array();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the form data
    $scholarship = $_POST["scholarship"];
    $details = $_POST["details"];
    $requirements = explode("\n", $_POST["requirements"]); // Split requirements by new line into an array
    $benefits = explode("\n", $_POST["benefits"]); // Split benefits by new line into an array

    if (!empty($scholarship) && !empty($details) && !empty($requirements) && !empty($benefits)) {
        $requirementsString = implode("\n", $requirements); // Convert requirements array back to a string
        $benefitsString = implode("\n", $benefits); // Convert benefits array back to a string

        $sql = "INSERT INTO `tbl_scholarship` (scholarship, details, requirements, benefits) VALUES ('$scholarship', '$details', '$requirementsString', '$benefitsString')";
        $result = $dbConn->query($sql);

        // Clear form inputs
        $scholarship = "";
        $details = "";
        $requirements = array();
        $benefits = array();

        header("location: scholarships.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Scholarship</title>
    <link rel="stylesheet" href="create_scholarship.css">
  </head>
  <body>
    <section class="container">
      <header>Add Scholarship</header>
      <form method="POST" action="#" class="form">
        <div class="input-box">
          <label>Scholarship</label>
          <input type="text" name="scholarship" placeholder="" value="<?php echo $scholarship; ?>" required>
        </div>

        <div class="input-box">
          <label>Details</label>
          <input type="text" name="details" placeholder="" value="<?php echo $details; ?>" required>
        </div>
        <div class="input-box">
  <label>Requirements</label>
  <textarea name="requirements" placeholder="Requirements" required><?php echo implode("\n", $requirements); ?></textarea>
</div>
<div class="input-box">
  <label>Benefits</label>
  <textarea name="benefits" placeholder="Benefits" required><?php echo implode("\n", $benefits); ?></textarea>
</div>

        <button type="submit">Submit</button>
      </form>
    </section>
  </body>
</html>
