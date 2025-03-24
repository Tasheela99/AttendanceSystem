<?php
// admin/add_user.php

global $pdo;
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashedPassword, $role]);
        $success = true;

        // Redirect after a short delay to show success message
        header("refresh:2;url=manage_users.php");
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <link rel="stylesheet" href="/assets/css/add_user.css">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Add Inter font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<header class="header">
    <h1 class="header-title">Add New User</h1>
    <a href="manage_users.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to User Management
    </a>
</header>

<div class="form-card">
    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            User has been successfully added! Redirecting to user management...
        </div>
    <?php endif; ?>

    <?php if (!$success): ?>
        <form method="post" id="addUserForm">
            <div class="form-group">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" id="name" name="name" class="form-input" required>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-input" required>
                <div class="form-hint">This will be used for login and notifications</div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-input" required>
                <div class="password-strength">
                    <div class="password-strength-meter" id="passwordStrength"></div>
                </div>
                <div class="form-hint" id="passwordHint">Use at least 8 characters with letters, numbers, and special characters</div>
            </div>

            <div class="form-group">
                <label for="role" class="form-label">User Role</label>
                <select id="role" name="role" class="form-select" required>
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                    <option value="admin">Administrator</option>
                </select>
                <div class="form-hint">This determines what permissions the user will have</div>
            </div>

            <div class="form-footer">
                <a href="manage_users.php" class="form-button cancel">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="form-button">
                    <i class="fas fa-user-plus"></i> Add User
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
    // Password strength indicator
    const passwordInput = document.getElementById('password');
    const strengthMeter = document.getElementById('passwordStrength');
    const passwordHint = document.getElementById('passwordHint');

    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;

        // Check length
        if (password.length >= 8) strength += 1;

        // Check for letters
        if (/[a-zA-Z]/.test(password)) strength += 1;

        // Check for numbers
        if (/[0-9]/.test(password)) strength += 1;

        // Check for special characters
        if (/[^a-zA-Z0-9]/.test(password)) strength += 1;

        // Update the visual indicator
        strengthMeter.className = 'password-strength-meter';

        if (password.length === 0) {
            strengthMeter.style.width = '0';
            passwordHint.textContent = 'Use at least 8 characters with letters, numbers, and special characters';
        } else if (strength <= 2) {
            strengthMeter.classList.add('strength-weak');
            passwordHint.textContent = 'Weak password - add more variety';
        } else if (strength === 3) {
            strengthMeter.classList.add('strength-medium');
            passwordHint.textContent = 'Medium strength - consider adding special characters';
        } else {
            strengthMeter.classList.add('strength-strong');
            passwordHint.textContent = 'Strong password';
        }
    });

    // Form validation
    document.getElementById('addUserForm').addEventListener('submit', function(event) {
        const password = passwordInput.value;

        if (password.length < 8) {
            event.preventDefault();
            alert('Password must be at least 8 characters long');
            passwordInput.focus();
        }
    });
</script>
</body>
</html>