<?php
include_once 'db_connection.php';  // Make sure database connection is included only once

// Declare the function only once
if (!function_exists('addQuestionsToProgress')) {
    function addQuestionsToProgress($exam_id, $student_id) {
        global $conn;
        $query = "INSERT INTO exam_progress (exam_id, question_id, question, option1, option2, option3, option4, correct_answer, student_id, selected_answer, last_updated)
                  SELECT exam_id, question_id, question, option1, option2, option3, option4, correct_answer, student_id, NULL, NOW()
                  FROM exam_questions WHERE exam_id = ? AND student_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $exam_id, $student_id);

        if (!$stmt->execute()) {
            error_log("Error executing query (addQuestionsToProgress): " . $stmt->error);
        } else {
            error_log("Inserted questions into exam_progress for exam_id: " . $exam_id);
        }
        $stmt->close();
    }
}

function updateSelectedAnswer($exam_id, $question_id, $selected_answer, $student_id) {
    global $conn;
    $query = "UPDATE exam_progress SET selected_answer = ?, last_updated = NOW() WHERE exam_id = ? AND question_id = ? AND student_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssii", $selected_answer, $exam_id, $question_id, $student_id);
   
    if (!$stmt->execute()) {
        error_log("Error executing query (updateSelectedAnswer): " . $stmt->error);
    } else {
        error_log("Updated selected_answer for question_id: " . $question_id);
    }
    $stmt->close();
}

function scheduleUpdateAnswers($exam_id, $answers, $student_id) {
    foreach ($answers as $question_id => $selected_answer) {
        updateSelectedAnswer($exam_id, $question_id, $selected_answer, $student_id);
    }
}
?>