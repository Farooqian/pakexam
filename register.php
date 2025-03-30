<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to ProjectD</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.min.css"/>
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
        .container {
            background: rgba(255, 255, 255, 0.2);
            padding: 25px;
            border-radius: 10px;
            width: 350px;
            margin: auto;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin-top: 40px;
        }
        input, select {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            display: block;
            box-sizing: border-box;
            text-align: left;
        }
        .btn {
            background: #ffcc00;
            color: #333;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
        }
        .btn:hover {
            background: #ffd633;
        }
        .error {
            color: red;
            font-size: 14px;
            font-weight: bold;
        }
        #registration_number {
            display: none;
        }
        .iti__selected-flag {
            background-color: green !important;
            color: white !important;
            padding: 5px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome to ProjectD</h1>
    </header>
    
    <div class="container">
        <h2>Register</h2>
        <form id="registerForm" action="register_process.php" method="POST">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="email" name="email" id="email" placeholder="Email" required>
            <span id="emailError" class="error"></span>
            <input type="tel" id="phone" name="phone" placeholder="Phone Number" required>
            <input type="text" name="dob" id="dob" placeholder="Date of Birth (DD-MM-YYYY)" required>
            <select name="role">
                <option value="student">Student</option>
                <option value="admin">Admin</option>
            </select>
            <input type="text" name="registration_number" id="registration_number" readonly>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <label>
                <input type="checkbox" name="terms" required> I agree to the Terms & Conditions
            </label>
            <button type="submit" class="btn">Register</button>
        </form>
        <p>Already a member? <a href="login.php">Login</a></p>
    </div>
    
    <script>
        $(document).ready(function() {
            $("#email").on("blur", function() {
                var email = $(this).val();
                $.post("check_email.php", { email: email }, function(data) {
                    if (data === "exists") {
                        $("#emailError").text("Email is already registered!");
                    } else {
                        $("#emailError").text("");
                    }
                });
            });

            var phoneInput = document.querySelector("#phone");
            var iti = window.intlTelInput(phoneInput, {
                initialCountry: "pk",
                separateDialCode: true,
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
            });

            function generateRegistrationNumber() {
                var date = new Date();
                var yy = date.getFullYear().toString().slice(-2);
                var mm = ("0" + (date.getMonth() + 1)).slice(-2);
                var uniqueID = Math.floor(Math.random() * 900000) + 100000;
                $("#registration_number").val(yy + mm + uniqueID);
            }
            generateRegistrationNumber();
        });

        document.getElementById("dob").addEventListener("input", function(e) {
            this.value = this.value.replace(/[^0-9-]/g, '');
            if (this.value.length === 2 || this.value.length === 5) {
                this.value += '-';
            }
        });
    </script>
</body>
</html>
