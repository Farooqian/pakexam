<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to ProjectD</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #ff758c, #ff7eb3);
            color: #fff;
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.2);
            animation: slideDown 1s ease-in-out;
        }
        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .features {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 40px;
            flex-wrap: wrap;
        }
        .feature {
            background: rgba(255, 255, 255, 0.2);
            padding: 20px;
            border-radius: 10px;
            width: 250px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease-in-out;
        }
        .feature:hover {
            transform: translateY(-5px);
        }
        h2, h3 {
            margin: 10px 0;
        }
        .auth-buttons {
            margin-top: 30px;
        }
        .btn {
            text-decoration: none;
            background: #ffcc00;
            color: #333;
            padding: 12px 24px;
            margin: 10px;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
            transition: background 0.3s ease-in-out, transform 0.2s;
        }
        .btn:hover {
            background: #ffd633;
            transform: scale(1.05);
        }
        footer {
            margin-top: 50px;
            padding: 10px;
            background: rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome to ProjectD</h1>
        <p>Your gateway to smart and efficient online examinations.</p>
    </header>
    
    <section class="features">
        <div class="feature">
            <h2>📖 Exam System</h2>
            <h3>Seamless Exams</h3>
            <p>Experience smooth and interactive exams.</p>
        </div>
        <div class="feature">
            <h2>📊 Analysis</h2>
            <h3>Smart Analytics</h3>
            <p>Get detailed insights into your performance.</p>
        </div>
        <div class="feature">
            <h2>🔒 Security</h2>
            <h3>Secure & Reliable</h3>
            <p>Your data is safe with our secure system.</p>
        </div>
    </section>
    
    <div class="auth-buttons">
        <a href="login.php" class="btn">Login</a>
        <a href="register.php" class="btn">Register</a>
    </div>
    
    <footer>
        <p>RAYZON Pvt Ltd | All Rights Reserved</p>
    </footer>
</body>
</html>
