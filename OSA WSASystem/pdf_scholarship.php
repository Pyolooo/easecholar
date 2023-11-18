<?php
ob_start();

require('../fpdf.php');
include '../include/connection.php';

class PDFWithHeader extends FPDF {
    function Header() {
        $logoWidth = 5;
        $pageWidth = $this->w;
        $x = ($pageWidth - $logoWidth) / 3.4;
        $this->Image('../img/isulogo.png', $x, 11, 15);
        $this->SetFont('Arial', '', 12);
        $this->MultiCell(0, 10, 'Isabela State University' . "\n" . 'Santiago City', 0, 'C');
        $this->Ln(20);
    }
}

$pdf = new PDFWithHeader();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);

$query = "SELECT s.scholarship, 
                 (COUNT(ua.user_id) + COUNT(sf.user_id)) AS num_applicants, 
                 s.benefits 
          FROM tbl_scholarship s 
          LEFT JOIN tbl_userapp ua ON s.scholarship_id = ua.scholarship_id 
          LEFT JOIN tbl_scholarship_1_form sf ON s.scholarship_id = sf.scholarship_id 
          GROUP BY s.scholarship";

$result = mysqli_query($dbConn, $query);

$number = 1;

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(6, 10,  'No.', 1, 0, 'C');
$pdf->Cell(60, 10, 'SCHOLARSHIP GRANTS', 1, 0, 'C');
$pdf->Cell(50, 10, 'NUMBER OF SCHOLARS', 1, 0, 'C');
$pdf->Cell(78, 10, 'PRIVILEGES', 1, 0, 'C'); // Adjust the width for privileges
$pdf->Ln();


while ($row = mysqli_fetch_assoc($result)) {
    $scholarshipName = $row['scholarship'];
    $numScholars = $row['num_applicants'];
    $privileges = $row['benefits'];

    // Save the current Y position for "SCHOLARSHIP GRANTS" column
    $yPosScholarship = $pdf->GetY();

    // Use MultiCell for the "SCHOLARSHIP GRANTS" column
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(66, 10, $scholarshipName, 1, 'C');

    // Calculate the height of the MultiCell for "SCHOLARSHIP GRANTS" column
    $heightScholarship = $pdf->GetY() - $yPosScholarship;

    // Set Y position for the next row
    $pdf->SetY($yPosScholarship);

    
    $pdf->Cell(6, $heightScholarship, $number++, 1, 0, 'C');

    // Move to the next cell for "SCHOLARSHIP GRANTS"
    $pdf->Cell(60, $heightScholarship, '', 0); // Empty cell

    // Move to the next cell for "Number of Scholars"
    $pdf->Cell(49.9, $heightScholarship, $numScholars, 1, 0, 'C');

    // Move to the next cell for "Privileges"
    $pdf->Cell(0.1, $heightScholarship, '', 0); // Empty cell

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(78, $heightScholarship, $privileges, 1, 0, 'L');

    $pdf->Ln();
}

$pdf->Output();

$pdfContent = ob_get_clean();

// Send appropriate headers for PDF display
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="scholarship_analytics.pdf"');

echo $pdfContent;
?>
