<?php
require_once "db_connection.php";
require 'vendor/autoload.php'; // Make sure this is the correct path

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

session_start();

if (!isset($_SESSION['registration_number'])) {
    header("Location: login.php");
    exit();
}

$registration_number = $_SESSION['registration_number'];

// Fetch results
$stmt = $conn->prepare("SELECT exam_id, organization, subject, total_questions, attempted_questions, correct_answers, wrong_answers, score, result_date FROM results WHERE student_id = ?");
$stmt->bind_param("s", $registration_number);
$stmt->execute();
$result = $stmt->get_result();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set column headings
$sheet->fromArray(['Exam ID', 'Organization', 'Subject', 'Total', 'Attempted', 'Correct', 'Wrong', 'Score', 'Date'], NULL, 'A1');

// Fill data
$rowNumber = 2;
while ($row = $result->fetch_assoc()) {
    $sheet->fromArray(array_values($row), NULL, 'A' . $rowNumber);
    $rowNumber++;
}

// Set header for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="exam_results.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
