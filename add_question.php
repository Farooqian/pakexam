<?php
// Start the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch the role from the session
include 'db_connection.php';

$query = "SELECT role FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check if the user is an admin
if ($user['role'] !== 'admin') {
    // Redirect if the logged-in user is not an admin
    header("Location: login.php");
    exit();
}

$message = ''; // Initialize the message variable

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $organization = trim($_POST['organization']);
    $subject = trim($_POST['subject']);
    $question = trim($_POST['question']);
    $a = trim($_POST['option_a']);
    $b = trim($_POST['option_b']);
    $c = trim($_POST['option_c']);
    $d = trim($_POST['option_d']);
    $correct = trim($_POST['correct_answer']);

    // Handle the case where "Add New Organization" or "Add New Subject" is selected
    if ($organization == 'new') {
        $organization = trim($_POST['new_organization']);  // Get new organization value
    }
    if ($subject == 'new') {
        $subject = trim($_POST['new_subject']);  // Get new subject value
    }

    // Insert new organization or subject into the 'mcqs' table if needed
    if (!empty($organization) && !empty($subject)) {
        // Insert the organization into the database if it's a new organization
        if ($organization == 'new' && !empty($organization)) {
            $insert_org_query = "INSERT INTO mcqs (organization) VALUES (?)";
            $insert_org_stmt = $conn->prepare($insert_org_query);
            $insert_org_stmt->bind_param("s", $organization);
            $insert_org_stmt->execute();
        }

        // Insert the subject into the database if it's a new subject
        if ($subject == 'new' && !empty($subject)) {
            $insert_subj_query = "INSERT INTO mcqs (subject) VALUES (?)";
            $insert_subj_stmt = $conn->prepare($insert_subj_query);
            $insert_subj_stmt->bind_param("s", $subject);
            $insert_subj_stmt->execute();
        }

        // Now insert the new question into the database
        $stmt = $conn->prepare("INSERT INTO MCQS (organization, subject, question, option1, option2, option3, option4, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $organization, $subject, $question, $a, $b, $c, $d, $correct);

        if ($stmt->execute()) {
            $message = "Question added successfully!";
        } else {
            $message = "Error executing query: " . $stmt->error;
        }
    } else {
        $message = "Please fill all fields correctly.";
    }
}

// Fetch all organizations and subjects to populate the dropdowns
$org_query = "SELECT DISTINCT organization FROM mcqs";
$org_result = $conn->query($org_query);

$subj_query = "SELECT DISTINCT subject FROM mcqs";
$subj_result = $conn->query($subj_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Question | Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef;
            padding: 20px;
        }

        .form-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            margin: auto;
        }

        h2 {
            text-align: center;
        }

        label {
            font-weight: bold;
        }

        input[type=text], textarea, select {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #aaa;
        }

        button {
            background-color: #1E90FF;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background-color: #0066cc;
        }

        .message {
            text-align: center;
            color: green;
            font-weight: bold;
            margin-top: 10px;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Add New Question</h2>

    <?php if ($message): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Organization</label>
        <select name="organization" required onchange="toggleNewOrgField(this)">
            <option value="">--Select Organization--</option>
            <?php while ($row = $org_result->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($row['organization']) ?>"><?= htmlspecialchars($row['organization']) ?></option>
            <?php endwhile; ?>
            <option value="new">Add New Organization</option>
        </select>
        <div id="newOrganizationDiv" style="display:none;">
            <label>New Organization</label>
            <input type="text" name="new_organization" placeholder="Enter new organization name">
        </div>

        <label>Subject</label>
        <select name="subject" required onchange="toggleNewSubjectField(this)">
            <option value="">--Select Subject--</option>
            <?php while ($row = $subj_result->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($row['subject']) ?>"><?= htmlspecialchars($row['subject']) ?></option>
            <?php endwhile; ?>
            <option value="new">Add New Subject</option>
        </select>
        <div id="newSubjectDiv" style="display:none;">
            <label>New Subject</label>
            <input type="text" name="new_subject" placeholder="Enter new subject name">
        </div>

        <label>Question</label>
        <textarea name="question" required></textarea>

        <label>Option A</label>
        <input type="text" name="option_a" required>

        <label>Option B</label>
        <input type="text" name="option_b" required>

        <label>Option C</label>
        <input type="text" name="option_c" required>

        <label>Option D</label>
        <input type="text" name="option_d" required>

        <label>Correct Answer</label>
        <select name="correct_answer" required>
            <option value="">--Select--</option>
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="C">C</option>
            <option value="D">D</option>
        </select>

        <button type="submit">Add Question</button>
    </form>
    <!-- Go to Dashboard Button -->
    <form action="dashboard.php" method="get">
        <button type="submit" style="background-color: #4CAF50; margin-top: 20px;">Go to Dashboard</button>
            </form>
    </div>

<script>
    function toggleNewOrgField(select) {
        if (select.value == 'new') {
            document.getElementById('newOrganizationDiv').style.display = 'block';
        } else {
            document.getElementById('newOrganizationDiv').style.display = 'none';
        }
    }

    function toggleNewSubjectField(select) {
        if (select.value == 'new') {
            document.getElementById('newSubjectDiv').style.display = 'block';
        } else {
            document.getElementById('newSubjectDiv').style.display = 'none';
        }
    }
</script>

</body>
</html>
