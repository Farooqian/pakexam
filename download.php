<?php
// Include necessary libraries for file generation
require_once 'vendor/autoload.php'; // Use Composer's autoload

// Include database connection
include('db_connection.php');

// Ensure student is logged in
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// Get the student ID and format from query parameters
$student_id = $_SESSION['student_id'];
$format = isset($_GET['format']) ? $_GET['format'] : 'pdf';

// Prepare the SQL query to fetch results
$query = "SELECT * FROM results WHERE student_id = ? ORDER BY result_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $student_id); // "s" for string type
$stmt->execute();
$result = $stmt->get_result();

// Handle file download based on selected format
switch ($format) {
    case 'pdf':
        // Generate PDF (TCPDF)
        require_once 'tcpdf/tcpdf.php';
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);
        
        // Add student info and results to PDF
        $pdf->Write(0, "Student ID: $student_id\n\n");
        while ($row = $result->fetch_assoc()) {
            $pdf->Write(0, "Exam ID: " . $row['exam_id'] . "\n");
            $pdf->Write(0, "Organization: " . $row['organization'] . "\n");
            $pdf->Write(0, "Subject: " . $row['subject'] . "\n");
            // Add more fields as needed
        }

        // Output PDF
        $pdf->Output('results.pdf', 'D');
        break;

    case 'excel':
        // Generate Excel (PhpSpreadsheet)
        $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $sheet->setCellValue('A1', 'Exam ID');
        $sheet->setCellValue('B1', 'Organization');
        $sheet->setCellValue('C1', 'Subject');
        // Add more headers as needed

        // Add results
        $rowIndex = 2;
        while ($row = $result->fetch_assoc()) {
            $sheet->setCellValue('A' . $rowIndex, $row['exam_id']);
            $sheet->setCellValue('B' . $rowIndex, $row['organization']);
            $sheet->setCellValue('C' . $rowIndex, $row['subject']);
            // Add more fields as needed
            $rowIndex++;
        }

        // Save as Excel file
        $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        break;

    case 'csv':
        // Generate CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="results.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Exam ID', 'Organization', 'Subject', 'Score']); // CSV headers
        
        // Write results to CSV
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }
        fclose($output);
        break;

    case 'word':
        // Generate Word (PHPWord)
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        
        // Add student info and results
        $section->addText("Student ID: $student_id\n\n");
        while ($row = $result->fetch_assoc()) {
            $section->addText("Exam ID: " . $row['exam_id']);
            $section->addText("Organization: " . $row['organization']);
            $section->addText("Subject: " . $row['subject']);
            // Add more fields as needed
        }

        // Save Word file
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save('php://output');
        break;

    default:
        echo "Invalid format.";
        break;
}
?>
