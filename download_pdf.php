<?php
require_once "db_connection.php";
require('fpdf/fpdf.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['registration_number'])) {
    header("Location: login.php");
    exit();
}

$registration_number = $_SESSION['registration_number'];

$stmt = $conn->prepare("SELECT exam_id, organization, subject, total_questions, attempted_questions, correct_answers, wrong_answers, score, result_date FROM results WHERE student_id = ?");
$stmt->bind_param("s", $registration_number);
$stmt->execute();
$result = $stmt->get_result();

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Cell(190,10,'Rayzon Exam Results',0,1,'C');
$pdf->Ln(5);

$pdf->SetFont('Arial','B',10);
$headers = ['Exam ID', 'Org', 'Subject', 'Total', 'Attempted', 'Correct', 'Wrong', 'Score', 'Date'];
foreach ($headers as $header) {
    $pdf->Cell(21, 10, $header, 1, 0, 'C');
}
$pdf->Ln();

$pdf->SetFont('Arial','',9);
while ($row = $result->fetch_assoc()) {
    foreach ($row as $value) {
        $pdf->Cell(21, 10, $value, 1, 0, 'C');
    }
    $pdf->Ln();
}

$pdf->Output('D', 'exam_results.pdf');
exit();
