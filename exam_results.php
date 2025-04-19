<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'db_connection.php';

// Check if session variables are set
if (!isset($_SESSION['exam_id']) || !isset($_SESSION['user_id'])) {
    echo "Error: Exam ID or User ID is not set in the session.";
    exit;
}

$exam_id = $_SESSION['exam_id'];
$user_id = $_SESSION['user_id'];

// Fetch student details
$name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : '';
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$subject = isset($_SESSION['subject']) ? $_SESSION['subject'] : '';
$organization = isset($_SESSION['organization']) ? $_SESSION['organization'] : '';

// Fetch exam questions and answers
$query = "SELECT ep.question_id, ep.selected_answer, eq.correct_answer, eq.question, eq.option1, eq.option2, eq.option3, eq.option4
          FROM exam_progress ep
          JOIN exam_questions eq ON ep.question_id = eq.id
          WHERE ep.exam_id = ? AND ep.student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $exam_id, $user_id);
$stmt->execute();
$results = $stmt->get_result();

if (!$results) {
    die("Error fetching exam results: " . $stmt->error);
}

$questions = $results->fetch_all(MYSQLI_ASSOC);

if (empty($questions)) {
    echo "No results found for this exam.";
    error_log("No results found for exam_id: $exam_id", 0);
    exit;
}

// Calculate score
$total_questions = count($questions);
$correct_answers = 0;
foreach ($questions as $question) {
    if ($question['selected_answer'] == $question['correct_answer']) {
        $correct_answers++;
    }
}
$score = ($correct_answers / $total_questions) * 100;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Results</title>
</head>
<body>
    <div id="studentDetails">
        <h2>Student Details</h2>
        <p>Name: <?= htmlspecialchars($name) ?></p>
        <p>ID: <?= htmlspecialchars($_SESSION['student_id']) ?></p>
        <p>Email: <?= htmlspecialchars($email) ?></p>
        <p>Organization: <?= htmlspecialchars($organization) ?></p>
        <p>Subject: <?= htmlspecialchars($subject) ?></p>
        <p>Exam ID: <?= htmlspecialchars($exam_id) ?></p>
    </div>

    <div id="examResults">
        <h2>Exam Results</h2>
        <p>Total Questions: <?= $total_questions ?></p>
        <p>Correct Answers: <?= $correct_answers ?></p>
        <p>Score: <?= number_format($score, 2) ?>%</p>

        <h3>Question-wise Details</h3>
        <table border="1">
            <tr>
                <th>Question</th>
                <th>Option 1</th>
                <th>Option 2</th>
                <th>Option 3</th>
                <th>Option 4</th>
                <th>Correct Answer</th>
                <th>Your Answer</th>
            </tr>
            <?php foreach ($questions as $question): ?>
            <tr>
                <td><?= htmlspecialchars($question['question']) ?></td>
                <td><?= htmlspecialchars($question['option1']) ?></td>
                <td><?= htmlspecialchars($question['option2']) ?></td>
                <td><?= htmlspecialchars($question['option3']) ?></td>
                <td><?= htmlspecialchars($question['option4']) ?></td>
                <td><?= htmlspecialchars($question['correct_answer']) ?></td>
                <td><?= htmlspecialchars($question['selected_answer']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>