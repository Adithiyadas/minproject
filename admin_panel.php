<?php
session_start();
include('db_config.php');

// Ensure only admin can access this page
if ($_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['user_id']; // Logged-in admin's ID
$error_message = "";
$success_message = "";

// Handle category addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['category'])) {
    $category_name = trim($_POST['category']);
    $category_password = trim($_POST['category_password']);

    if (!empty($category_name) && !empty($category_password)) {
        $hashed_password = password_hash($category_password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO categories (name, password, category_owner) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $category_name, $hashed_password, $admin_id);

        if ($stmt->execute()) {
            $success_message = "Category added successfully!";
        } else {
            $error_message = "Error adding category: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Category name and password cannot be empty!";
    }
}

// Handle question addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['question_text'])) {
    $question_text = trim($_POST['question_text']);
    $options = trim($_POST['options']);
    $correct_option = intval($_POST['correct_option']);
    $category_id = intval($_POST['category_id']);

    // Verify admin owns the category
    $category_check = $conn->prepare("SELECT id FROM categories WHERE id = ? AND category_owner = ?");
    $category_check->bind_param("ii", $category_id, $admin_id);
    $category_check->execute();
    $result = $category_check->get_result();
    if ($result->num_rows > 0) {
        $sql = "INSERT INTO questions (question_text, options, correct_option, category_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $question_text, $options, $correct_option, $category_id);

        if ($stmt->execute()) {
            $success_message = "Question added successfully!";
        } else {
            $error_message = "Error adding question: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Unauthorized action!";
    }
}

// Handle question deletion (Only category owner can delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_question'])) {
    $question_id = intval($_POST['delete_question']);

    // Ensure the question belongs to a category owned by the admin
    $stmt = $conn->prepare("DELETE FROM questions WHERE id = ? AND category_id IN (SELECT id FROM categories WHERE category_owner = ?)");
    $stmt->bind_param("ii", $question_id, $admin_id);
    if ($stmt->execute()) {
        $success_message = "Question deleted successfully!";
    } else {
        $error_message = "Error deleting question!";
    }
    $stmt->close();
}

// Fetch categories belonging to the admin
$categories_result = $conn->prepare("SELECT * FROM categories WHERE category_owner = ?");
$categories_result->bind_param("i", $admin_id);
$categories_result->execute();
$categories_result = $categories_result->get_result();

// Fetch questions for a specific category if requested
if (isset($_POST['view_category'])) {
    $category_id = intval($_POST['view_category']);
    $password = $_POST['password'];

    // Verify category ownership and password
    $category_check = $conn->prepare("SELECT * FROM categories WHERE id = ? AND category_owner = ?");
    $category_check->bind_param("ii", $category_id, $admin_id);
    $category_check->execute();
    $category_check_result = $category_check->get_result();
    $category = $category_check_result->fetch_assoc();

    if ($category && password_verify($password, $category['password'])) {
        $questions_result = $conn->prepare("SELECT * FROM questions WHERE category_id = ?");
        $questions_result->bind_param("i", $category_id);
        $questions_result->execute();
        $questions_result = $questions_result->get_result();
    } else {
        $error_message = "Invalid password or unauthorized access.";
    }
}
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
        <h2>Teacher Panel</h2>

        <?php if (!empty($success_message)) { ?>
            <p class="success"><?= $success_message ?></p>
        <?php } ?>
        <?php if (!empty($error_message)) { ?>
            <p class="error"><?= $error_message ?></p>
        <?php } ?>

        <!-- Add Category Form -->
        <h3>Add Category</h3>
        <form method="POST">
            <input type="text" name="category" placeholder="Category Name" required>
            <input type="password" name="category_password" placeholder="New Password for Category" required>
            <button type="submit">Add Category</button>
        </form>

        <!-- Add Question Form -->
        <h3>Add Question</h3>
        <form method="POST">
            <textarea name="question_text" placeholder="Question" required></textarea>
            <input type="text" name="options" placeholder="Options (comma-separated)" required>
            <input type="number" name="correct_option" placeholder="Correct Option Index (starting from 1)" required>
            <select name="category_id" required>
                <?php while ($category = $categories_result->fetch_assoc()) { ?>
                    <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                <?php } ?>
            </select>
            <button type="submit">Add Question</button>
        </form>

        <!-- Display Categories with View Button -->
        <h3>Your Categories</h3>
        <table>
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $categories_result->data_seek(0); // Reset pointer
                while ($category = $categories_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($category['name']) ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="view_category" value="<?= $category['id'] ?>">
                                <input type="password" name="password" placeholder="Enter Password" required>
                                <button type="submit">View Questions</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Display Questions if Selected -->
        <?php if (isset($questions_result)) { ?>
            <h3>Questions for your Category <?= isset($category['name']) ? htmlspecialchars($category['name']) : '' ?></h3>

            <table>
                <thead>
                    <tr>
                        <th>Question</th>
                        <th>Options</th>
                        <th>Correct Answer</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($question = $questions_result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= htmlspecialchars($question['question_text']) ?></td>
                            <td><?= htmlspecialchars($question['options']) ?></td>
                            <td><?= $question['correct_option'] ?></td>
                            <td>
                                <form method="POST">
                                    <button type="submit" name="delete_question" value="<?= $question['id'] ?>" onclick="return confirm('Delete this question?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</body>
</html>
