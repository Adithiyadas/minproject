<?php
// db_config.php
$servername = "localhost";  // Your MySQL server (e.g., localhost for XAMPP)
$username = "root";         // Default PHPMyAdmin username
$password = "";             // Default password for XAMPP MySQL (empty string)
$dbname = "quiz_system";    // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
