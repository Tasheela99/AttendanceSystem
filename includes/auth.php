<?php
require 'config.php';

// Check if user is logged in
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

// Redirect non-authenticated users
function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: ../login.php');
        exit();
    }
}

// Login logic
function loginUser($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}