<?php
// Include the fpdf library
require('C:\wamp64\www\EASE-CHOLAR\fpdf.php');

// Database connection and query
include 'connection.php';

// Create an output buffer to capture PDF content
ob_start();

// Create a custom class by extending FPDF
class PDFWithHeader extends FPDF {
    // Header function
function Header() {
  // Logo
  $logoWidth = 5; // Width of your logo image
  $pageWidth = $this->w; // Get the page width

  // Calculate the horizontal position to center the image
  $x = ($pageWidth - $logoWidth) / 2;

  $this->Image($_SERVER['DOCUMENT_ROOT'] . '/EASE-CHOLAR/isulogo.png', $x, 10, 30);

  // Header text
  $this->SetFont('Arial', 'B', 12);
  $this->Cell(0, 10, 'Isabela State University Santiago', 0, 1, 'C');
  
  // Line break to move content below the header
  $this->Ln(20);
}
}

// Retrieve the scholarship name from the URL parameter
if (isset($_GET['scholarship_name'])) {
    $scholarshipName = $_GET['scholarship_name'];
} else {
    die('Scholarship name not provided.');
}

// Query data for the scholarship
$query = "SELECT application_id, scholarship_name, applicant_name, id_number, mobile_num FROM tbl_userapp WHERE status = 'Accepted'";
$result = mysqli_query($dbConn, $query);

if (!$result) {
    die('Query failed: ' . mysqli_error($dbConn));
}

$prevScholarshipName = '';
$pdf = null;
$number = 1;

// Loop through the data and generate a PDF for each applicant
while ($row = mysqli_fetch_assoc($result)) {
    $currentScholarshipName = $row['scholarship_name'];
    $applicantName = $row['applicant_name'];
    $idNumber = $row['id_number'];
    $mobileNumber = $row['mobile_num'];

    // Check if the scholarship name has changed
    if ($currentScholarshipName != $prevScholarshipName) {
        // Create a new PDF instance based on your custom class
        if ($pdf) {
            // Close the previous PDF if it exists
            $pdf->Output();
        }
        $pdf = new PDFWithHeader();
        $pdf->AddPage();

        // Set title to the current scholarship name
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(190, 10, $currentScholarshipName, 0, 1, 'C');

        // Table header
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(10, 10, 'No.', 1); // Add the "No." column
        $pdf->Cell(40, 10, 'Applicant Name', 1);
        $pdf->Cell(40, 10, 'ID Number', 1);
        $pdf->Cell(40, 10, 'Mobile Number', 1);
        $pdf->Ln(); // Move to the next line

        // Update the previous scholarship name
        $prevScholarshipName = $currentScholarshipName;
    }

    // Add applicant data to the PDF
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(10, 10, $number++, 1); // Increment the numbering count
    $pdf->Cell(40, 10, $applicantName, 1);
    $pdf->Cell(40, 10, $idNumber, 1);
    $pdf->Cell(40, 10, $mobileNumber, 1);
    $pdf->Ln(); // Move to the next line
}

// Close the last PDF
if ($pdf) {
    $pdf->Output();
}

// Capture the PDF content
$pdfContent = ob_get_clean();

// Set the content type header to display the PDF in the browser
header('Content-Type: application/pdf');

// Output the PDF content to the browser
echo $pdfContent;
?>
