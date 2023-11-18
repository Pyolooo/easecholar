<?php
require 'vendor/autoload.php';
include '../include/connection.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (isset($_GET['scholarship_id'])) {
    $selectedScholarshipId = $_GET['scholarship_id'];
    $selectedStatus = isset($_GET['status']) ? $_GET['status'] : 'Accepted'; // Default to 'Accepted' if status not provided
    $query = "SELECT ua.applicant_name, ua.id_number, ua.course, ua.gender, ua.scholarship_name, ts.benefits
              FROM tbl_userapp ua
              JOIN tbl_scholarship ts ON ua.scholarship_id = ts.scholarship_id
              WHERE ua.scholarship_id = '$selectedScholarshipId' AND ua.status = '$selectedStatus'
              UNION
              SELECT sf.applicant_name, sf.id_number, sf.course, sf.gender, sf.scholarship_name, ts.benefits
              FROM tbl_scholarship_1_form sf
              JOIN tbl_scholarship ts ON sf.scholarship_id = ts.scholarship_id
              WHERE sf.scholarship_id = '$selectedScholarshipId' AND sf.status = '$selectedStatus'";
} else {
    $selectedStatus = isset($_GET['status']) ? $_GET['status'] : 'Accepted'; // Default to 'Accepted' if status not provided
    $query = "SELECT ua.applicant_name, ua.id_number, ua.course, ua.gender, ua.scholarship_name, ts.benefits
              FROM tbl_userapp ua
              JOIN tbl_scholarship ts ON ua.scholarship_id = ts.scholarship_id
              WHERE ua.status = '$selectedStatus'
              UNION
              SELECT sf.applicant_name, sf.id_number, sf.course, sf.gender, sf.scholarship_name, ts.benefits
              FROM tbl_scholarship_1_form sf
              JOIN tbl_scholarship ts ON sf.scholarship_id = ts.scholarship_id
              WHERE sf.status = '$selectedStatus'";
}

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

    // Use a helper function to get the grouped scholarship names and benefits
    $scholarshipsAndBenefits = getGroupedValues($applicantName, $result);

    // Use a helper function to get the grouped scholarship names as a single string
    $scholarships = implode(', ', array_keys($scholarshipsAndBenefits));

    // Initialize an empty array for benefits
    $benefitsArray = [];

    // Loop through scholarships and fetch benefits
    foreach ($scholarshipsAndBenefits as $scholarship => $benefits) {
        $benefitsArray[] = implode(', ', $benefits);
    }

    // Use a helper function to get the grouped benefits as a single string
    $benefits = implode(', ', $benefitsArray);

    $sheet->setCellValue('A' . $rowNumber, $number);
    $sheet->setCellValue('B' . $rowNumber, $idNumber);
    $sheet->setCellValue('C' . $rowNumber, $applicantName);
    $sheet->setCellValue('D' . $rowNumber, $course);
    $sheet->setCellValue('E' . $rowNumber, $sex);
    $sheet->setCellValue('F' . $rowNumber, $scholarships);
    $sheet->setCellValue('G' . $rowNumber, $benefits);

    $number++;
    $rowNumber++;
}



$writer = new Xlsx($spreadsheet);

// Send appropriate headers for Excel download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="qualified_applicants.xlsx"');
$writer->save('php://output');

// Helper function to get grouped scholarship names
function getGroupedScholarships($applicantName, $result) {
    $groupedScholarships = [];

    // Reset the result pointer to the beginning
    mysqli_data_seek($result, 0);

    // Collect scholarship names for the given applicant
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['applicant_name'] === $applicantName) {
            $groupedScholarships[] = $row['scholarship_name'];
        }
    }

    // Return a comma-separated string of scholarship names
    return implode(', ', $groupedScholarships);
}

// Helper function to get grouped values
function getGroupedValues($applicantName, $result) {
    $groupedValues = [];

    // Reset the result pointer to the beginning
    mysqli_data_seek($result, 0);

    // Collect values for the given applicant
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['applicant_name'] === $applicantName) {
            $scholarshipName = $row['scholarship_name'];
            $benefits = $row['benefits'];
            
            // Use scholarship name as the key in the associative array
            $groupedValues[$scholarshipName][] = $benefits;
        }
    }

    // Return an associative array of scholarship names and their benefits
    return $groupedValues;
}

