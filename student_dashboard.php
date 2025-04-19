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
$query = "SELECT full_name, email, registration_number, role FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User not found!";
    exit();
}

$role = $user['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | ProjectD</title>
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

        .btn-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
    display: flex;
    align-items: center; /* Centers text vertically */
    justify-content: center; /* Centers text horizontally */
    padding: 6px 12px; /* Reduce padding for a sleeker look */
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    transition: 0.3s;
    text-align: center;
    font-size: 13px; /* Reduce font size slightly */
    height: 36px; /* Reduce height */
    white-space: nowrap; /* Prevent text wrapping */
}


        .btn-primary { background: #1E90FF; color: white; }
        .btn-primary:hover { background:rgb(184, 11, 200); }

        .btn-danger { background: red; color: white; }
        .btn-danger:hover { background:rgb(10, 87, 6); }
    </style>
</head>
<body>

<div class="container">
    <h2>Welcome, <?= htmlspecialchars($user['full_name']) ?>!</h2>

    <div class="profile-box">
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Registration ID:</strong> <?= htmlspecialchars($user['registration_number']) ?></p>
        <p><strong>Role:</strong> <?= htmlspecialchars(ucfirst($role)) ?></p>
    </div>

    <?php if ($role == "student") : ?>
        <div class="btn-container">
            <a href="select.php" class="btn btn-primary">Start Exam</a>
            <a href="s_results.php" class="btn btn-primary">Results</a>
            <a href="analysis.php" class="btn btn-primary">Analysis</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    <?php endif; ?>

    <?php if ($role == "admin") : ?>
        <div class="btn-container">
            <a href="approve_users.php" class="btn btn-primary">Approve Users</a>
            <a href="add_question.php" class="btn btn-primary">Add Question</a>
            <a href="upload_mcq_page.php" class="btn btn-primary">Upload</a>
            <a href="online_users.php" class="btn btn-primary">Online Users</a>
            <a href="active_exams.php" class="btn btn-primary">Active Exams</a>
            <a href="exam_history.php" class="btn btn-primary">History</a>
            <a href="results_admin.php" class="btn btn-primary">All Results</a>
            <a href="top_students.php" class="btn btn-primary">Top Students</a>
            <a href="subject_analysis_admin.php" class="btn btn-primary">Analysis</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>