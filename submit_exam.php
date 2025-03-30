<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'db_connection.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$answers = $_POST['answers'];
$reviewed_questions = $_POST['reviewed_questions'];
$problematic_questions = $_POST['problematic_questions'];

// Save answers to the database
foreach ($answers as $question_id => $answer) {
    $reviewed = in_array($question_id, $reviewed_questions) ? 1 : 0;
    $problematic = in_array($question_id, $problematic_questions) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO student_answers (user_id, question_id, answer, reviewed, problematic) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisii", $user_id, $question_id, $answer, $reviewed, $problematic);
    $stmt->execute();
}

header("Location: exam_results.php");
exit();
?>
```

### Update `exam.php` to Handle Exam Submission

We need to update `exam.php` to submit the answers to `submit_exam.php` when the "End Exam" button is clicked.

````php name=exam.php
<?php
// Existing code...

// Fetch questions
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
        }

        .profile-box {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .timer {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 24px;
        }

        .roundel {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 24px;
        }

        .question-box {
            margin-top: 20px;
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
</head>
<body>

<div class="container">
    <div class="roundel">Progress: <span id="progress">1/<?= $total_questions ?></span></div>
    <div class="timer" id="timer"></div>
    <div class="profile-box">
        <p><strong>Name:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Student ID:</strong> <?= htmlspecialchars($user['registration_number']) ?></p>
        <p><strong>Organization:</strong> <?= $organization ?></p>
        <p><strong>Subject:</strong> <?= $subject ?></p>
        <p><strong>Session ID:</strong> <?= $session_id ?></p>
    </div>

    <form id="exam-form" action="submit_exam.php" method="POST">
        <div class="question-box" id="question-box">
            <!-- Questions will be dynamically loaded here -->
        </div>

        <div class="navigation">
            <button class="btn" id="prev-btn" disabled>Previous Question</button>
            <button class="btn" id="next-btn">Next Question</button>
            <button class="btn" id="end-btn" style="display:none;">End Exam</button>
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
let answers = {};
let reviewedQuestions = [];
let problematicQuestions = [];

function loadQuestion(index) {
    let question = questions[index];
    let questionBox = document.getElementById('question-box');
    questionBox.innerHTML = `
        <h3>Question ${index + 1}</h3>
        <p>${question.question}</p>
        <input type="radio" name="answer" value="1" ${answers[question.id] == '1' ? 'checked' : ''}> ${question.option1}<br>
        <input type="radio" name="answer" value="2" ${answers[question.id] == '2' ? 'checked' : ''}> ${question.option2}<br>
        <input type="radio" name="answer" value="3" ${answers[question.id] == '3' ? 'checked' : ''}> ${question.option3}<br>
        <input type="radio" name="answer" value="4" ${answers[question.id] == '4' ? 'checked' : ''}> ${question.option4}<br>
        <label><input type="checkbox" name="review" ${reviewedQuestions.includes(question.id) ? 'checked' : ''}> Mark for Review</label><br>
        <label><input type="checkbox" name="problematic" ${problematicQuestions.includes(question.id) ? 'checked' : ''}> Mark as Problematic</label>
    `;

    document.getElementById('progress').innerText = `${index + 1}/${totalQuestions}`;
    document.getElementById('prev-btn').disabled = (index === 0);
    document.getElementById('next-btn').style.display = (index === totalQuestions - 1) ? 'none' : 'inline-block';
    document.getElementById('end-btn').style.display = (index === totalQuestions - 1) ? 'inline-block' : 'none';
}

function startTimer(duration) {
    let timer = duration, minutes, seconds;
    timerInterval = setInterval(function () {
        minutes = parseInt(timer / 60, 10);
        seconds = parseInt(timer % 60, 10);

        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;

        document.getElementById('timer').textContent = minutes + ":" + seconds;

        if (--timer < 0) {
            clearInterval(timerInterval);
            alert("Time's up! The exam will now end.");
            document.getElementById('exam-form').submit();
        }

        if (timer < 300) { // less than 5 minutes
            document.getElementById('timer').style.color = 'red';
        }
    }, 1000);
}

document.getElementById('next-btn').addEventListener('click', function() {
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
    if (currentQuestionIndex < totalQuestions - 1) {
        currentQuestionIndex++;
        loadQuestion(currentQuestionIndex);
    }
});

document.getElementById('prev-btn').addEventListener('click', function() {
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
    if (currentQuestionIndex > 0) {
        currentQuestionIndex--;
        loadQuestion(currentQuestionIndex);
    }
});

document.getElementById('end-btn').addEventListener('click', function() {
    if (confirm("Are you sure you want to end the exam?")) {
        clearInterval(timerInterval);
        document.getElementById('exam-form').submit();
    }
});

document.getElementById('question-nav').addEventListener('change', function() {
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
```

### Explanation:

1. **Form Submission**:
   - The form is created around the question box and navigation buttons.
   - The form action is set to `submit_exam.php` with the POST method.

2. **JavaScript**:
   - The `answers`, `reviewedQuestions`, and `problematicQuestions` objects/arrays are used to store the student's responses and markings.
   - The `loadQuestion` function updates the state of the checkboxes based on the stored values.
   - The `next-btn` and `prev-btn` event listeners update the stored values when navigating between questions.
   - The `end-btn` event listener submits the form when the exam is ended.

### Next Steps:

- Implement the results page (`exam_results.php`) to display the student's performance.
- Test the entire workflow to ensure everything functions as expected.

If you have any specific requirements or encounter any issues, please let me know!