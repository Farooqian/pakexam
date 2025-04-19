<?php
include 'db_connection.php';
// In store_questions.php
include_once 'exam_progress.php'; // Only if necessary, and use include_once
function storeSelectedQuestions($exam_id, $questions, $student_id) {
    global $conn;
    $query = "INSERT INTO exam_questions (exam_id, question_id, question, option1, option2, option3, option4, correct_answer, student_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    foreach ($questions as $question) {
        // Ensure correct_answer is not null
        if (empty($question['correct_answer'])) {
            error_log("Error: correct_answer is null for question_id: " . $question['id']);
            continue; // Skip this question if correct_answer is not set
        }

        $stmt->bind_param("sissssssi", $exam_id, $question['id'], $question['question'], $question['option1'], $question['option2'], $question['option3'], $question['option4'], $question['correct_answer'], $student_id);
       
        if (!$stmt->execute()) {
            error_log("Error executing query: " . $stmt->error);
        } else {
            error_log("Inserted question_id: " . $question['id'] . " into exam_questions");
        }
    }
    $stmt->close();
}
?>