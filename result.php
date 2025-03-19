<?php
session_start();
include('db_config.php');

// Ensure only users can access this page
if ($_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Get the user's score and total questions from the session
$score = $_SESSION['score'];
$total = $_SESSION['question_index'];
$category_id = $_SESSION['category_id']; // Get the category ID from the session
$user_id = $_SESSION['user_id']; // Get the user ID from the session

// Insert or update the score in the database
$stmt = $conn->prepare("
    INSERT INTO scores (user_id, category_id, score) 
    VALUES (?, ?, ?) 
    ON DUPLICATE KEY UPDATE score = GREATEST(score, VALUES(score))
");
$stmt->bind_param("iii", $user_id, $category_id, $score);

if ($stmt->execute()) {
    echo "Score inserted/updated successfully!";
} else {
    echo "Error: " . $stmt->error;
}
$stmt->close();

// Reset quiz-related session variables
unset($_SESSION['question_index']);
unset($_SESSION['score']);
unset($_SESSION['category_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz Result</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="result-container">
        <h1 style="color:<?= ($score >= $total / 2) ? 'green' : 'red' ?>;">
            Your Score: <?= $score ?> / <?= $total ?>
        </h1>
        <a href="quiz.php?category_id=<?= $category_id ?>" class="retry-btn">Retry Quiz</a>
        <a href="quiz_selection.php" class="back-btn">Back to Categories</a>
    </div>
</body>
</html>