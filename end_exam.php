<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['student_id'], $_SESSION['exam_id'])) {
    die("Invalid session!");
}

$student_id = $_SESSION['student_id'];
$exam_id = $_SESSION['exam_id'];

// Fetch attempted answers
$query = "SELECT COUNT(*) AS attempted FROM results WHERE exam_id = ? AND student_id = ? AND answer IS NOT NULL";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $exam_id, $student_id);
$stmt->execute();
$stmt->bind_result($attempted_questions);
$stmt->fetch();
$stmt->close();

// Fetch total questions
$total_questions = $_SESSION['total_questions'];

// Fetch correct answers
$query = "SELECT COUNT(*) AS correct FROM results WHERE exam_id = ? AND student_id = ? AND answer = correct_answer";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $exam_id, $student_id);
$stmt->execute();
$stmt->bind_result($correct_answers);
$stmt->fetch();
$stmt->close();

// Calculate incorrect answers
$incorrect_answers = $attempted_questions - $correct_answers;
$unattempted_questions = $total_questions - $attempted_questions;

// Store exam summary
$query = "INSERT INTO exam (exam_id, student_id, total_questions, attempted_questions, unattempted_questions, correct, incorrect) 
          VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("siiiiii", $exam_id, $student_id, $total_questions, $attempted_questions, $unattempted_questions, $correct_answers, $incorrect_answers);
$stmt->execute();
$stmt->close();

// Clear session
unset($_SESSION['exam_id'], $_SESSION['questions'], $_SESSION['total_questions'], $_SESSION['current_question']);

header("Location: analyser.php?exam_id=" . $exam_id);
exit;
?>
