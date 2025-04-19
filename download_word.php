<?php
require_once "db_connection.php";
require_once 'vendor/autoload.php'; // Path to Composer autoload

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

session_start();

if (!isset($_SESSION['registration_number'])) {
    header("Location: login.php");
    exit();
}

$registration_number = $_SESSION['registration_number'];

$stmt = $conn->prepare("SELECT exam_id, organization, subject, total_questions, attempted_questions, correct_answers, wrong_answers, score, result_date FROM results WHERE student_id = ?");
$stmt->bind_param("s", $registration_number);
$stmt->execute();
$result = $stmt->get_result();

$phpWord = new PhpWord();
$section = $phpWord->addSection();

$section->addTitle("Rayzon Exam Results", 1);

$table = $section->addTable([
    'borderSize' => 6,
    'borderColor' => '999999',
    'cellMargin' => 50
]);

// Add headers
$headers = ['Exam ID', 'Org', 'Subject', 'Total', 'Attempted', 'Correct', 'Wrong', 'Score', 'Date'];
$headerRow = $table->addRow();
foreach ($headers as $header) {
    $headerRow->addCell(1000)->addText($header, ['bold' => true]);
}

// Add data rows
while ($row = $result->fetch_assoc()) {
    $tableRow = $table->addRow();
    foreach ($row as $value) {
        $tableRow->addCell(1000)->addText($value);
    }
}

// Output
$filename = 'exam_results.docx';
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$objWriter = IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save('php://output');
exit();
