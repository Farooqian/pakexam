<?php
require_once 'vendor/autoload.php';  // Ensure PHPWord is autoloaded

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;  // To handle unit conversions
use PhpOffice\PhpWord\SimpleType\Jc;    // Correct class for alignment constants

if (!class_exists('PhpOffice\\PhpWord\\PhpWord')) {
    die("PHPWord class not found. Check autoload or installation.");
}

require_once("db_connection.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['exam_id'])) {
    die("Exam ID is missing.");
}

$exam_id = $_GET['exam_id'];

// Fetch exam data, including start_time and extract the date part
$query = "SELECT ef.exam_id, ef.student_id, ef.organization, ef.subject, DATE(ef.start_time) as exam_date, ef.question, ef.option1, ef.option2, ef.option3, ef.option4, ef.correct_answer, ef.selected_answer, ef.is_reviewed, ef.is_problematic FROM exam_final ef WHERE ef.exam_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $exam_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("No data found for this Exam ID.");
}

// Create a new PHPWord object
$phpWord = new PhpWord();

// Set properties for the Word document
$phpWord->getDocInfo()->setCreator("Rayzon");
$phpWord->getDocInfo()->setTitle("Exam Results");

// Add a new section for the document with landscape orientation
$sectionStyle = [
    'orientation' => 'landscape',
    'marginLeft' => Converter::cmToTwip(1.5),
    'marginRight' => Converter::cmToTwip(1.5),
    'marginTop' => Converter::cmToTwip(1.5),
    'marginBottom' => Converter::cmToTwip(1.5),
];
$section = $phpWord->addSection($sectionStyle);

// Header Information (3 rows)
$section->addText('Student ID: ' . $exam_id, ['size' => 12, 'bold' => true], ['alignment' => Jc::LEFT]);

// Fetch the data row
$row = $result->fetch_assoc();
$organization = $row['organization'];
$subject = $row['subject'];
$exam_date = $row['exam_date'];  // Use the extracted date from start_time

$section->addText('Organization: ' . $organization, ['size' => 12, 'bold' => true], ['alignment' => Jc::LEFT]);
$section->addText('Subject: ' . $subject, ['size' => 12, 'bold' => true], ['alignment' => Jc::LEFT]);
$section->addText('Exam Date: ' . date("d-M-Y", strtotime($exam_date)), ['size' => 12, 'bold' => true], ['alignment' => Jc::LEFT]);
$section->addTextBreak(1); // Add space between header and table

// Define table style with borders
$tableStyle = [
    'borderSize' => 6,  // Border size (thickness in twips)
    'borderColor' => '000000',  // Border color (black)
    'cellMargin' => 50,  // Cell margin
    'alignment' => Jc::CENTER,  // Table alignment
];

// Add the table style to the PHPWord object
$phpWord->addTableStyle('ExamTable', $tableStyle);

// Add a table for the results
$table = $section->addTable('ExamTable');

// Define headers with borders
$table->addRow();
$table->addCell(2000, ['borderSize' => 6, 'borderColor' => '000000'])->addText('Q. No', ['bold' => true]);
$table->addCell(4000, ['borderSize' => 6, 'borderColor' => '000000'])->addText('Question', ['bold' => true]);
$table->addCell(2000, ['borderSize' => 6, 'borderColor' => '000000'])->addText('Option A', ['bold' => true]);
$table->addCell(2000, ['borderSize' => 6, 'borderColor' => '000000'])->addText('Option B', ['bold' => true]);
$table->addCell(2000, ['borderSize' => 6, 'borderColor' => '000000'])->addText('Option C', ['bold' => true]);
$table->addCell(2000, ['borderSize' => 6, 'borderColor' => '000000'])->addText('Option D', ['bold' => true]);
$table->addCell(2000, ['borderSize' => 6, 'borderColor' => '000000'])->addText('Correct Answer', ['bold' => true]);
$table->addCell(2000, ['borderSize' => 6, 'borderColor' => '000000'])->addText('Selected Answer', ['bold' => true]);
$table->addCell(2000, ['borderSize' => 6, 'borderColor' => '000000'])->addText('Reviewed', ['bold' => true]);
$table->addCell(2000, ['borderSize' => 6, 'borderColor' => '000000'])->addText('Problematic', ['bold' => true]);

// Loop through the results and add each question to the table
$rowCount = 1;
do {
    $table->addRow();
    $table->addCell(2000, ['borderSize' => 6, 'borderColor' => '000000'])->addText($rowCount);
    $table->addCell(4000, ['borderSize' => 6, 'borderColor' => '000000'])->addText($row['question']);
    $table->addCell(2000, ['borderSize' => 6, 'borderColor' => '000000'])->addText($row['option1']);
    $table->addCell(2000, ['borderSize' => 6, 'borderColor' => '000000'])->addText($row['option2']);
    $table->addCell(2000, ['borderSize' => 6, 'borderColor' => '000000'])->addText($row['option3']);
    $table->addCell(2000, ['borderSize' => 6, 'borderColor' => '000000'])->addText($row['option4']);
    $table->addCell(2000, ['borderSize' => 6, 'borderColor' => '000000'])->addText($row['correct_answer']);
    $table->addCell(2000, ['borderSize' => 6, 'borderColor' => '000000'])->addText($row['selected_answer']);
    $table->addCell(2000, ['borderSize' => 6, 'borderColor' => '000000'])->addText($row['is_reviewed'] ? 'Yes' : 'No');
    $table->addCell(2000, ['borderSize' => 6, 'borderColor' => '000000'])->addText($row['is_problematic'] ? 'Yes' : 'No');
    $rowCount++;
} while ($row = $result->fetch_assoc());

// Add footer with page numbers
$footer = $section->addFooter();
$footer->addPreserveText('Page {PAGE} of {NUMPAGES}', null, ['alignment' => Jc::CENTER]);

// Save the Word file
$filename = "Exam_Results_" . $exam_id . ".docx";
header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
header("Content-Disposition: attachment;filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$writer->save('php://output');
exit();
?>
