<?php
require 'vendor/autoload.php'; 
include '../include/connection.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$query = "SELECT s.scholarship, 
                 (COUNT(ua.user_id) + COUNT(sf.user_id)) AS num_applicants, 
                 s.benefits 
          FROM tbl_scholarship s 
          LEFT JOIN tbl_userapp ua ON s.scholarship_id = ua.scholarship_id 
          LEFT JOIN tbl_scholarship_1_form sf ON s.scholarship_id = sf.scholarship_id 
          GROUP BY s.scholarship";

$result = mysqli_query($dbConn, $query);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Add headers to the Excel file
$sheet->setCellValue('A1', 'No.');
$sheet->setCellValue('B1', 'SCHOLARSHIP GRANTS');
$sheet->setCellValue('C1', 'NUMBER OF SCHOLARS');
$sheet->setCellValue('D1', 'PRIVILEGES');

$number = 1;
$rowNumber = 2;

while ($row = mysqli_fetch_assoc($result)) {
  $scholarshipName = $row['scholarship'];
  $numScholars = $row['num_applicants'];
  $privileges = $row['benefits'];

    $sheet->setCellValue('A' . $rowNumber, $number);
    $sheet->setCellValue('B' . $rowNumber, $scholarshipName);
    $sheet->setCellValue('C' . $rowNumber, $numScholars);
    $sheet->setCellValue('D' . $rowNumber, $privileges);


    $number++;
    $rowNumber++;
}

$writer = new Xlsx($spreadsheet);

// Send appropriate headers for Excel download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="summary_per_scholarship.xlsx"');
$writer->save('php://output');

