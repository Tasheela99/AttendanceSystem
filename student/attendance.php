<?php
// student/attendance.php

global $pdo;
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Check if attendance has already been marked today
try {
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE user_id = ? AND DATE(timestamp) = CURDATE()");
    $stmtCheck->execute([$userId]);
    $attendanceCount = $stmtCheck->fetchColumn();

    if ($attendanceCount > 0) {
        $message = "Attendance automatically marked for today.";
    } else {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['status']; // 'present', 'absent', 'late'

            try {
                $stmt = $pdo->prepare("INSERT INTO attendance (user_id, status) VALUES (?, ?)");
                $stmt->execute([$userId, $status]);
                $message = "Attendance marked successfully.";
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System</title>
    <link rel="stylesheet" href="/assets/css/attendance.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="container">
    <header>
        <h1><i class="fas fa-calendar-check"></i> Mark Attendance</h1>
    </header>

    <main>
        <div class="card">
            <?php if ($message): ?>
                <div class="message success">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="message error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Status:</label>
                    <div class="status-options">
                        <div class="status-option">
                            <input type="radio" id="present" name="status" value="present" required>
                            <label for="present">
                                <i class="fas fa-check-circle status-icon present"></i>
                                <span>Present</span>
                            </label>
                        </div>

                        <div class="status-option">
                            <input type="radio" id="absent" name="status" value="absent">
                            <label for="absent">
                                <i class="fas fa-times-circle status-icon absent"></i>
                                <span>Absent</span>
                            </label>
                        </div>

                        <div class="status-option">
                            <input type="radio" id="late" name="status" value="late">
                            <label for="late">
                                <i class="fas fa-clock status-icon late"></i>
                                <span>Late</span>
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-save"></i> Submit
                </button>
            </form>

            <div class="logout-section">
                <a href="../logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </main>
</div>
</body>
</html>