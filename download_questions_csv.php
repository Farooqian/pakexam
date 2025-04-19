<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once "db_connection.php";

if (!isset($_GET['exam_id'])) {
    echo "No exam ID provided.";
    exit();
}

$exam_id = $_GET['exam_id'];

// Fetch required data
$stmt = $conn->prepare("SELECT 
    exam_id, 
    student_id, 
    question_id, 
    question, 
    option1, 
    option2, 
    option3, 
    option4, 
    correct_answer, 
    selected_answer, 
    is_reviewed, 
    is_problematic, 
    organization, 
    subject, 
    start_time 
    FROM exam_final 
    WHERE exam_id = ?");
$stmt->bind_param("s", $exam_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "No questions found for this exam.";
    exit();
}

// Prepare CSV output
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="questions.csv"');

$output = fopen('php://output', 'w');

// Fetch first row to get meta info
$first_row = $result->fetch_assoc();
$exam_date = date('d-M-Y', strtotime($first_row['start_time']));

// Write top info lines
fputcsv($output, ["Student ID: " . $first_row['student_id']]);
fputcsv($output, ["Exam ID: " . $first_row['exam_id']]);
fputcsv($output, ["Organization: " . $first_row['organization']]);
fputcsv($output, ["Subject: " . $first_row['subject']]);
fputcsv($output, ["Exam Date: " . $exam_date]);
fputcsv($output, []); // Blank line before table

// Table headers (including Question Number)
$headers = [
    "Question Number",
    "question",
    "option1",
    "option2",
    "option3",
    "option4",
    "correct_answer",
    "your answer",
    "Marked for reviewed",
    "Marked problematic",
    "Result"
];
fputcsv($output, $headers);

// Write first row (already fetched)
$question_number = 1; // Start with question 1
$result_text = ($first_row['selected_answer'] == $first_row['correct_answer']) ? 'Correct' : 'Wrong';
$data = [
    $question_number++,
    $first_row['question'],
    $first_row['option1'],
    $first_row['option2'],
    $first_row['option3'],
    $first_row['option4'],
    $first_row['correct_answer'],
    $first_row['selected_answer'],
    $first_row['is_reviewed'] ? 'Yes' : 'No',
    $first_row['is_problematic'] ? 'Yes' : 'No',
    $result_text
];
fputcsv($output, $data);

// Write remaining rows
while ($row = $result->fetch_assoc()) {
    $result_text = ($row['selected_answer'] == $row['correct_answer']) ? 'Correct' : 'Wrong';
    $data = [
        $question_number++,
        $row['question'],
        $row['option1'],
        $row['option2'],
        $row['option3'],
        $row['option4'],
        $row['correct_answer'],
        $row['selected_answer'],
        $row['is_reviewed'] ? 'Yes' : 'No',
        $row['is_problematic'] ? 'Yes' : 'No',
        $result_text
    ];
    fputcsv($output, $data);
}

fclose($output);
exit();
?>
