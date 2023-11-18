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

// Helper function to calculate the number of lines for a given text and width
function calculateNumLines($pdf, $text, $maxWidth) {
    $pdf->SetFont('Arial', '', 9);
    $lineHeight = 10; // Set the line height (adjust as needed)

    // Manually calculate the number of lines based on the width
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

foreach ($groupedApplicants as $applicantName => $data) {
    $pdf->SetFont('Arial', '', 9);

    // Set the line height (adjust as needed)
    $lineHeight = 10;

    // Calculate the number of lines for the MultiCell
    $numLines = calculateNumLines($pdf, implode(", ", $data['scholarships']), 60);

    // Use the number of lines for the other cells
    $pdf->Cell(10, $numLines * $lineHeight, $number++, 1, 0, 'C');
    $pdf->Cell(40, $numLines * $lineHeight, $data['idNumber'], 1, 0, 'C');
    $pdf->Cell(40, $numLines * $lineHeight, $applicantName, 1, 0, 'C');
    $pdf->Cell(40, $numLines * $lineHeight, $data['course'], 1, 0, 'C');
    $pdf->MultiCell(60, $lineHeight, implode(", ", $data['scholarships']), 1, 'C');

    // Move to the next line
    $pdf->Ln();
}


// Output the PDF as inline content
$pdf->Output();

$pdfContent = ob_get_clean();

// Send appropriate headers for PDF display
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="applicants.pdf"');

echo $pdfContent;
?>
