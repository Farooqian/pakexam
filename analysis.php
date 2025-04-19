<?php
// Check if session is already started before calling session_start()
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection file
include('db_connection.php');

// Ensure $conn is defined
if (!isset($conn) || !$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Check if the student is logged in
if (!isset($_SESSION['registration_number'])) { // Updated to use 'registration_number'
    header("Location: login.php");
    exit();
}

// Get the student's registration number from the session
$registration_number = $_SESSION['registration_number'];

// Fetch student details
$student_query = "SELECT full_name, email, phone FROM users WHERE registration_number = ?";
$student_stmt = $conn->prepare($student_query);
$student_stmt->bind_param("s", $registration_number);
$student_stmt->execute();
$student_result = $student_stmt->get_result();
$student_details = $student_result->fetch_assoc();

// Handle case where student details are not found
if (!$student_details) {
    die("Error: Student details not found.");
}

// Prepare the SQL query to fetch exam results for this student
$query = "SELECT * FROM exam_final WHERE student_id = ?";

// Prepare and bind
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $registration_number); // "s" for string type (since registration_number is varchar)
$stmt->execute();

// Get the results
$result = $stmt->get_result();

// Initialize counters for overall analysis
$total_questions = 0;
$correct_answers = 0;
$incorrect_answers = 0;
$reviewed_questions = 0;
$problematic_questions = 0;

// Initialize an array to store organization and subject-wise analysis
$org_subject_analysis = [];
$org_subjects = [];
$correct_counts = [];
$incorrect_counts = [];
$reviewed_counts = [];
$problematic_counts = [];

// Process each exam result
while ($row = $result->fetch_assoc()) {
    $total_questions++;
    $organization = $row['organization'];
    $subject = $row['subject'];
    $org_subject_key = $organization . ' - ' . $subject;

    // Initialize organization and subject analysis if not already set
    if (!isset($org_subject_analysis[$org_subject_key])) {
        $org_subject_analysis[$org_subject_key] = [
            'total_questions' => 0,
            'correct_answers' => 0,
            'incorrect_answers' => 0,
            'reviewed_questions' => 0,
            'problematic_questions' => 0
        ];
        $org_subjects[] = $org_subject_key;  // Add organization-subject to list
    }

    // Update organization and subject-wise counters
    $org_subject_analysis[$org_subject_key]['total_questions']++;
    if ($row['selected_answer'] == $row['correct_answer']) {
        $org_subject_analysis[$org_subject_key]['correct_answers']++;
    } else {
        $org_subject_analysis[$org_subject_key]['incorrect_answers']++;
    }

    if ($row['is_reviewed'] == 1) {
        $org_subject_analysis[$org_subject_key]['reviewed_questions']++;
    }

    if ($row['is_problematic'] == 1) {
        $org_subject_analysis[$org_subject_key]['problematic_questions']++;
    }

    // Overall analysis
    if ($row['selected_answer'] == $row['correct_answer']) {
        $correct_answers++;
    } else {
        $incorrect_answers++;
    }
    if ($row['is_reviewed'] == 1) {
        $reviewed_questions++;
    }
    if ($row['is_problematic'] == 1) {
        $problematic_questions++;
    }
}

// Calculate the overall performance percentage
$performance = 0;
if ($total_questions > 0) {
    $performance = ($correct_answers / $total_questions) * 100;
}

// Prepare data for charts
foreach ($org_subject_analysis as $org_subject => $data) {
    $correct_counts[] = $data['correct_answers'];
    $incorrect_counts[] = $data['incorrect_answers'];
    $reviewed_counts[] = $data['reviewed_questions'];
    $problematic_counts[] = $data['problematic_questions'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Performance Summary</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Info</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background:rgb(244, 15, 15);
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background:rgb(201, 243, 159);
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #003366;
            margin-bottom: 20px;
        }

        .student-info {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .info-item {
            padding: 15px;
            border-radius: 8px;
            color:rgb(255, 255, 255);
            font-size: 5;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-left: 6px solid #ffffff;
            border-right: 6px  solid #ffffff;
            background: linear-gradient(to right,rgb(134, 80, 165),rgb(198, 6, 219));
        }

        .info-item.name {
            border-left: 6px solid #ffffff;
            border-right: 6px  solid #ffffff;
            background: linear-gradient(to right,rgb(134, 80, 165),rgb(198, 6, 219));
        }

        .info-item.age {
            background: #2196f3; /* Blue */
        }

        .info-item.grade {
            background: #ff9800; /* Orange */
        }

        .info-item.school {
            background: #9c27b0; /* Purple */
        }

        .info-item.contact {
            background: #f44336; /* Red */
        }
    </style>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background: #f7f9fc;
            color: #333;
        }

        .student-details {
            margin: 20px auto;
            padding: 20px;
            background: linear-gradient(to right,rgb(118, 164, 124),rgb(6, 167, 12));
            border-radius: 10px;
            border-left: 6px solid   #560beb;
            border-right: 6px  solid   #560beb;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            color: #555;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .student-details p {
            margin: 5px 0;
            font-size: 16px;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .summary-box {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            flex: 1 1 200px;
            padding: 15px;
            border-radius: 12px;
            background: linear-gradient(to right,rgb(221, 254, 100),rgb(232, 247, 154));
            border-left: 6px solid #4facfe;
            border-right: 6px solid #4facfe;
            transition: 0.3s ease;
            cursor: pointer;
            text-align: center;
        }

        .card:hover {
            background: #d9f2ff;
        }

        .card h3 {
            margin: 0 0 10px;
            color: #0078d7;
        }

        .card p {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }

        .chart-container {
            margin-top: 30px;
        }

        h2 {
            text-align: center;
            color: #0078d7;
        }

        canvas {
            max-height: 300px;
        }

        .btn {
            margin-top: 30px;
            padding: 12px 25px;
            font-size: 16px;
            background: #4facfe;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background: #0078d7;
        }
        
    </style>
    <script>
        function redirectToQuestions(type) {
            window.location.href = `questions.php?type=${type}`;
        }
    </script>
</head>
<body>

<div class="student-details">
    <div class="info-item name">
        <p><strong>Student Name:</strong> <?php echo htmlspecialchars($student_details['full_name']); ?></p>
    </div>
    <div class="info-item email">
        <p><strong>Email:</strong> <?php echo htmlspecialchars($student_details['email']); ?></p>
    </div>
    <div class="info-item phone">
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($student_details['phone']); ?></p>
    </div>
    <div class="info-item reg-number">
        <p><strong>Registration Number:</strong> <?php echo htmlspecialchars($registration_number); ?></p>
    </div>
</div>

<div class="container">

    <div class="summary-box">
        <div class="card">
            <h3>Total Questions</h3>
            <p><?php echo $total_questions; ?></p>
        </div>
        <div class="card" onclick="redirectToQuestions('correct')">
            <h3>Correct Answers</h3>
            <p><?php echo $correct_answers; ?></p>
        </div>
        <div class="card" onclick="redirectToQuestions('incorrect')">
            <h3>Incorrect Answers</h3>
            <p><?php echo $incorrect_answers; ?></p>
        </div>
        <div class="card">
            <h3>Performance</h3>
            <p><?php echo number_format($performance, 2); ?>%</p>
        </div>
        <div class="card" onclick="redirectToQuestions('reviewed')">
            <h3>Marked for Review</h3>
            <p><?php echo $reviewed_questions; ?></p>
        </div>
        <div class="card" onclick="redirectToQuestions('problematic')">
            <h3>Flagged as Problematic</h3>
            <p><?php echo $problematic_questions; ?></p>
        </div>
    </div>

    <div class="chart-container">
        <h2>Organization and Subject-wise Performance</h2>
        <canvas id="orgSubjectChart"></canvas>
    </div>

    <button class="btn" onclick="window.location.href='dashboard.php';">Go to Dashboard</button>
</div>

<script>
    var ctx = document.getElementById('orgSubjectChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($org_subjects); ?>,
            datasets: [{
                label: 'Correct Answers',
                data: <?php echo json_encode($correct_counts); ?>,
                backgroundColor: '#4caf50'
            }, {
                label: 'Incorrect Answers',
                data: <?php echo json_encode($incorrect_counts); ?>,
                backgroundColor: '#e53935'
            }, {
                label: 'Marked for Review',
                data: <?php echo json_encode($reviewed_counts); ?>,
                backgroundColor: '#ff9800'
            }, {
                label: 'Flagged as Problematic',
                data: <?php echo json_encode($problematic_counts); ?>,
                backgroundColor: '#9c27b0'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

</body>
</html>