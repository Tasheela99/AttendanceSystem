<?php
// admin/edit_user.php

global $pdo;
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $userId = $_GET['id'];
    try {
        $stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmtUser->execute([$userId]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            header("Location: manage_users.php");
            exit;
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
        exit;
    }
} else {
    header("Location: manage_users.php");
    exit;
}

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    try {
        $stmtUpdate = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
        $stmtUpdate->execute([$name, $email, $role, $userId]);
        $success = true;

        // Fetch updated user data
        $stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmtUser->execute([$userId]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        // Redirect after a short delay to show success message
        header("refresh:2;url=manage_users.php");
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Get user initials for avatar
function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';

    foreach ($words as $word) {
        if (!empty($word[0])) {
            $initials .= strtoupper($word[0]);
            if (strlen($initials) >= 2) break;
        }
    }

    return $initials;
}

$userInitials = getInitials($user['name']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="/assets/css/edit_user.css">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Add Inter font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<header class="header">
    <h1 class="header-title">Edit User</h1>
    <a href="manage_users.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to User Management
    </a>
</header>

<div class="form-card">
    <div class="user-profile">
        <div class="user-avatar">
            <?php echo $userInitials; ?>
        </div>
        <div class="user-info">
            <h2><?php echo $user['name']; ?></h2>
            <p><?php echo $user['email']; ?></p>
            <span class="user-badge <?php echo strtolower($user['role']); ?>">
                    <?php echo ucfirst($user['role']); ?>
                </span>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            User information has been successfully updated! Redirecting to user management...
        </div>
    <?php endif; ?>

    <?php if (!$success): ?>
        <form method="post" id="editUserForm">
            <div class="form-group">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" id="name" name="name" class="form-input" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                <div class="form-hint">This will be used for login and notifications</div>
            </div>

            <div class="form-group">
                <label for="role" class="form-label">User Role</label>
                <select id="role" name="role" class="form-select" required>
                    <option value="student" <?php if ($user['role'] === 'student') echo 'selected'; ?>>Student</option>
                    <option value="teacher" <?php if ($user['role'] === 'teacher') echo 'selected'; ?>>Teacher</option>
                    <option value="admin" <?php if ($user['role'] === 'admin') echo 'selected'; ?>>Administrator</option>
                </select>
                <div class="form-hint">This determines what permissions the user will have</div>
            </div>

            <div class="form-footer">
                <div>
                    <a href="reset_password.php?id=<?php echo $user['id']; ?>" class="form-button cancel">
                        <i class="fas fa-key"></i> Reset Password
                    </a>
                </div>
                <div>
                    <a href="manage_users.php" class="form-button cancel">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="form-button">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
    // Form validation
    document.getElementById('editUserForm').addEventListener('submit', function(event) {
        const email = document.getElementById('email').value;
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailPattern.test(email)) {
            event.preventDefault();
            alert('Please enter a valid email address');
        }
    });
</script>
</body>
</html>