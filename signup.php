<?php
// signup.php
session_start();
include('db_config.php');

// Define admin password - in a real application, this should be stored securely
// and not directly in the code (e.g., in a config file outside web root)
$admin_password = "feature"; // This is just an example

// Handle signup form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    // Check if admin validation is required
    if ($role == 'admin') {
        if (!isset($_POST['admin_password']) || $_POST['admin_password'] !== $admin_password) {
            $error = "Invalid admin password. Access denied.";
        }
    }
    
    // Proceed with registration if no error
    if (!isset($error)) {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email already exists!";
        } else {
            // Insert new user
            $password_hashed = password_hash($password, PASSWORD_DEFAULT); // Secure password
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $password_hashed, $role);
            
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['role'] = $role;
                header("Location: login.php"); // Redirect to login page after successful signup
                exit();
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function toggleAdminPassword() {
            var roleSelect = document.getElementById('role');
            var adminPasswordField = document.getElementById('admin_password_field');
            
            if (roleSelect.value === 'admin') {
                adminPasswordField.style.display = 'block';
                document.getElementById('admin_password').required = true;
            } else {
                adminPasswordField.style.display = 'none';
                document.getElementById('admin_password').required = false;
            }
        }
    </script>
</head>
<body>
    <div class="signup-container">
        <h2>Signup</h2>
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role" id="role" required onchange="toggleAdminPassword()">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
            
            <div id="admin_password_field" style="display: none;">
                <input type="password" name="admin_password" id="admin_password" placeholder="Admin Password">
                <p class="info-text">Please enter the admin password to register as an administrator.</p>
            </div>
            
            <button type="submit">Signup</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>