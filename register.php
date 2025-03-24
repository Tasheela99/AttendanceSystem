<?php
global $pdo;
session_start();
include 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashedPassword, $role]);
        header("Location: login.php?message=" . urlencode("Registration successful! Please login."));
        exit;
    } catch (PDOException $e) {
        $error = "Registration error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Student Portal</title>
    <link rel="stylesheet" href="assets/css/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="container">
    <div class="register-card">
        <div class="register-header">
            <h1><i class="fas fa-user-plus"></i> Create Account</h1>
            <p class="subtitle">Join our Student Portal</p>
        </div>

        <?php if (isset($error)) { echo "<div class='alert alert-error'><i class='fas fa-exclamation-circle'></i> $error</div>"; } ?>

        <form method="post" class="register-form">
            <div class="form-group">
                <label for="name"><i class="fas fa-user"></i> Full Name</label>
                <input type="text" name="name" id="name" placeholder="Enter your full name" required>
            </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                <input type="email" name="email" id="email" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-fingerprint"></i> Place Your Finger Here</label>
                <input type="password" name="password" id="password" placeholder="●●●●●●●●" required>
            </div>

            <div class="form-group">
                <label for="role"><i class="fas fa-user-tag"></i> Select Role</label>
                <div class="select-wrapper">
                    <select name="role" id="role">
                        <option value="teacher">Teacher</option>
                        <option value="admin">Admin</option>
                    </select>
                    <i class="fas fa-chevron-down select-icon"></i>
                </div>
            </div>

            <button type="submit" class="btn-register">
                <i class="fas fa-user-check"></i> Create Account
            </button>
        </form>

        <div class="register-footer">
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
</div>
</body>
</html>