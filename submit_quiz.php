<?php
session_start();
include('db_config.php');

if ($_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$category_id = $_POST['category_id']; // Get category_id from the POST request

// Validate category ID
$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM categories WHERE id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();

if ($category['count'] == 0) {
    die("Error: Invalid category ID.");
}

// Calculate the score
$score = 0;
foreach ($_POST as $question_id => $answer) {
    if ($question_id == 'category_id' || $question_id == 'submit') continue;

    $question_result = $conn->query("SELECT * FROM questions WHERE id = " . substr($question_id, 1));
    $question = $question_result->fetch_assoc();

    if ($answer == $question['correct_option']) {
        $score++;
    }
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Check if user already has a score for this category
$stmt = $conn->prepare("SELECT score FROM scores WHERE user_id = ? AND category_id = ?");
$stmt->bind_param("ii", $user_id, $category_id);
$stmt->execute();
$result = $stmt->get_result();
$existing_score = $result->fetch_assoc();

if ($existing_score) {
    // Update only if the new score is higher
    if ($score > $existing_score['score']) {
        $stmt = $conn->prepare("UPDATE scores SET score = ? WHERE user_id = ? AND category_id = ?");
        $stmt->bind_param("iii", $score, $user_id, $category_id);
        $stmt->execute();
    }
} else {
    // Insert new score if no previous entry exists
    $stmt = $conn->prepare("INSERT INTO scores (user_id, category_id, score) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $user_id, $category_id, $score);
    $stmt->execute();
}

// Fetch leaderboard (highest score per user)
$leaderboard_result = $conn->query("SELECT users.name, MAX(scores.score) as highest_score FROM scores JOIN users ON scores.user_id = users.id GROUP BY users.id ORDER BY highest_score DESC");

// Show the score to the user
echo "<h1><style=color:red>Your score is: $score"</h1>;
?>

<h2>Leaderboard</h2>
<table border="1">
    <tr>
        <th>Rank</th>
        <th>Name</th>
        <th>Highest Score</th>
    </tr>
    <?php $rank = 1; while ($row = $leaderboard_result->fetch_assoc()) { ?>
        <tr>
            <td><?= $rank++ ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= $row['highest_score'] ?></td>
        </tr>
    <?php } ?>
</table>
