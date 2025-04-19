<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_or_regid = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (empty($email_or_regid) || empty($password)) {
        $_SESSION['error'] = "All fields are required!";
        header("Location: login.php");
        exit();
    }

    // Include status and role in the query
    $query = "SELECT id, full_name, email, password, registration_number, status, role FROM users WHERE email = ? OR registration_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email_or_regid, $email_or_regid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Check status before login
        if ($user['status'] === 'pending') {
            $_SESSION['error'] = "Your account is pending approval. Please wait for admin approval.";
            header("Location: login.php");
            exit();
        } elseif ($user['status'] === 'rejected') {
            $_SESSION['error'] = "Your account has been rejected. Contact support for help.";
            header("Location: login.php");
            exit();
        }

        if (password_verify($password, $user['password'])) {
            // Set session variables including user role
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["full_name"];
            $_SESSION["user_email"] = $user["email"];
            $_SESSION["registration_number"] = $user["registration_number"];  // Add this
            $_SESSION["user_role"] = $user["role"];  // Set role

            // Track login time in user_sessions table
            $login_time = date("Y-m-d H:i:s");
            $session_status = 'active';
            $insert_session_query = "INSERT INTO user_sessions (registration_number, login_time, session_status) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_session_query);
            $stmt->bind_param("sss", $user['registration_number'], $login_time, $session_status);
            $stmt->execute();

            // Redirect to appropriate dashboard based on role
            if ($_SESSION["user_role"] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit();
        } else {
            $_SESSION['error'] = "Invalid password!";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "No account found with that email or registration ID!";
        header("Location: login.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>
