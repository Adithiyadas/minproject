<?php
session_start();
include('db_config.php');

$category_id = $_GET['category_id']; // Get category from URL
$leaderboard_query = $conn->query("
    SELECT users.name, scores.score 
    FROM scores 
    JOIN users ON scores.user_id = users.id 
    WHERE scores.category_id = $category_id
    ORDER BY scores.score DESC
");

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
                <th>Score</th>
            </tr>
            <?php 
            $rank = 1;
            while ($row = $leaderboard_query->fetch_assoc()) { ?>
                <tr>
                    <td><?= $rank++ ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= $row['score'] ?></td>
                </tr>
            <?php } ?>
        </table>
        <a href="quiz_selection.php" class="back-btn">Back to Quizzes</a>
    </div>
</body>
</html>
