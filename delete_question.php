<?php
session_start();
include('db_config.php');

// Ensure only admin can delete questions
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Check if ID is provided
if (isset($_GET['id'])) {
    $question_id = $_GET['id'];

    // Delete the question from the database
    $sql = "DELETE FROM questions WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $question_id);

    if ($stmt->execute()) {
        echo "<script>alert('Question deleted successfully!'); window.location='admin_panel.php';</script>";
    } else {
        echo "<script>alert('Error deleting question.'); window.location='admin_panel.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location='admin_panel.php';</script>";
}
?>
