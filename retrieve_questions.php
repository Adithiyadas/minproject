<?php
session_start();
include('db_config.php');

// Assuming session contains the admin's user ID
$admin_id = $_SESSION['user_id']; // The logged-in admin's user ID

// Variable to store error messages
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_id = $_POST['category_id'];
    $password = $_POST['password'];

    // Fetch the category and check if the admin owns it
    $sql = "SELECT * FROM categories WHERE id = ? AND category_owner = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $category_id, $admin_id);  // Check if the category belongs to the logged-in admin
    $stmt->execute();
    $result = $stmt->get_result();
    $category = $result->fetch_assoc();

    // If the category exists and password matches, fetch questions
    if ($category && password_verify($password, $category['password'])) {
        // Password is correct, fetch questions for the category
        $questions_sql = "SELECT * FROM questions WHERE category_id = ?";
        $questions_stmt = $conn->prepare($questions_sql);
        $questions_stmt->bind_param("i", $category_id);
        $questions_stmt->execute();
        $questions_result = $questions_stmt->get_result();
    } else {
        $error = "Invalid password or unauthorized access."; // Invalid password or unauthorized access error
    }
}

// Fetch all categories that belong to the logged-in admin
$categories_result = $conn->query("SELECT * FROM categories WHERE category_owner = $admin_id"); // Only categories owned by the logged-in admin
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - View Category Questions</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-panel">
        <h2>Admin Panel - View Category Questions</h2>

        <form method="POST">
            <!-- Dropdown to select category -->
            <select name="category_id" required>
                <?php while ($category = $categories_result->fetch_assoc()) { ?>
                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                <?php } ?>
            </select>

            <!-- Password field for category -->
            <input type="password" name="password" placeholder="Enter Category Password" required>

            <button type="submit">View Questions</button>
        </form>

        <?php if (isset($error) && $error != "") { ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php } ?>

        <?php if (isset($questions_result) && $questions_result->num_rows > 0) { ?>
            <h3>Questions for Category: <?= htmlspecialchars($category['name']) ?></h3>
            <table class="question-table">
                <thead>
                    <tr>
                        <th>Question</th>
                        <th>Options</th>
                        <th>Correct Option</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($question = $questions_result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= htmlspecialchars($question['question_text']) ?></td>
                            <td><?= htmlspecialchars($question['options']) ?></td>
                            <td><?= $question['correct_option'] ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</body>
</html>
