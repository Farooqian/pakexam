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

// Handle user approval or rejection
if (isset($_GET['approve'])) {
    $user_id_to_approve = $_GET['approve'];
    $update_query = "UPDATE users SET status = 'approved' WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $user_id_to_approve);
    $update_stmt->execute();
    header("Location: approve_users.php"); // Redirect to refresh the page
    exit();
} elseif (isset($_GET['reject'])) {
    $user_id_to_reject = $_GET['reject'];
    $update_query = "UPDATE users SET status = 'rejected' WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $user_id_to_reject);
    $update_stmt->execute();
    header("Location: approve_users.php"); // Redirect to refresh the page
    exit();
}

// Fetch pending users to approve/reject
$pending_users_query = "SELECT id, full_name, email, registration_number, role FROM users WHERE status = 'pending'";
$pending_users_result = $conn->query($pending_users_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Users | Admin Dashboard</title>
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

        .btn-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
            text-align: center;
            font-size: 13px;
            height: 36px;
        }

        .btn-primary { background: #1E90FF; color: white; }
        .btn-primary:hover { background:rgb(184, 11, 200); }

        .btn-danger { background: red; color: white; }
        .btn-danger:hover { background:rgb(10, 87, 6); }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #1E90FF;
            color: white;
        }

        .highlight-admin {
            background-color:rgb(8, 211, 116); /* Highlighting admin rows with a golden color */
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Approve Users</h2>

    <?php if ($pending_users_result->num_rows > 0) : ?>
        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Registration ID</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $pending_users_result->fetch_assoc()) : ?>
                    <tr class="<?= $row['role'] === 'admin' ? 'highlight-admin' : '' ?>">
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['registration_number']) ?></td>
                        <td><?= htmlspecialchars($row['role']) ?></td>
                        <td>
                            <a href="?approve=<?= $row['id'] ?>" class="btn btn-primary">Approve</a>
                            <a href="?reject=<?= $row['id'] ?>" class="btn btn-danger">Reject</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p>No pending users for approval.</p>
    <?php endif; ?>

    <div class="btn-container">
        <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
    </div>
</div>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
