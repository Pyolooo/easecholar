<?php
require 'vendor/autoload.php';
include '../include/connection.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (isset($_GET['scholarship_id'])) {
    $selectedScholarshipId = $_GET['scholarship_id'];
    $query = "SELECT ua.id_number, ua.applicant_name, ua.course, ua.gender, ua.scholarship_name, ts.benefits
              FROM tbl_userapp ua
              JOIN tbl_scholarship ts ON ua.scholarship_id = ts.scholarship_id
              WHERE ua.scholarship_id = '$selectedScholarshipId' AND ua.status = 'Accepted'
              UNION
              SELECT sf.id_number, sf.applicant_name, sf.course, sf.gender, sf.scholarship_name, ts.benefits
              FROM tbl_scholarship_1_form sf
              JOIN tbl_scholarship ts ON sf.scholarship_id = ts.scholarship_id
              WHERE sf.scholarship_id = '$selectedScholarshipId' AND sf.status = 'Accepted'";
} else {
    $query = "SELECT ua.id_number, ua.applicant_name, ua.course, ua.gender, ua.scholarship_name, ts.benefits
              FROM tbl_userapp ua
              JOIN tbl_scholarship ts ON ua.scholarship_id = ts.scholarship_id
              WHERE ua.status = 'Accepted'
              UNION
              SELECT sf.id_number, sf.applicant_name, sf.course, sf.gender, sf.scholarship_name, ts.benefits
              FROM tbl_scholarship_1_form sf
              JOIN tbl_scholarship ts ON sf.scholarship_id = ts.scholarship_id
              WHERE sf.status = 'Accepted'";
}


$result = mysqli_query($dbConn, $query);
$result = mysqli_query($dbConn, $query);
if (!$result) {
    die("Error in SQL query: " . mysqli_error($dbConn));
}



$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Add headers to the Excel file
$sheet->setCellValue('A1', 'No.');
$sheet->setCellValue('B1', 'ID Number');
$sheet->setCellValue('C1', 'Applicant Name');
$sheet->setCellValue('D1', 'Course');
$sheet->setCellValue('E1', 'Gender');
$sheet->setCellValue('F1', 'Type of Scholarship');
$sheet->setCellValue('G1', 'Privelege/Benefits'); // Add Benefits header

$number = 1;
$rowNumber = 2;

while ($row = mysqli_fetch_assoc($result)) {
    $applicantName = $row['applicant_name'];
    $idNumber = $row['id_number'];
    $course = $row['course'];
    $sex = $row['gender'];
    $scholarshipName = $row['scholarship_name'];
    $benefits = $row['benefits'];

    $sheet->setCellValue('A' . $rowNumber, $number);
    $sheet->setCellValue('B' . $rowNumber, $idNumber);
    $sheet->setCellValue('C' . $rowNumber, $applicantName);
    $sheet->setCellValue('D' . $rowNumber, $course);
    $sheet->setCellValue('E' . $rowNumber, $sex);
    $sheet->setCellValue('F' . $rowNumber, $scholarshipName);
    $sheet->setCellValue('G' . $rowNumber, $benefits);

    $number++;
    $rowNumber++;
}

$writer = new Xlsx($spreadsheet);

// Send appropriate headers for Excel download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="qualified_applicants.xlsx"');
$writer->save('php://output');
