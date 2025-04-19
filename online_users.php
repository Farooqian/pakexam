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

// Fetch only users whose latest session is active
$query = "
    SELECT u.full_name, u.registration_number, u.email, s.login_time
    FROM user_sessions s
    INNER JOIN (
        SELECT registration_number, MAX(id) AS latest_session_id
        FROM user_sessions
        GROUP BY registration_number
    ) latest ON s.id = latest.latest_session_id
    INNER JOIN users u ON s.registration_number = u.registration_number
    WHERE s.session_status = 'active'
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Online Users | Admin Panel</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
            color: #333;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
        }

        h1 {
            text-align: center;
            color: #007bff;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background: #007bff;
            color: white;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        button {
            background-color: #28a745;
            border: none;
            color: white;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #218838;
        }

        .no-users {
            text-align: center;
            font-size: 18px;
            margin-top: 20px;
            color: #999;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Currently Online Users</h1>

    <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Reg. Number</th>
                    <th>Email</th>
                    <th>Login Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= htmlspecialchars($row['registration_number']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['login_time']) ?></td>
                        <td>
                            <form method="POST" action="email_user.php">
                                <input type="hidden" name="email" value="<?= htmlspecialchars($row['email']) ?>">
                                <button type="submit">Email User</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-users">No users are currently online.</p>
    <?php endif; ?>
</div>

</body>
</html>
