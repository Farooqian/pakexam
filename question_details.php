<?php
require_once "db_connection.php";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['registration_number'])) {
    header("Location: login.php");
    exit();
}

$registration_number = $_SESSION['registration_number'];

if (!isset($_GET['exam_id'])) {
    echo "No exam ID provided.";
    exit();
}

$exam_id = $_GET['exam_id'];

// Fetch user info
$stmt_user = $conn->prepare("SELECT * FROM users WHERE registration_number = ?");
$stmt_user->bind_param("s", $registration_number);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_info = $result_user->fetch_assoc();

if (!$user_info) {
    echo "User not found.";
    exit();
}

// Fetch exam details from the exam_final table
$stmt_exam = $conn->prepare("SELECT * FROM exam_final WHERE exam_id = ? AND student_id = ?");
$stmt_exam->bind_param("ss", $exam_id, $registration_number);
$stmt_exam->execute();
$result_exam = $stmt_exam->get_result();

if ($result_exam->num_rows == 0) {
    // Display message when no results are found, and still show the navigation buttons
    echo "<p>No questions found for this exam.</p>";
}

// Fetch the exam details once
$exam_details = $result_exam->fetch_assoc(); // This fetches the first result
$stmt_exam->free_result(); // Reset the result set to reuse the statement

// Now, fetch all the questions for the exam
$stmt_exam->execute(); // Re-execute the same statement
$result_exam = $stmt_exam->get_result(); // Fetch results again for the questions

// Fetch the latest `last_updated` time from the exam_final table for the given exam_id and student_id
$stmt_last_updated = $conn->prepare("SELECT MAX(last_updated) AS last_updated FROM exam_final WHERE exam_id = ? AND student_id = ?");
$stmt_last_updated->bind_param("ss", $exam_id, $registration_number);
$stmt_last_updated->execute();
$result_last_updated = $stmt_last_updated->get_result();
$last_updated_data = $result_last_updated->fetch_assoc();

$last_updated = $last_updated_data['last_updated'];
$start_time = $exam_details['start_time'];
$end_time = $exam_details['end_time'];

// Convert start_time and end_time (in UTC) to local time (Asia/Karachi)
$timezone = new DateTimeZone('Asia/Karachi'); // Replace with the desired local timezone
$start_time_utc = new DateTime($start_time, new DateTimeZone('UTC'));
$end_time_utc = new DateTime($end_time, new DateTimeZone('UTC'));

// Convert to local time
$start_time_utc->setTimezone($timezone);
$end_time_utc->setTimezone($timezone);

// Format the local times
$start_time_local = $start_time_utc->format('Y-m-d H:i:s');
$end_time_local = $end_time_utc->format('Y-m-d H:i:s');

// Convert the last_updated time to the same timezone (local)
$last_updated_time = new DateTime($last_updated, $timezone);

// Calculate the time taken to complete the exam (difference between last_updated and start_time)
if ($last_updated) {
    // Convert both start_time_local and last_updated_time to timestamps for comparison
    $start_time_timestamp = $start_time_utc->getTimestamp(); // Use UTC timestamp
    $last_updated_timestamp = $last_updated_time->getTimestamp();

    // Calculate the time difference in seconds
    $time_taken_seconds = $last_updated_timestamp - $start_time_timestamp;

    // Ensure that time_taken_seconds is positive (if somehow the last_updated time is earlier than start_time)
    if ($time_taken_seconds < 0) {
        $time_taken_seconds = 0;
    }

    // Convert seconds to hours, minutes, and seconds
    $hours = floor($time_taken_seconds / 3600);
    $minutes = floor(($time_taken_seconds % 3600) / 60);
    $seconds = $time_taken_seconds % 60;
    $time_taken = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
} else {
    $time_taken = "N/A"; // In case the last updated time is unavailable
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question Details</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 30px;
            background-color:rgb(253, 244, 214);
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-top: 50px;
            border-left: 8px  solid #fc00f8;
            border-right: 8px  solid #fc00f8;
            border-top: 8px  solid #fc00f8;
            border-bottom: 8px  solid #fc00f8;

        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            color: #343a40;
        }
        .header h2 {
            color: #007bff;
        }
        .parallel-containers {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .container-left, .container-right {
            width: 48%;
            padding: 20px;
            border-radius: 10px;
        }
        .container-left {
            background-color: #007bff;
            color: white;
            border-left: 6px  solid #fc0000;
            border-right: 6px  solid #fc0000;
        }
        .container-right {
            background-color: #17a2b8;
            color: white;
            border-left: 6px solid #00ff04;
            border-right: 6px solid #00ff04;
        }
        .container-left p, .container-right p {
            margin: 10px 0;
            font-size: 18px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        table, th, td {
            border: 1px solid #ddd;
            text-align: left;
        }
        th, td {
            padding: 12px;
            font-size: 16px;
        }
        th {
            background-color: #28a745;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .status-correct {
            color: green;
            font-weight: bold;
        }
        .status-incorrect {
            color: red;
            font-weight: bold;
        }
        .status-incorrect, .status-correct {
            text-transform: capitalize;
        }
        .btn-container {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        .btn-container a {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }
        .btn-container a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Question Details for Exam: <?= htmlspecialchars($exam_id) ?></h2>
        </div>

        <!-- Parallel containers for Exam and Student Details -->
        <div class="parallel-containers">
            <!-- Exam Details Container -->
            <div class="container-left">
                <h3>Exam Details</h3>
                
                <p><strong>Duration:</strong> <?= htmlspecialchars($exam_details['duration_minutes']) ?> minutes</p>
                <p><strong>Start Time (Local):</strong> <?= $start_time_local ?></p>
                <p><strong>End Time (Local):</strong> <?= $end_time_local ?></p>
                <p><strong>Time Taken:</strong> <?= $time_taken ?></p> <!-- Display time taken -->
            </div>

            <!-- Student Details Container -->
            <div class="container-right">
                <h3>Student Details</h3>
                <p><strong>Full Name:</strong> <?= htmlspecialchars($user_info['full_name']) ?></p>
                <p><strong>Registration ID:</strong> <?= htmlspecialchars($user_info['registration_number']) ?></p>
                <p><strong>Organization:</strong> <?= htmlspecialchars($exam_details['organization']) ?></p>
                <p><strong>Subject:</strong> <?= htmlspecialchars($exam_details['subject']) ?></p>
            </div>
        </div>

        <h3>Question & Answer Details</h3>
        <table>
            <tr>
                <th>#</th>
                <th>Question</th>
                <th>Your Answer</th>
                <th>Correct Answer</th>
                <th>Status</th>
                <th>Marked for Review</th>
                <th>Problematic</th>
            </tr>

            <?php
            $count = 1;
            while ($row = $result_exam->fetch_assoc()):
                $question_text = isset($row['question']) ? $row['question'] : 'No question text';
                $selected_answer = isset($row['selected_answer']) ? $row['selected_answer'] : 'Not answered';
                $correct_answer = isset($row['correct_answer']) ? $row['correct_answer'] : 'No correct answer';
                $is_reviewed = isset($row['is_reviewed']) ? ($row['is_reviewed'] == 1 ? 'Yes' : 'No') : 'No';
                $is_problematic = isset($row['is_problematic']) ? ($row['is_problematic'] == 1 ? 'Yes' : 'No') : 'No';
            ?>
            <tr>
                <td><?= $count++ ?></td>
                <td><?= htmlspecialchars($question_text) ?></td>
                <td><?= htmlspecialchars($selected_answer) ?></td>
                <td><?= htmlspecialchars($correct_answer) ?></td>
                <td class="<?= ($selected_answer == $correct_answer ? 'status-correct' : 'status-incorrect') ?>"><?= $selected_answer == $correct_answer ? 'Correct' : 'Incorrect' ?></td>
                <td><?= $is_reviewed ?></td>
                <td><?= $is_problematic ?></td>
            </tr>
            <?php endwhile; ?>
        </table>

       
      
    <!-- Download Buttons -->
    <div class="btn-container">
    <a href="download_questions_csv.php?exam_id=<?= $exam_id ?>" class="btn-download">
        <img src="icons/csv.png" alt="CSV" style="height:20px; vertical-align:middle; margin-right:8px;">
        Download CSV
    </a>

    <a href="download_questions_excel.php?exam_id=<?= $exam_id ?>" class="btn-download">
        <img src="icons/excel.png" alt="Excel" style="height:20px; vertical-align:middle; margin-right:8px;">
        Download Excel
    </a>

    <a href="download_questions_pdf.php?exam_id=<?= $exam_id ?>" class="btn-download">
        <img src="icons/pdf.png" alt="PDF" style="height:20px; vertical-align:middle; margin-right:8px;">
        Download PDF
    </a>

    <a href="download_questions_word.php?exam_id=<?= $exam_id ?>" class="btn-download">
        <img src="icons/word.png" alt="Word" style="height:20px; vertical-align:middle; margin-right:8px;">
        Download Word
    </a>
</div>



        <!-- Buttons for Navigation -->
        <div class="btn-container">
            <a href="dashboard.php">Go to Dashboard</a>
            <a href="javascript:history.back()">Back to Previous Page</a>
        </div>
    </div>
</body>
</html>
