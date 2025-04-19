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




// Check if a file has been uploaded
if (isset($_POST["submit"])) {
    // Get file details
    $fileName = $_FILES["fileToUpload"]["name"];
    $fileTmpName = $_FILES["fileToUpload"]["tmp_name"];
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Validate file type
    if ($fileType != "csv") {
        echo "Sorry, only CSV files are allowed.";
        exit;
    }

    // Open the file for reading
    if (($handle = fopen($fileTmpName, "r")) !== FALSE) {
        // Skip the first row if it's headers
        fgetcsv($handle);
        
        // Prepare a SQL statement to insert data
        $stmt = $conn->prepare("INSERT INTO MCQs (organization, subject, question, option1, option2, option3, option4, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        // Read each row of the CSV and insert it into the database
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $organization = $data[0];
            $subject = $data[1];
            $question = $data[2];
            $option1 = $data[3];
            $option2 = $data[4];
            $option3 = $data[5];
            $option4 = $data[6];
            $correct_answer = $data[7];

            // Bind the parameters and execute the query
            $stmt->bind_param("ssssssss", $organization, $subject, $question, $option1, $option2, $option3, $option4, $correct_answer);
            $stmt->execute();
        }

        fclose($handle);
        echo "Questions have been successfully uploaded!";
          
    } else {
        echo "There was an error opening the file.";
    }
} else {
    echo "No file uploaded.";

}


?>
<form action="dashboard.php" method="get">
        <button type="submit" style="background-color:rgb(90, 246, 95); margin-top: 20px; font: 15px;">Go to Dashboard</button>
            </form>