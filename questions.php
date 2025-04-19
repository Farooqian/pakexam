<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection file
include('db_connection.php');

// Check if the database connection is defined and active
if (!isset($conn) || !$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Ensure the student is logged in
if (!isset($_SESSION['registration_number'])) {
    header("Location: login.php");
    exit();
}

// Get the student's registration number from the session
$registration_number = $_SESSION['registration_number'];

// Get the type of questions to display from the query parameter
$type = isset($_GET['type']) ? $_GET['type'] : null;
if (!$type) {
    die("Error: No type specified.");
}

// Pagination settings
$questions_per_page = 15;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $questions_per_page;

// Define the query condition and page title based on the type of questions
$query_condition = "";
$title = "Question List"; // Default title
switch ($type) {
    case 'correct':
        $query_condition = "WHERE student_id = ? AND selected_answer = correct_answer";
        $title = "Correct Answers";
        break;
    case 'incorrect':
        $query_condition = "WHERE student_id = ? AND (selected_answer IS NULL OR selected_answer != correct_answer)";
        $title = "Incorrect Answers";
        break;
    case 'reviewed':
        $query_condition = "WHERE student_id = ? AND is_reviewed = 1";
        $title = "Marked for Review";
        break;
    case 'problematic':
        $query_condition = "WHERE student_id = ? AND is_problematic = 1";
        $title = "Flagged as Problematic";
        break;
    default:
        die("Error: Invalid type specified.");
}

// Fetch the questions based on the defined condition with LIMIT and OFFSET
$query = "SELECT * FROM exam_final $query_condition LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);

// Check if the statement preparation is successful
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

// Bind parameters and execute the query
$stmt->bind_param("sii", $registration_number, $questions_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Fetch all questions
$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}

// Count total questions for pagination
$count_query = "SELECT COUNT(*) as total FROM exam_final $query_condition";
$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param("s", $registration_number);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_questions = $count_result->fetch_assoc()['total'];

// Calculate total pages
$total_pages = ceil($total_questions / $questions_per_page);

// Close the statement and connection
$stmt->close();
$count_stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($title); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
            color: #333;
        }

        .container {
            max-width: 900px; /* Reduced the width for better readability */
            margin: 30px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #003366;
            margin-bottom: 20px;
            font-size: 1.8rem;
        }

        .question {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 10px; /* Reduced padding for smaller height */
    background: #ffffff;
    border-left: 6px solid #fd2929;
    margin-bottom: 10px; /* Reduced margin for tighter spacing */
    border-radius: 6px; /* Slightly smaller border radius */
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05); /* Subtle shadow */
    transition: all 0.3s ease;
    box-sizing: border-box;
    font-size: 0.9rem; /* Reduced font size for compact layout */
}


        .question:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .question .left {
    flex: 0 0 75%;
    padding: 10px; /* Reduced padding */
    background: #f0f9ff; /* Light Blue */
    border-radius: 6px; /* Slightly smaller border radius */
    color:rgb(33, 5, 103); /* Navy Blue */
    font-size: 0.9rem; /* Reduced font size */
    box-sizing: border-box;
}

.question .right {
    flex: 0 0 23%;
    padding: 10px; /* Reduced padding */
    background: #e8f5e9; /* Light Green */
    border-radius: 6px; /* Slightly smaller border radius */
    color: #1b5e20; /* Dark Green */
    text-align: left;
    font-size: 0.9rem; /* Reduced font size */
    box-sizing: border-box;
}

.question h3 {
    margin: 0 0 5px 0; /* Reduced margin for tighter spacing */
    font-size: 1rem; /* Reduced header font size */
}

.question p {
    margin: 3px 0 0 0; /* Reduced margin for compact layout */
    font-size: 0.9rem; /* Reduced font size */
}


        .pagination {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }

        .pagination a {
            text-decoration: none;
            padding: 10px 20px;
            margin: 0 5px;
            background: #003366;
            color: white;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .pagination a:hover {
            background: #002244;
        }

        .pagination .disabled {
            background: #ccc;
            color: #666;
            pointer-events: none;
        }

        .btn-back {
            display: block;
            margin: 20px auto;
            padding: 12px 25px;
            background: #0078d7; /* Blue button */
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-size: 1rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: background 0.3s ease;
            max-width: 300px; /* Button size */
        }

        .btn-back:hover {
            background: #005a9e; /* Darker blue on hover */
        }
    </style>
</head>
<body>

<div class="container">
    <h1><?php echo htmlspecialchars($title); ?></h1>

    <?php if (count($questions) > 0): ?>
        <?php foreach ($questions as $question): ?>
            <div class="question">
                <div class="left">
                    <h3>Question:</h3>
                    <p><?php echo htmlspecialchars($question['question'] ?? "Not Available"); ?></p>
                </div>
                <div class="right">
                    <p><strong>Your Answer:</strong> <?php echo htmlspecialchars($question['selected_answer'] ?? "Not Attemp"); ?></p>
                    <p><strong>Correct Answer:</strong> <?php echo htmlspecialchars($question['correct_answer'] ?? "Not Available"); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="no-questions">No questions found for this category.</p>
    <?php endif; ?>

    <!-- Pagination -->
    <div class="pagination">
        <?php if ($current_page > 1): ?>
            <a href="?type=<?php echo htmlspecialchars($type); ?>&page=<?php echo $current_page - 1; ?>">Previous</a>
        <?php else: ?>
            <a class="disabled">Previous</a>
        <?php endif; ?>

        <?php if ($current_page < $total_pages): ?>
            <a href="?type=<?php echo htmlspecialchars($type); ?>&page=<?php echo $current_page + 1; ?>">Next</a>
        <?php else: ?>
            <a class="disabled">Next</a>
        <?php endif; ?>
    </div>

    <!-- Go Back to Summary Button -->
    <a href="analysis.php" class="btn-back">Go Back to Summary</a>
</div>

</body>
</html>