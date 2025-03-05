<?php
session_start();
include('db_config.php');

// Ensure only admin can access this page
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle category and question addition
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['category'])) {
        $category_name = $_POST['category'];
        $sql = "INSERT INTO categories (name) VALUES ('$category_name')";
        $conn->query($sql);
    }

    if (isset($_POST['question_text'])) {
        $question_text = $_POST['question_text'];
        $options = $_POST['options'];
        $correct_option = $_POST['correct_option'];
        $category_id = $_POST['category_id'];

        $sql = "INSERT INTO questions (question_text, options, correct_option, category_id) VALUES ('$question_text', '$options', '$correct_option', '$category_id')";
        $conn->query($sql);
    }
}

// Fetch categories and questions
$categories_result = $conn->query("SELECT * FROM categories");
$questions_result = $conn->query("SELECT questions.id, questions.question_text, categories.name AS category_name 
                                  FROM questions 
                                  JOIN categories ON questions.category_id = categories.id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-panel">
        <h2>Admin Panel</h2>

        <h3>Add Category</h3>
        <form method="POST">
            <input type="text" name="category" placeholder="Category Name" required>
            <button type="submit">Add Category</button>
        </form>

        <h3>Add Question</h3>
        <form method="POST">
            <textarea name="question_text" placeholder="Question" required></textarea>
            <input type="text" name="options" placeholder="Options (comma-separated)" required>
            <input type="number" name="correct_option" placeholder="Correct Option (1-4)" required>
            <select name="category_id">
                <?php while ($category = $categories_result->fetch_assoc()) { ?>
                    <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                <?php } ?>
            </select>
            <button type="submit">Add Question</button>
        </form>

        <h3>Existing Questions</h3>
        <table class="question-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Question</th>
                    <th>Category</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($question = $questions_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $question['id'] ?></td>
                        <td><?= htmlspecialchars($question['question_text']) ?></td>
                        <td><?= $question['category_name'] ?></td>
                        <td>
                            <a href="delete_question.php?id=<?= $question['id'] ?>" class="delete-btn">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>
