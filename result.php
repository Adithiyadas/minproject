<?php
session_start();
$score = $_SESSION['score'];
$total = $_SESSION['question_index'];
$category_id = $_SESSION['category_id']; // Get the category ID from the session

// Reset session for new quiz
session_destroy();
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
    </div>
</body>
</html>