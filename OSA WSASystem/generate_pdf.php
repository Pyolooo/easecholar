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

if (isset($_GET['scholarship_id'])) {
    $selectedScholarshipId = $_GET['scholarship_id'];
    $query = "
        SELECT applicant_name, id_number, course, scholarship_name, status
        FROM tbl_userapp
        WHERE scholarship_id = ? AND status = 'Accepted'
        UNION
        SELECT applicant_name, id_number, course, scholarship_name, status
        FROM tbl_scholarship_1_form
        WHERE scholarship_id = ? AND status = 'Accepted'
    ";
    $stmt = mysqli_prepare($dbConn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $selectedScholarshipId, $selectedScholarshipId);
    }
} else {
    $query = "
        SELECT applicant_name, id_number, course, scholarship_name, status
        FROM tbl_userapp
        WHERE status = 'Accepted'
        UNION
        SELECT applicant_name, id_number, course, scholarship_name, status
        FROM tbl_scholarship_1_form
        WHERE status = 'Accepted'
    ";
    $stmt = mysqli_prepare($dbConn, $query);
}

if ($stmt && mysqli_stmt_execute($stmt)) {
    $result = mysqli_stmt_get_result($stmt);
} else {
    die('Error preparing or executing statement: ' . mysqli_error($dbConn));
}


$number = 1;

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(10, 10,  'No.', 1, 0, 'C');
$pdf->Cell(40, 10, 'ID Number', 1, 0, 'C');
$pdf->Cell(40, 10, 'Applicant Name', 1, 0, 'C');
$pdf->Cell(40, 10, 'Course', 1, 0, 'C');
$pdf->Cell(60, 10, 'Type of Scholarship', 1, 0, 'C');
$pdf->Ln();

$groupedApplicants = [];

while ($row = mysqli_fetch_assoc($result)) {
    $applicantName = $row['applicant_name'];
    $idNumber = $row['id_number'];
    $course = $row['course'];
    $scholarshipName = $row['scholarship_name'];

    if (!isset($groupedApplicants[$applicantName])) {
        $groupedApplicants[$applicantName] = [
            'idNumber' => $idNumber,
            'course' => $course,
            'scholarships' => [$scholarshipName],
        ];
    } else {
        $groupedApplicants[$applicantName]['scholarships'][] = $scholarshipName;
    }
}

foreach ($groupedApplicants as $applicantName => $data) {
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(10, 10, $number++, 1, 0, 'C');
    $pdf->Cell(40, 10, $data['idNumber'], 1, 0, 'C');
    $pdf->Cell(40, 10, $applicantName, 1, 0, 'C');
    $pdf->Cell(40, 10, $data['course'], 1, 0, 'C');
    $pdf->Cell(60, 10, implode(", ", $data['scholarships']), 1, 0, 'C');
    $pdf->Ln();
}

// Output the PDF as inline content
$pdf->Output();

$pdfContent = ob_get_clean();

// Send appropriate headers for PDF display
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="qualified_applicants.pdf"');

echo $pdfContent;
?>
