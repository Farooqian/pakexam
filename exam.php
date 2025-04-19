<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ensure the user is logged in and session variables are set
if (!isset($_SESSION['user_id']) || !isset($_SESSION['exam_id'])) {
    header("Location: login.php");
    exit();
}

// Retrieve exam ID and initialize current question index
$exam_id = $_SESSION['exam_id'];
if (!isset($_SESSION['current_question_index'])) {
    $_SESSION['current_question_index'] = 0; // Start from the first question
}

$current_index = $_SESSION['current_question_index'];

// Retrieve student and exam info from session
$full_name = htmlspecialchars($_SESSION['full_name']);
$email = htmlspecialchars($_SESSION['email']);
$student_id = htmlspecialchars($_SESSION['student_id']);
$organization = htmlspecialchars($_SESSION['organization']);
$subject = htmlspecialchars($_SESSION['subject']);

// Get the detected timezone from POST (if submitted) or default to UTC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['timezone'])) {
    $_SESSION['timezone'] = $_POST['timezone']; // Store the detected timezone in the session
}

$student_timezone = $_SESSION['timezone'] ?? 'UTC'; // Use UTC if timezone is not set

// Fetch questions from the database
include 'db_connection.php';
$query = "SELECT question_id, question, option1, option2, option3, option4, selected_answer, is_reviewed, is_problematic, start_time, end_time 
          FROM exam_progress 
          WHERE exam_id = ? 
          ORDER BY question_id ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $exam_id);
$stmt->execute();
$result = $stmt->get_result();

$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}

$total_questions = count($questions);

// Ensure the current index is within valid bounds
if ($current_index < 0) {
    $current_index = 0;
} elseif ($current_index >= $total_questions) {
    $current_index = $total_questions - 1;
}

// Handle navigation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['go_to_question'])) {
        $selected_question = (int)$_POST['go_to_question'];
        if ($selected_question >= 0 && $selected_question < $total_questions) {
            $current_index = $selected_question;
        }
    } elseif (isset($_POST['previous']) && $current_index > 0) {
        $current_index--;
    } elseif (isset($_POST['next']) && $current_index < $total_questions - 1) {
        $current_index++;
    } elseif (isset($_POST['end_exam'])) {
        header("Location: end_exam.php"); // Redirect to exam submission
        exit();
    }

    // Update the session with the new current index
    $_SESSION['current_question_index'] = $current_index;
}

// Get the current question
$current_question = $questions[$current_index];

// Convert UTC times to local timezone
function convertToLocalTime($utc_time, $timezone) {
    try {
        $date = new DateTime($utc_time, new DateTimeZone('UTC')); // Create DateTime object with UTC timezone
        $date->setTimezone(new DateTimeZone($timezone)); // Convert to student's local timezone
        return $date->format('Y-m-d H:i:s'); // Format the date
    } catch (Exception $e) {
        return $utc_time; // Return original UTC time if conversion fails
    }
}

// Calculate remaining time
function calculateRemainingTime($end_time, $timezone) {
    try {
        $current_time = new DateTime('now', new DateTimeZone($timezone)); // Get the current time in the student's local timezone
        $end_time_local = new DateTime($end_time, new DateTimeZone('UTC')); // Convert end_time to UTC
        $end_time_local->setTimezone(new DateTimeZone($timezone)); // Convert end_time to local timezone

        if ($end_time_local > $current_time) {
            return $end_time_local->getTimestamp() - $current_time->getTimestamp(); // Return the remaining time in seconds
        }
        return 0; // No remaining time
    } catch (Exception $e) {
        return 0; // Return 0 if calculation fails
    }
}

$start_time_local = convertToLocalTime($current_question['start_time'], $student_timezone);
$end_time_local = convertToLocalTime($current_question['end_time'], $student_timezone);
$remaining_time_seconds = calculateRemainingTime($current_question['end_time'], $student_timezone);

// Generate dropdown options with question statuses
function generateQuestionDropdown($questions, $current_index) {
    $dropdown_html = "";
    foreach ($questions as $index => $question) {
        $status = "";
        $selected = $index === $current_index ? "selected" : "";

        // Determine the status of the question
        if (empty($question['selected_answer'])) {
            $status = "X"; // Unattempted
        } else {
            $status = "O"; // Attempted
        }

        if ($question['is_reviewed']) {
            $status .= " - #"; // Marked for review
        }

        $dropdown_html .= "<option value='{$index}' {$selected}>Q " . ($index + 1) . " - {$status}</option>";
    }
    return $dropdown_html;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Page | ProjectD</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
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
            max-width: 1000px;
            margin: 50px auto;
        }
        .info-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .info-box {
            width: 30%;
            background: #eaf4fc;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        .info-box h3 {
            margin-bottom: 10px;
            color: #0077cc;
        }
        .timer {
            font-size: 1.5em;
            color: #d9534f;
            font-weight: bold;
        }
        .question-box {
            background: #fdfdfd;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        .options-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }
        .option {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease, color 0.3s ease;
            text-align: center;
            font-weight: bold;
            border: 2px solid transparent;
        }
        .option:hover {
            background: #e0f7fa;
            color: #00796b;
        }
        .option.selected {
            background: #b2dfdb;
            color: #004d40;
            border-color: #00796b;
        }
        .checkbox-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .checkbox-container label {
            font-weight: bold;
            color: #555;
        }
        .navigation-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 15px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
            font-size: 16px;
            background: #0077cc;
            color: white;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: #005fa3;
        }
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .dropdown-container select {
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 200px;
            height: 40px;
            text-align: center;
            cursor: pointer;
            background: #f0f0f0;
        }
        .dropdown-container select:hover {
            border-color: #0077cc;
            background: #eaf4fc;
        }
    </style>
    <script>
        // Initialize countdown timer
        let remainingTime = <?= $remaining_time_seconds ?>; // from PHP
// Calculate the absolute end timestamp in milliseconds
const endTime = new Date().getTime() + remainingTime * 1000;

        function startTimer() {
    const timerElement = document.getElementById('timer');
    if (!timerElement) return;

    const interval = setInterval(() => {
        // Get current time in milliseconds
        const now = new Date().getTime();
        // Calculate the difference in seconds
        let timeLeft = Math.floor((endTime - now) / 1000);
        
        // If time is up, clear the interval and handle expiration
        if (timeLeft <= 0) {
            clearInterval(interval);
            timerElement.textContent = "Time's up!";
            // Optionally perform a redirection or execute a function
            window.location.href = "end_exam.php";
            return;
        }
        
        // Convert seconds to HH:MM:SS
        const hours = Math.floor(timeLeft / 3600);
        const minutes = Math.floor((timeLeft % 3600) / 60);
        const seconds = timeLeft % 60;
        timerElement.textContent = 
            `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }, 1000); // Update every second
}

document.addEventListener('DOMContentLoaded', startTimer);

        document.addEventListener('DOMContentLoaded', startTimer);

        // Save selected answer
        function saveAnswer(questionId, selectedOption) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "update_answer.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.querySelectorAll(".option").forEach(option => option.classList.remove("selected"));
                    document.getElementById("option-" + selectedOption).classList.add("selected");
                }
            };
            xhr.send(`question_id=${questionId}&selected_answer=${selectedOption}`);
        }

        // Update checkbox state
        function updateCheckbox(questionId, columnName, isChecked) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "update_question_flags.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {};
            xhr.send(`question_id=${questionId}&column_name=${columnName}&is_checked=${isChecked ? 1 : 0}`);
        }
    </script>
</head>
<body>

<div class="container">
    <!-- Info Section -->
    <div class="info-container">
        <div class="info-box">
            <h3>Student Information</h3>
            <p><strong>Name:</strong> <?= $full_name ?></p>
            <p><strong>Email:</strong> <?= $email ?></p>
            <p><strong>ID:</strong> <?= $student_id ?></p>
        </div>
        <div class="info-box">
            <h3>Exam Details</h3>
            <p><strong>Exam ID:</strong> <?= $exam_id ?></p>
            <p><strong>Subject:</strong> <?= $subject ?></p>
            <p><strong>Subject:</strong> <?= $organization ?></p>
        </div>
        <div class="info-box">
            <h3>Timing Information</h3>
            <p><strong>Start Time:</strong> <?= $start_time_local ?> UTC </p>
            <p><strong>End Time:</strong> <?= $end_time_local ?> UTC </p>
            <p><strong>Remaining Time:</strong> <span id="timer" class="timer"></span></p>
        </div>
    </div>

    <!-- Question Section -->
    <div class="question-box">
        <h2>Question <?= $current_index + 1 ?> of <?= $total_questions ?></h2>
        <p><?= htmlspecialchars($current_question['question']) ?></p>
        <div class="options-container">
            <?php foreach (['A', 'B', 'C', 'D'] as $index => $option): ?>
                <div id="option-<?= $option ?>" class="option <?= $current_question['selected_answer'] === $option ? 'selected' : '' ?>"
                     onclick="saveAnswer(<?= $current_question['question_id'] ?>, '<?= $option ?>')">
                    <?= htmlspecialchars($current_question['option' . ($index + 1)]) ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="checkbox-container">
            <label>
                <input type="checkbox" <?= $current_question['is_reviewed'] ? 'checked' : '' ?>
                       onchange="updateCheckbox(<?= $current_question['question_id'] ?>, 'is_reviewed', this.checked)">
                Mark for Review
            </label>
            <label>
                <input type="checkbox" <?= $current_question['is_problematic'] ? 'checked' : '' ?>
                       onchange="updateCheckbox(<?= $current_question['question_id'] ?>, 'is_problematic', this.checked)">
                Mark as Problematic
            </label>
        </div>
    </div>

    <!-- Navigation Buttons and Dropdown -->
    <div class="navigation-container">
    <!-- Previous Button -->
    <form method="POST">
        <button type="submit" name="previous" class="btn" <?= $current_index === 0 ? 'disabled' : '' ?>>Previous</button>
    </form>

    <!-- Dropdown -->
    <form method="POST" class="dropdown-container">
        <select name="go_to_question" onchange="this.form.submit()">
            <?= generateQuestionDropdown($questions, $current_index) ?>
        </select>
    </form>

    <!-- Next Button and End Exam Button -->
    <form method="POST">
        <!-- Disable Next button if on the last question -->
        <button type="submit" name="next" class="btn" <?= $current_index === $total_questions - 1 ? 'disabled' : '' ?>>Next</button>

        <!-- Show End Exam button only on the last question -->
        <?php if ($current_index === $total_questions - 1): ?>
            <button type="submit" name="end_exam" class="btn">End Exam</button>
        <?php endif; ?>
    </form>
</div>
<script>
let endTime = Date.now() + (<?= $remaining_time_seconds ?> * 1000);

function startTimer() {
    const timerElement = document.getElementById('timer');

    function updateTimer() {
        let now = Date.now();
        let diff = endTime - now;

        if (diff <= 0) {
            timerElement.textContent = "Time's up!";
            window.location.href = "end_exam.php"; // or trigger form submit
            return;
        }

        let totalSeconds = Math.floor(diff / 1000);
        let hours = Math.floor(totalSeconds / 3600);
        let minutes = Math.floor((totalSeconds % 3600) / 60);
        let seconds = totalSeconds % 60;

        timerElement.textContent = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

        requestAnimationFrame(updateTimer);
    }

    requestAnimationFrame(updateTimer);
}

document.addEventListener('DOMContentLoaded', startTimer);
</script>
</body>
</html>

</body>
</html>