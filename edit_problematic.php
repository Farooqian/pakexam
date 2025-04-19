<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Restrict access to only admins
if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== 'admin') {
    $_SESSION['error'] = "Access denied. Admins only.";
    header("Location: login.php");
    exit();
}

include 'db_connection.php';

// Fetch problematic questions excluding those with problem_resolved = 1
$query = "SELECT * FROM exam_final WHERE is_problematic = 1 AND (problem_resolved IS NULL OR problem_resolved != 1)";
$result = $conn->query($query);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $question_id = $_POST['question_id'];
    $updated_question = $_POST['updated_question'];
    $updated_option1 = $_POST['updated_option1'];
    $updated_option2 = $_POST['updated_option2'];
    $updated_option3 = $_POST['updated_option3'];
    $updated_option4 = $_POST['updated_option4'];
    $updated_answer = $_POST['updated_answer'];

    $update_query = "
        UPDATE exam_final
        SET question = ?, option1 = ?, option2 = ?, option3 = ?, option4 = ?, correct_answer = ?, problem_resolved = 1
        WHERE question_id = ?
    ";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssssssi", $updated_question, $updated_option1, $updated_option2, $updated_option3, $updated_option4, $updated_answer, $question_id);
    if ($stmt->execute()) {
        echo "<script>alert('Question updated and marked as resolved.'); window.location.href='edit_problematic.php';</script>";
    } else {
        echo "<script>alert('Error updating question. Please try again.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Problematic Questions</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,700">
    <style>
        body {
            font-family: Roboto, Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            margin: 40px auto;
        }

        h1 {
            text-align: center;
            color: #007bff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        .action-buttons a {
            background-color: #28a745;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
        }

        .action-buttons a:hover {
            background-color: #218838;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.4);
            overflow: auto;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 600px;
            max-width: 95%;
        }

        .modal-header {
            background-color: #007bff;
            color: white;
            padding: 10px;
            font-size: 20px;
        }

        .modal-body textarea,
        .modal-body input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            box-sizing: border-box;
        }

        .modal-footer {
            text-align: right;
            margin-top: 20px;
        }

        .modal-footer input[type="submit"] {
            background-color: #007bff;
            border: none;
            color: white;
            padding: 10px 18px;
            border-radius: 5px;
            cursor: pointer;
        }

        .close {
            float: right;
            font-size: 28px;
            cursor: pointer;
        }

        .close:hover {
            color: red;
        }
        .modal-content {
    background-color: #fff;
    margin: 5% auto;
    padding: 20px;
    border-radius: 8px;
    width: 600px;
    max-width: 95%;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    border: 2px solid #007bff;
}

.modal-header {
    background-color: #007bff;
    color: white;
    padding: 15px;
    font-size: 22px;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-body label {
    font-weight: bold;
    margin-top: 10px;
    display: block;
    color: #333;
}

.modal-footer {
    text-align: right;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #ccc;
}

.modal-footer input[type="submit"],
.modal-footer button {
    background-color: #007bff;
    border: none;
    color: white;
    padding: 10px 18px;
    border-radius: 5px;
    cursor: pointer;
}

.modal-footer button {
    background-color: #6c757d;
    margin-left: 10px;
}

.option-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}


    </style>
</head>
<body>

<div class="container">
    <h1>Edit Problematic Questions</h1>
    <table>
        <thead>
            <tr>
                <th>Question ID</th>
                <th>Question</th>
                <th>Options</th>
                <th>Correct Answer</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['question_id']) ?></td>
                <td><?= htmlspecialchars($row['question']) ?></td>
                <td>
                    1. <?= htmlspecialchars($row['option1']) ?><br>
                    2. <?= htmlspecialchars($row['option2']) ?><br>
                    3. <?= htmlspecialchars($row['option3']) ?><br>
                    4. <?= htmlspecialchars($row['option4']) ?>
                </td>
                <td><?= htmlspecialchars($row['correct_answer']) ?></td>
                <td><?= $row['problem_resolved'] == 1 ? '<span style="color: green;">Resolved</span>' : '<span style="color: red;">Problematic</span>' ?></td>
                <td class="action-buttons">
                    <a href="#" onclick='editQuestion(<?= json_encode($row) ?>)'>Edit</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal Form -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close" onclick="closeModal()">&times;</span>
            Edit Question
        </div>
        <div class="modal-body">
            <form method="POST" id="editForm">
                <input type="hidden" name="question_id" id="question_id">
                <label>Question:</label>
                <textarea name="updated_question" id="updated_question" required></textarea>

                <div class="option-grid">
    <div>
        <label>Option 1:</label>
        <input type="text" name="updated_option1" id="updated_option1" required>
    </div>
    <div>
        <label>Option 2:</label>
        <input type="text" name="updated_option2" id="updated_option2" required>
    </div>
    <div>
        <label>Option 3:</label>
        <input type="text" name="updated_option3" id="updated_option3" required>
    </div>
    <div>
        <label>Option 4:</label>
        <input type="text" name="updated_option4" id="updated_option4" required>
    </div>
</div>


                <label>Correct Answer:</label>
                <input type="text" name="updated_answer" id="updated_answer" required>

                <div class="modal-footer">
    <input type="submit" name="update" value="Update Question">
    <button type="button" onclick="closeModal()" style="background-color: #6c757d; color: white; padding: 10px 18px; border: none; border-radius: 5px; margin-left: 10px; cursor: pointer;">
        Close
    </button>
</div>

            </form>
        </div>
    </div>
</div>

<script>
    function editQuestion(data) {
        document.getElementById("question_id").value = data.question_id;
        document.getElementById("updated_question").value = data.question;
        document.getElementById("updated_option1").value = data.option1;
        document.getElementById("updated_option2").value = data.option2;
        document.getElementById("updated_option3").value = data.option3;
        document.getElementById("updated_option4").value = data.option4;
        document.getElementById("updated_answer").value = data.correct_answer;

        document.getElementById("editModal").style.display = "block";
    }

    function closeModal() {
        document.getElementById("editModal").style.display = "none";
    }

    
</script>
<div style="margin-bottom: 20px;">
    <a href="admin_dashboard.php" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;">Dashboard</a>
    <a href="javascript:history.back()" style="background-color: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Previous Page</a>
</div>

</body>
</html>
