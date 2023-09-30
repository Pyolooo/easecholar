<?php
require('../fpdf.php');
include '../include/connection.php';

ob_start();
class PDFWithHeader extends FPDF {
 
function Header() {
  $logoWidth = 5;
  $pageWidth = $this->w; 

 
  $x = ($pageWidth - $logoWidth) / 2;

  $this->Image('../img/isulogo.png', $x, 10, 30);


  $this->SetFont('Arial', 'B', 12);
  $this->Cell(0, 10, 'Isabela State University Santiago', 0, 1, 'C');
  
  $this->Ln(20);
}
}

if (isset($_GET['scholarship_name'])) {
    $scholarshipName = $_GET['scholarship_name'];
} else {
    die('Scholarship name not provided.');
}

$query = "SELECT application_id, scholarship_name, applicant_name, id_number, mobile_num FROM tbl_userapp WHERE status = 'Accepted'";
$result = mysqli_query($dbConn, $query);

if (!$result) {
    die('Query failed: ' . mysqli_error($dbConn));
}

$prevScholarshipName = '';
$pdf = null;
$number = 1;


while ($row = mysqli_fetch_assoc($result)) {
    $currentScholarshipName = $row['scholarship_name'];
    $applicantName = $row['applicant_name'];
    $idNumber = $row['id_number'];
    $mobileNumber = $row['mobile_num'];

    if ($currentScholarshipName != $prevScholarshipName) {
        if ($pdf) {
            $pdf->Output();
        }
        $pdf = new PDFWithHeader();
        $pdf->AddPage();

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(190, 10, $currentScholarshipName, 0, 1, 'C');

      
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(10, 10, 'No.', 1);
        $pdf->Cell(40, 10, 'Applicant Name', 1);
        $pdf->Cell(40, 10, 'ID Number', 1);
        $pdf->Cell(40, 10, 'Mobile Number', 1);
        $pdf->Ln(); 

        $prevScholarshipName = $currentScholarshipName;
    }

 
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(10, 10, $number++, 1); 
    $pdf->Cell(40, 10, $applicantName, 1);
    $pdf->Cell(40, 10, $idNumber, 1);
    $pdf->Cell(40, 10, $mobileNumber, 1);
    $pdf->Ln(); 
}

if ($pdf) {
    $pdf->Output();
}

$pdfContent = ob_get_clean();

header('Content-Type: application/pdf');

echo $pdfContent;
?>
