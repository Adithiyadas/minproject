<?php
session_start();
include('db_config.php');

// Ensure only authorized users (e.g., teachers) can access this page
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get the category ID from the URL
if (!isset($_GET['category_id'])) {
    die("Error: Category ID not provided.");
}
$category_id = intval($_GET['category_id']);

// Fetch the highest scores for the category
$leaderboard_query = $conn->prepare("
    SELECT users.name, MAX(scores.score) AS highest_score
    FROM scores
    JOIN users ON scores.user_id = users.id
    WHERE scores.category_id = ?
    GROUP BY scores.user_id
    ORDER BY highest_score DESC
");
$leaderboard_query->bind_param("i", $category_id);
$leaderboard_query->execute();
$leaderboard_result = $leaderboard_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leaderboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="leaderboard-container">
        <h2>Leaderboard</h2>
        <table>
            <tr>
                <th>Rank</th>
                <th>Name</th>
                <th>Highest Score</th>
            </tr>
            <?php 
            $rank = 1;
            while ($row = $leaderboard_result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $rank++ ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= $row['highest_score'] ?></td>
                </tr>
            <?php } ?>
        </table>
        <a href="admin_panel.php" class="back-btn">Back to Teacher Panel</a>
    </div>
</body>
</html>