<?php
require_once("db_connection.php");
require('fpdf/fpdf.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['exam_id'])) {
    die("Exam ID missing");
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

$first_row = $result->fetch_assoc();
$student_id = $first_row['student_id'];
$organization = $first_row['organization'];
$subject = $first_row['subject'];
$exam_date = date("d-M-Y", strtotime($first_row['start_time'] ?? date("Y-m-d"))); // fallback

// Get student full name
$full_name = "Student";
$user_stmt = $conn->prepare("SELECT full_name FROM users WHERE registration_number = ?");
$user_stmt->bind_param("s", $student_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
if ($user_result->num_rows > 0) {
    $user = $user_result->fetch_assoc();
    $full_name = $user['full_name'];
}

// Custom PDF class for footer
class PDF extends FPDF
{
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'C');
    }

    function Header()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Exam Report', 0, 1, 'C');
    }
}

$pdf = new PDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();

// Colors
$headerBg = [220, 230, 241];
$rowBg = [255, 255, 255];
$altRowBg = [245, 245, 245];
$headerFontColor = [0, 0, 80];
$cellBorder = 1;

// Font
$pdf->SetFont('Arial', '', 11);

// Header Info
$pdf->SetFillColor(230, 240, 250);
$pdf->SetTextColor(0);
$pdf->Cell(95, 8, "Student: " . $full_name, 0, 0, 'L', true);
$pdf->Cell(95, 8, "Exam ID: " . $exam_id, 0, 0, 'L', true);
$pdf->Cell(95, 8, "Date: " . $exam_date, 0, 1, 'L', true);

$pdf->Cell(95, 8, "Student ID: " . $student_id, 0, 0, 'L', true);
$pdf->Cell(95, 8, "Organization: " . $organization, 0, 0, 'L', true);
$pdf->Cell(95, 8, "Subject: " . $subject, 0, 1, 'L', true);

$pdf->Ln(4);

// Column headers
$cols = ['Q. No', 'Question', 'Option A', 'Option B', 'Option C', 'Option D', 'Correct', 'Selected', 'Reviewed', 'Problem'];
$widths = [15, 60, 30, 30, 30, 30, 20, 20, 20, 20];

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor($headerBg[0], $headerBg[1], $headerBg[2]);
$pdf->SetTextColor($headerFontColor[0], $headerFontColor[1], $headerFontColor[2]);

foreach ($cols as $i => $col) {
    $pdf->Cell($widths[$i], 10, $col, $cellBorder, 0, 'C', true);
}
$pdf->Ln();

// Reset data pointer and loop
$result->data_seek(0);
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(0);

$rowNum = 1;
$questionsPerPage = 10;
$currentQuestionCount = 0;

while ($row = $result->fetch_assoc()) {
    // Calculate height of the row based on content
    $lineHeight = 6;

    // Measure how many lines we need to wrap the question and options text
    $maxLines = 1;
    $maxLines = max($maxLines, ceil($pdf->GetStringWidth($row['question']) / $widths[1]));
    for ($i = 1; $i <= 4; $i++) {
        $maxLines = max($maxLines, ceil($pdf->GetStringWidth($row['option'.$i]) / $widths[2]));
    }

    // Set row height based on the longest text block
    $height = $lineHeight * $maxLines;

    // Alternating row colors
    $bg = $rowNum % 2 == 0 ? $altRowBg : $rowBg;
    $pdf->SetFillColor($bg[0], $bg[1], $bg[2]);

    // Draw each cell
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    // Add question number
    $pdf->MultiCell($widths[0], $height, $rowNum, $cellBorder, 'C', true);
    $pdf->SetXY($x + $widths[0], $y);

    // Add question text
    $pdf->MultiCell($widths[1], $height, $row['question'], $cellBorder, 'L', true);

    $x2 = $x + $widths[0] + $widths[1];
    for ($i = 1; $i <= 4; $i++) {
        $pdf->SetXY($x2, $y);
        $pdf->MultiCell($widths[2], $height, $row['option'.$i], $cellBorder, 'L', true);
        $x2 += $widths[2];
    }

    // Add correct answer, selected answer, reviewed, and problematic
    $pdf->SetXY($x2, $y);
    $pdf->MultiCell($widths[6], $height, $row['correct_answer'], $cellBorder, 'C', true);
    $x2 += $widths[6];

    $pdf->SetXY($x2, $y);
    $pdf->MultiCell($widths[7], $height, $row['selected_answer'], $cellBorder, 'C', true);
    $x2 += $widths[7];

    $pdf->SetXY($x2, $y);
    $pdf->MultiCell($widths[8], $height, $row['is_reviewed'] ? 'Yes' : 'No', $cellBorder, 'C', true);
    $x2 += $widths[8];

    $pdf->SetXY($x2, $y);
    $pdf->MultiCell($widths[9], $height, $row['is_problematic'] ? 'Yes' : 'No', $cellBorder, 'C', true);

    $pdf->Ln();
    $rowNum++;
    $currentQuestionCount++;

    // Ensure a new page after every 10 questions
    if ($currentQuestionCount % $questionsPerPage == 0) {
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 10);
        foreach ($cols as $i => $col) {
            $pdf->Cell($widths[$i], 10, $col, $cellBorder, 0, 'C', true);
        }
        $pdf->Ln();
    }
}

// Output PDF
$filename = str_replace(' ', '_', $full_name) . "_" . $exam_id . ".pdf";
$pdf->Output('D', $filename);
exit;
