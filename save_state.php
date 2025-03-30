<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['answers'])) {
    $_SESSION['answers'] = json_decode($_POST['answers'], true);
}

if (isset($_POST['reviewedQuestions'])) {
    $_SESSION['reviewedQuestions'] = json_decode($_POST['reviewedQuestions'], true);
}

if (isset($_POST['problematicQuestions'])) {
    $_SESSION['problematicQuestions'] = json_decode($_POST['problematicQuestions'], true);
}

echo json_encode(['status' => 'success']);
?>