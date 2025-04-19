<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db_connection.php';
include_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user
$user_id = $_SESSION['user_id'];
$query = "SELECT full_name, email, registration_number FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User not found!";
    exit();
}

$student_id = $user['registration_number'];
$_SESSION['student_id'] = $student_id;

// Generate exam ID
$year=date('y');
$month=date('m');
$day = date('d');
$time = date('Hi');
$exam_id = substr($student_id . $year . $month . $day . $time, 0, 20);
$_SESSION['exam_id'] = $exam_id;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_exam'])) {
    if (!isset($_SESSION['organization']) || !isset($_SESSION['subject'])) {
        header("Location: select.php");
        exit();
    }

    $organization = $_SESSION['organization'];
    $subject = $_SESSION['subject'];

 

    // RANDOMIZE LOGIC: Fetch all matching question IDs
    $query = "SELECT id FROM MCQs WHERE organization = ? AND subject = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $organization, $subject);
    $stmt->execute();
    $result = $stmt->get_result();

    $question_ids = [];
    while ($row = $result->fetch_assoc()) {
        $question_ids[] = $row['id'];
    }

    // Shuffle and select 50
    shuffle($question_ids);
    $selected_ids = array_slice($question_ids, 0, 50);

    if (count($selected_ids) === 0) {
        die("No questions available for selected subject and organization.");
    }

    // Fetch full question data
    $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
    $types = str_repeat('i', count($selected_ids));
    $query = "SELECT id, question, option1, option2, option3, option4, correct_answer FROM MCQs WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$selected_ids);
    $stmt->execute();
    $question_result = $stmt->get_result();

    $questions = [];
    while ($row = $question_result->fetch_assoc()) {
        $questions[] = $row;
    }

    $total_questions = count($questions);
    $exam_duration = ($total_questions < 50) ? ceil($total_questions * 0.6) : 30;

    $_SESSION['questions'] = $questions;
    $_SESSION['total_questions'] = $total_questions;
    $_SESSION['exam_duration'] = $exam_duration;
    $_SESSION['exam_end_time'] = time() + ($exam_duration * 60);

    // Save into exam_questions and exam_progress
    foreach ($questions as $question) {
        // Save exam_questions
        $query = "INSERT INTO exam_questions (exam_id, student_id, question_id, question, option1, option2, option3, option4, correct_answer) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("siissssss", $exam_id, $student_id, $question['id'], $question['question'], $question['option1'], $question['option2'], $question['option3'], $question['option4'], $question['correct_answer']);
        $stmt->execute();

        // Save exam_progress
        $query = "INSERT INTO exam_progress (exam_id, student_id, organization, subject, question_id, question, option1, option2, option3, option4, correct_answer, start_time, end_time) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, UTC_TIMESTAMP(), DATE_ADD(UTC_TIMESTAMP(), INTERVAL ? MINUTE))";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sississssssi", $exam_id, $student_id, $organization, $subject, $question['id'], $question['question'], $question['option1'], $question['option2'], $question['option3'], $question['option4'], $question['correct_answer'], $exam_duration);
        $stmt->execute();
    }

    // Start the exam
    header("Location: exam.php");
    exit();
}

// GET only
if (!isset($_GET['organization']) || !isset($_GET['subject'])) {
    header("Location: select.php");
    exit();
}

$organization = htmlspecialchars($_GET['organization']);
$subject = htmlspecialchars($_GET['subject']);
$_SESSION['organization'] = $organization;
$_SESSION['subject'] = $subject;

$_SESSION['full_name'] = $user['full_name'];
$_SESSION['email'] = $user['email'];
?>

<!-- HTML remains unchanged -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Details | ProjectD</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #1E90FF, #FF69B4);
            color: white;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 800px;
            margin: 50px auto;
            color: black;
        }
        .details-box {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
            font-size: 16px;
            background: #1E90FF;
            color: white;
            border: none;
        }
        .btn:hover {
            background: #FF69B4;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Confirm Your Details</h2>
    <div class="details-box">
        <p><strong>Name:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Student ID:</strong> <?= htmlspecialchars($user['registration_number']) ?></p>
        <p><strong>Subject:</strong> <?= htmlspecialchars($subject) ?></p>
        <p><strong>Organization:</strong> <?= htmlspecialchars($organization) ?></p>
        <p><strong>Exam ID:</strong> <?= htmlspecialchars($exam_id) ?></p>
    </div>

    <form action="confirm.php" method="POST">
        <input type="hidden" name="confirm_exam" value="1">
        <button type="submit" class="btn">Confirm and Start Exam</button>
    </form>
</div>

</body>
</html>
