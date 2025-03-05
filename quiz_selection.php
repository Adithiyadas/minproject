<?php
session_start();
include('db_config.php');

// Ensure only user can access this page
if ($_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$categories_result = $conn->query("SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Quiz</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="quiz-selection">
        <h2>Select Quiz Category</h2>
        <form action="quiz.php" method="GET">
            <select name="category_id" required>
                <?php while ($category = $categories_result->fetch_assoc()) { ?>
                    <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                <?php } ?>
            </select>
            <button type="submit">Start Quiz</button>
        </form>
    </div>
</body>
</html>

