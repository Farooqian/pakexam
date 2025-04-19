<?php
// Start the session if not active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include 'db_connection.php';

// Validate session variables
if (!isset($_SESSION['exam_id'])) {
    echo "Error: Missing exam ID. Please start the exam again.";
    exit;
}

$exam_id = $_SESSION['exam_id'];

// Derive student_id from the first 10 digits of exam_id
$student_id = substr($exam_id, 0, 10);

// Debug derived variables
echo "Exam ID: $exam_id<br>";
echo "Derived Student ID: $student_id<br>";

// Fetch the exam results for the current student
$query = "SELECT * FROM exam_progress WHERE exam_id = ? AND student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $exam_id, $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Error: Exam not found. Please contact the administrator.";
    exit;
}

// Fetch exam details
$exam_details = [];
while ($row = $result->fetch_assoc()) {
    $exam_details[] = $row;
}

// Display results
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        .container {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 800px;
            margin: 50px auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background: #0077cc;
            color: #fff;
        }
        tr:nth-child(even) {
            background: #f2f2f2;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Exam Results</h1>
    <table>
        <thead>
        <tr>
            <th>Question</th>
            <th>Your Answer</th>
            <th>Correct Answer</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($exam_details as $detail): ?>
            <tr>
                <td><?= htmlspecialchars($detail['question']) ?></td>
                <td><?= htmlspecialchars($detail['selected_answer'] ?? 'Not Answered') ?></td>
                <td><?= htmlspecialchars($detail['correct_answer']) ?></td>
                <td>
                    <?php
                    if ($detail['selected_answer'] === $detail['correct_answer']) {
                        echo "Correct";
                    } elseif (empty($detail['selected_answer'])) {
                        echo "Not Answered";
                    } else {
                        echo "Incorrect";
                    }
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>