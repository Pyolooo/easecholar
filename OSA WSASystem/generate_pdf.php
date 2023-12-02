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
    $selectedStatus = isset($_GET['status']) ? $_GET['status'] : 'Accepted'; // Default to 'Accepted' if status not provided
    $query = "
        SELECT applicant_name, id_number, course, scholarship_name, status
        FROM tbl_userapp
        WHERE scholarship_id = ? AND status = ?
        UNION
        SELECT applicant_name, id_number, course, scholarship_name, status
        FROM tbl_scholarship_1_form
        WHERE scholarship_id = ? AND status = ?
    ";
    $stmt = mysqli_prepare($dbConn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $selectedScholarshipId, $selectedStatus, $selectedScholarshipId, $selectedStatus);
    }
} else {
    $selectedStatus = isset($_GET['status']) ? $_GET['status'] : 'Accepted'; // Default to 'Accepted' if status not provided
    $query = "
        SELECT applicant_name, id_number, course, scholarship_name, status
        FROM tbl_userapp
        WHERE status = ?
        UNION
        SELECT applicant_name, id_number, course, scholarship_name, status
        FROM tbl_scholarship_1_form
        WHERE status = ?
    ";
    $stmt = mysqli_prepare($dbConn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $selectedStatus, $selectedStatus);
    }
}

if ($stmt && mysqli_stmt_execute($stmt)) {
    $result = mysqli_stmt_get_result($stmt);
} else {
    die('Error preparing or executing statement: ' . mysqli_error($dbConn));
}

$number = 1;

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(10, 10,  'No.', 1, 0, 'C');
$pdf->Cell(25, 10, 'ID Number', 1, 0, 'C');
$pdf->Cell(60, 10, 'Applicant Name', 1, 0, 'C');
$pdf->Cell(20, 10, 'Course', 1, 0, 'C');
$pdf->Cell(77, 10, 'Type of Scholarship', 1, 0, 'C');
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


// ...

foreach ($groupedApplicants as $applicantName => $data) {
    $pdf->SetFont('Arial', '', 9);

    // Set a consistent line height for all cells
    $lineHeight = 6;

    // Calculate the number of lines for the MultiCell
    $numLinesScholarship = calculateNumLines($pdf, implode(", ", $data['scholarships']), 77);

    // Use the consistent line height for all cells
    $pdf->Cell(10, $lineHeight * $numLinesScholarship, $number, 1, 0, 'C');
    $pdf->Cell(25, $lineHeight * $numLinesScholarship, $data['idNumber'], 1, 0, 'C');
    $pdf->Cell(60, $lineHeight * $numLinesScholarship, $applicantName, 1, 0, 'C');
    $pdf->Cell(20, $lineHeight * $numLinesScholarship, $data['course'], 1, 0, 'C');

  
    $pdf->MultiCell(77, $lineHeight, implode(", ", $data['scholarships']), 1, 'C');

    $pdf->SetY($pdf->GetY());

    // Increment the row number
    $number++;
}



function calculateNumLines($pdf, $text, $maxWidth) {
    $pdf->SetFont('Arial', '', 9);

    $words = explode(' ', $text);
    $currentWidth = 0;
    $numLines = 1;

    foreach ($words as $word) {
        $wordWidth = $pdf->GetStringWidth($word);

        if ($currentWidth + $wordWidth <= $maxWidth) {
            $currentWidth += $wordWidth + $pdf->GetStringWidth(' '); // Add space width
        } else {
            $currentWidth = $wordWidth;
            $numLines++;
        }
    }

    return $numLines;
}




$pdf->Output();

$pdfContent = ob_get_clean();

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="applicants.pdf"');

echo $pdfContent;
?>
