<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'db_connection.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['organization']) || !isset($_GET['subject'])) {
    header("Location: confirm.php");
    exit();
}

$user_id = $_SESSION["user_id"];
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

$organization = htmlspecialchars($_GET['organization']);
$subject = htmlspecialchars($_GET['subject']);
$session_id = session_id();

// Check if the exam time is up
if (isset($_SESSION['exam_end_time']) && time() > $_SESSION['exam_end_time']) {
    // Clear exam session variables
    unset($_SESSION['questions']);
    unset($_SESSION['total_questions']);
    unset($_SESSION['exam_duration']);
    unset($_SESSION['answers']);
    unset($_SESSION['reviewedQuestions']);
    unset($_SESSION['problematicQuestions']);
    unset($_SESSION['exam_end_time']);
    // Temporarily comment out the alert
    // echo "<script>alert('Time\'s up! The exam will now end.'); window.location.href = 'confirm.php';</script>";
    header("Location: confirm.php"); // Redirect without alert
    exit();
}

// Fetch questions only if they are not already stored in the session
if (!isset($_SESSION['questions']) || !isset($_SESSION['total_questions']) || !isset($_SESSION['exam_duration'])) {
    $question_query = "SELECT id, question, option1, option2, option3, option4 FROM MCQs WHERE organization = ? AND subject = ? ORDER BY RAND() LIMIT 50";
    $stmt = $conn->prepare($question_query);
    $stmt->bind_param("ss", $organization, $subject);
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
    $_SESSION['exam_end_time'] = time() + ($exam_duration * 60); // Set exam end time
} else {
    $questions = $_SESSION['questions'];
    $total_questions = $_SESSION['total_questions'];
    $exam_duration = $_SESSION['exam_duration'];
}

// Initialize answers, reviewedQuestions, and problematicQuestions if not set
if (!isset($_SESSION['answers'])) {
    $_SESSION['answers'] = [];
}
if (!isset($_SESSION['reviewedQuestions'])) {
    $_SESSION['reviewedQuestions'] = [];
}
if (!isset($_SESSION['problematicQuestions'])) {
    $_SESSION['problematicQuestions'] = [];
}

$answers = $_SESSION['answers'];
$reviewedQuestions = $_SESSION['reviewedQuestions'];
$problematicQuestions = $_SESSION['problematicQuestions'];

// Debug information to check the current state of session variables
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam | ProjectD</title>
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
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .info-container {
            background: #f0f8ff;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .info-box {
            text-align: left;
            width: 45%;
        }

        .timer {
            font-size: 24px;
            background: #f0f8ff;
            padding: 10px;
            border-radius: 10px;
            color: #ff4500;
        }

        .progress {
            font-size: 24px;
            background: #f0f8ff;
            padding: 10px;
            border-radius: 10px;
            color: #32cd32;
        }

        .question-container {
            background: #f5fffa;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            width: 100%;
            margin-bottom: 20px;
        }

        .option-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .option-container div {
            width: 48%;
            background: #ffffff;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }

        .option-container div:hover {
            background: #e0ffff;
        }

        input[type="radio"]:checked + label {
            background: #87cefa;
        }

        .checkbox-container {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            font-size: 14px;
        }

        .navigation {
            margin-top: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
            text-align: center;
            font-size: 16px;
            margin: 5px;
            background: #1E90FF;
            color: white;
        }

        .btn:hover {
            background: #FF69B4;
        }

    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    window.addEventListener('beforeunload', function (e) {
        var confirmationMessage = 'Are you sure you want to leave? Your progress will be lost.';

        (e || window.event).returnValue = confirmationMessage;
        return confirmationMessage;
    });
    </script>
</head>
<body>

<div class="container">
    <div class="info-container">
        <div class="info-box">
            <p><strong>Name:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Student ID:</strong> <?= htmlspecialchars($user['registration_number']) ?></p>
        </div>
        <div class="info-box">
            <p><strong>Organization:</strong> <?= $organization ?></p>
            <p><strong>Subject:</strong> <?= $subject ?></p>
        </div>
        <div class="progress">Progress: <span id="progress">1/<?= $total_questions ?></span></div>
        <div class="timer" id="timer"></div>
    </div>

    <form id="exam-form" action="submit_exam.php" method="POST">
        <div class="question-container" id="question-box">
            <!-- Questions will be dynamically loaded here -->
        </div>

        <div class="navigation">
            <button type="button" class="btn" id="prev-btn" disabled>Previous Question</button>
            <button type="button" class="btn" id="next-btn">Next Question</button>
            <button type="submit" class="btn" id="end-btn" style="display:none;">End Exam</button>
        </div>

        <div class="navigation">
            <label for="question-nav">Go to Question:</label>
            <select id="question-nav">
                <?php for ($i = 1; $i <= $total_questions; $i++): ?>
                    <option value="<?= $i ?>">Q - <?= $i ?> - X</option>
                <?php endfor; ?>
            </select>
        </div>
    </form>
</div>

<script>
let questions = <?= json_encode($questions) ?>;
let currentQuestionIndex = 0;
let totalQuestions = <?= $total_questions ?>;
let examDuration = <?= $exam_duration ?> * 60; // in seconds
let timerInterval;
let answers = <?= json_encode($answers) ?>;
let reviewedQuestions = <?= json_encode($reviewedQuestions) ?>;
let problematicQuestions = <?= json_encode($problematicQuestions) ?>;

function loadQuestion(index) {
    let question = questions[index];
    let questionBox = document.getElementById('question-box');
    questionBox.innerHTML = `
        <h3>Question ${index + 1}</h3>
        <p>${question.question}</p>
        <div class="option-container">
            <div>
                <input type="radio" name="answer" value="1" id="option1" ${answers[question.id] == '1' ? 'checked' : ''}>
                <label for="option1">${question.option1}</label>
            </div>
            <div>
                <input type="radio" name="answer" value="2" id="option2" ${answers[question.id] == '2' ? 'checked' : ''}>
                <label for="option2">${question.option2}</label>
            </div>
            <div>
                <input type="radio" name="answer" value="3" id="option3" ${answers[question.id] == '3' ? 'checked' : ''}>
                <label for="option3">${question.option3}</label>
            </div>
            <div>
                <input type="radio" name="answer" value="4" id="option4" ${answers[question.id] == '4' ? 'checked' : ''}>
                <label for="option4">${question.option4}</label>
            </div>
        </div>
        <div class="checkbox-container">
            <label><input type="checkbox" name="review" ${reviewedQuestions.includes(question.id) ? 'checked' : ''}> Mark for Review</label>
            <label><input type="checkbox" name="problematic" ${problematicQuestions.includes(question.id) ? 'checked' : ''}> Mark as Problematic</label>
        </div>
    `;

    document.getElementById('progress').innerText = `${index + 1}/${totalQuestions}`;
    document.getElementById('prev-btn').disabled = (index === 0);
    document.getElementById('next-btn').style.display = (index === totalQuestions - 1) ? 'none' : 'inline-block';
    document.getElementById('end-btn').style.display = (index === totalQuestions - 1) ? 'inline-block' : 'none';
    updateQuestionNav();
}

function updateQuestionNav() {
    let questionNav = document.getElementById('question-nav');
    questionNav.innerHTML = '';
    for (let i = 1; i <= totalQuestions; i++) {
        let status = 'X';
        if (answers[questions[i - 1].id]) {
            status = 'O';
        }
        if (reviewedQuestions.includes(questions[i - 1].id)) {
            status += ' #';
        }
        let option = document.createElement('option');
        option.value = i;
        option.text = `Q - ${i} - ${status}`;
        questionNav.add(option);
    }
}

function saveState() {
    $.ajax({
        type: 'POST',
        url: 'save_state.php',
        data: {
            answers: JSON.stringify(answers),
            reviewedQuestions: JSON.stringify(reviewedQuestions),
            problematicQuestions: JSON.stringify(problematicQuestions)
        }
    });
}

function startTimer(duration) {
    let timer = localStorage.getItem('exam_timer') ? parseInt(localStorage.getItem('exam_timer'), 10) : duration;
    let minutes, seconds;
    timerInterval = setInterval(function () {
        minutes = parseInt(timer / 60, 10);
        seconds = parseInt(timer % 60, 10);

        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;

        document.getElementById('timer').textContent = minutes + ":" + seconds;

        if (--timer < 0) {
            clearInterval(timerInterval);
            localStorage.removeItem('exam_timer');
            alert("Time's up! The exam will now end.");
            document.getElementById('exam-form').submit();
        }

        if (timer < 300) { // less than 5 minutes
            document.getElementById('timer').style.color = 'red';
        }

        localStorage.setItem('exam_timer', timer);
    }, 1000);
}

document.getElementById('next-btn').addEventListener('click', function(event) {
    event.preventDefault();
    let question = questions[currentQuestionIndex];
    answers[question.id] = document.querySelector('input[name="answer"]:checked')?.value;
    if (document.querySelector('input[name="review"]').checked) {
        if (!reviewedQuestions.includes(question.id)) {
            reviewedQuestions.push(question.id);
        }
    } else {
        reviewedQuestions = reviewedQuestions.filter(id => id !== question.id);
    }
    if (document.querySelector('input[name="problematic"]').checked) {
        if (!problematicQuestions.includes(question.id)) {
            problematicQuestions.push(question.id);
        }
    } else {
        problematicQuestions = problematicQuestions.filter(id => id !== question.id);
    }
    saveState();
    if (currentQuestionIndex < totalQuestions - 1) {
        currentQuestionIndex++;
        loadQuestion(currentQuestionIndex);
    }
});

document.getElementById('prev-btn').addEventListener('click', function(event) {
    event.preventDefault();
    let question = questions[currentQuestionIndex];
    answers[question.id] = document.querySelector('input[name="answer"]:checked')?.value;
    if (document.querySelector('input[name="review"]').checked) {
        if (!reviewedQuestions.includes(question.id)) {
            reviewedQuestions.push(question.id);
        }
    } else {
        reviewedQuestions = reviewedQuestions.filter(id => id !== question.id);
    }
    if (document.querySelector('input[name="problematic"]').checked) {
        if (!problematicQuestions.includes(question.id)) {
            problematicQuestions.push(question.id);
        }
    } else {
        problematicQuestions = problematicQuestions.filter(id => id !== question.id);
    }
    saveState();
    if (currentQuestionIndex > 0) {
        currentQuestionIndex--;
        loadQuestion(currentQuestionIndex);
    }
});

document.getElementById('end-btn').addEventListener('click', function(event) {
    if (confirm("Are you sure you want to end the exam?")) {
        clearInterval(timerInterval);
        localStorage.removeItem('exam_timer');
        document.getElementById('exam-form').submit();
    }
});

document.getElementById('question-nav').addEventListener('change', function(event) {
    event.preventDefault();
    let question = questions[currentQuestionIndex];
    answers[question.id] = document.querySelector('input[name="answer"]:checked')?.value;
    if (document.querySelector('input[name="review"]').checked) {
        if (!reviewedQuestions.includes(question.id)) {
            reviewedQuestions.push(question.id);
        }
    } else {
        reviewedQuestions = reviewedQuestions.filter(id => id !== question.id);
    }
    if (document.querySelector('input[name="problematic"]').checked) {
        if (!problematicQuestions.includes(question.id)) {
            problematicQuestions.push(question.id);
        }
    } else {
        problematicQuestions = problematicQuestions.filter(id => id !== question.id);
    }
    saveState();
    let selectedQuestion = parseInt(this.value, 10) - 1;
    currentQuestionIndex = selectedQuestion;
    loadQuestion(currentQuestionIndex);
});

// Initialize the exam
loadQuestion(currentQuestionIndex);
startTimer(examDuration);
</script>

</body>
</html>