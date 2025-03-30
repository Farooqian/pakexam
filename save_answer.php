<?php
session_start();
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['student_id'], $_SESSION['exam_id'], $_POST['question_id'], $_POST['answer'])) {
        die("Invalid request!");
    }

    $student_id = $_SESSION['student_id'];
    $exam_id = $_SESSION['exam_id'];
    $question_id = $_POST['question_id'];
    $selected_answer = $_POST['answer'];

    // Retrieve correct answer
    $query = "SELECT question, correct_answer FROM MCQs WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($question_text, $correct_answer);
    $stmt->fetch();
    $stmt->close();

    // Save answer in results table
    $query = "INSERT INTO results (exam_id, student_id, question_id, question_text, answer, correct_answer, is_reviewed, is_problematic) 
              VALUES (?, ?, ?, ?, ?, ?, 0, 0) 
              ON DUPLICATE KEY UPDATE answer = VALUES(answer)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("siissi", $exam_id, $student_id, $question_id, $question_text, $selected_answer, $correct_answer);
    $stmt->execute();
    $stmt->close();
}
?>
