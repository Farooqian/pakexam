<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload MCQs CSV</title>
</head>
<body>
    <h2>Upload MCQs CSV</h2>
    <form action="upload_mcqs.php" method="post" enctype="multipart/form-data">
        <input type="file" name="csv_file" accept=".csv" required>
        <button type="submit">Upload</button>
    </form>
</body>
</html>
