<?php
session_start();
include('db_config.php');

// Ensure only users can access this page
if ($_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Get the selected category ID from the URL
if (!isset($_GET['category_id'])) {
    die("Error: Category ID not provided.");
}
$category_id = intval($_GET['category_id']);

// Fetch questions for the selected category
$sql = "SELECT * FROM questions WHERE category_id = ? ORDER BY id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();
$questions = $result->fetch_all(MYSQLI_ASSOC);

// Check if there are questions for the category
if (empty($questions)) {
    die("No questions found for this category.");
}

// Initialize session variables for the quiz
if (!isset($_SESSION['question_index'])) {
    $_SESSION['question_index'] = 0;
    $_SESSION['score'] = 0;
    $_SESSION['category_id'] = $category_id; // Store the selected category ID in the session
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selected_answer = intval($_POST['answer']);
    $current_index = $_SESSION['question_index'];

    // Check if answer is correct
    if ($selected_answer == $questions[$current_index]['correct_option']) {
        $_SESSION['score']++;
    }

    // Move to the next question
    $_SESSION['question_index']++;

    // If all questions are answered, redirect to result page
    if ($_SESSION['question_index'] >= count($questions)) {
        header("Location: result.php");
        exit();
    }
}

// Get current question
$current_index = $_SESSION['question_index'];
$current_question = $questions[$current_index];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="quiz-container">
        <h2>Question <?= $current_index + 1 ?> of <?= count($questions) ?></h2>
        <p><?= htmlspecialchars($current_question['question_text']) ?></p>

        <form method="POST">
            <?php 
            $options = explode(',', $current_question['options']); 
            foreach ($options as $index => $option) { ?>
                <label>
                    <input type="radio" name="answer" value="<?= $index + 1 ?>" required>
                    <?= htmlspecialchars($option) ?>
                </label><br>
            <?php } ?>
            
            <button type="submit" class="next-btn">
                <?= ($current_index + 1 == count($questions)) ? 'Finish' : 'Next' ?>
            </button>
        </form>
    </div>
</body>
</html>