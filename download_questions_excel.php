<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require 'vendor/autoload.php'; // PhpSpreadsheet autoload

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

require_once("db_connection.php");

if (!isset($_GET['exam_id'])) {
    die("Exam ID is missing.");
}

$exam_id = $_GET['exam_id'];

// Fetch exam data
$query = "SELECT * FROM exam_final WHERE exam_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $exam_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("No data found for this Exam ID.");
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Fetch first row for metadata
$first_row = $result->fetch_assoc();
$student_id = $first_row['student_id'];
$organization = $first_row['organization'];
$subject = $first_row['subject'];
$exam_date = date("d-M-Y", strtotime($first_row['exam_date'] ?? date("Y-m-d"))); // fallback: today

// Get full name from users table
$full_name = 'Student'; // fallback
$user_stmt = $conn->prepare("SELECT full_name FROM users WHERE registration_number = ?");
$user_stmt->bind_param("s", $student_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows > 0) {
    $user = $user_result->fetch_assoc();
    $full_name = str_replace(' ', '_', $user['full_name']); // clean for filename
}

// Header information
$sheet->setCellValue('A1', "Student ID: $student_id");
$sheet->setCellValue('C1', "Exam ID: $exam_id");
$sheet->setCellValue('A2', "Organization: $organization");
$sheet->setCellValue('C2', "Subject: $subject");
$sheet->setCellValue('A3', "Exam Date: $exam_date");

// Column Headers
$sheet->setCellValue('A5', 'Q. No');
$sheet->setCellValue('B5', 'Question');
$sheet->setCellValue('C5', 'Option A');
$sheet->setCellValue('D5', 'Option B');
$sheet->setCellValue('E5', 'Option C');
$sheet->setCellValue('F5', 'Option D');
$sheet->setCellValue('G5', 'Correct Answer');
$sheet->setCellValue('H5', 'Selected Answer');
$sheet->setCellValue('I5', 'Reviewed');
$sheet->setCellValue('J5', 'Problematic');

// Optional: Style headers
$headerStyle = $sheet->getStyle('A5:J5');
$headerStyle->getFont()->setBold(true);
$headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');

// Optional: Auto width for all columns
foreach (range('A', 'J') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Reset pointer and write questions
$result->data_seek(0);
$row = 6;

while ($data = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $row, $row - 5); // Q. No from 1
    $sheet->setCellValue('B' . $row, $data['question']);
    $sheet->setCellValue('C' . $row, $data['option1']);
    $sheet->setCellValue('D' . $row, $data['option2']);
    $sheet->setCellValue('E' . $row, $data['option3']);
    $sheet->setCellValue('F' . $row, $data['option4']);
    $sheet->setCellValue('G' . $row, $data['correct_answer']);
    $sheet->setCellValue('H' . $row, $data['selected_answer']);
    $sheet->setCellValue('I' . $row, $data['is_reviewed'] ? 'Yes' : 'No');
    $sheet->setCellValue('J' . $row, $data['is_problematic'] ? 'Yes' : 'No');
    $row++;
}

// Filename
$filename = $full_name . "_" . $exam_id . ".xlsx";

// Output to browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
