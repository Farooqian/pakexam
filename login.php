<?php if (session_status() == PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ProjectD</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #1E90FF, #FF69B4);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 100%;
            max-width: 350px;
        }
        h2 {
            color: #333;
            margin-bottom: 1rem;
        }
        .error-message {
            color: red;
            background: #ffdede;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: bold;
        }
        .input-group {
            width: 100%;
            margin-bottom: 1rem;
        }
        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            outline: none;
            transition: 0.3s;
        }
        .input-group input:focus {
            border-color: #1E90FF;
            box-shadow: 0 0 5px rgba(30, 144, 255, 0.5);
        }
        .login-btn {
            width: 100%;
            background: #1E90FF;
            color: white;
            padding: 10px;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }
        .login-btn:hover {
            background: #FF69B4;
        }
        .links {
            margin-top: 10px;
        }
        .links a {
            text-decoration: none;
            color: #1E90FF;
            font-weight: bold;
        }
        .links a:hover {
            color: #FF69B4;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login to ProjectD</h2>

        <?php
        $error_message = "";
        if (isset($_SESSION['error'])) {
            $error_message = $_SESSION['error'];
            unset($_SESSION['error']); // Clear the error message after displaying
        }
        ?>

        <?php if (!empty($error_message)) : ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form action="login_process.php" method="POST">
            <div class="input-group">
                <input type="text" name="email" placeholder="Email or Registration ID" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="login-btn">Login</button>
        </form>

        <div class="links">
            <p><a href="register.php">Create an Account</a> | <a href="forgot_password.php">Forgot Password?</a></p>
        </div>
    </div>
</body>
</html>
