<?php

$message = '';
$error = '';

session_start();
include_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Check if attendance record exists for today
try {
    $stmtCheck = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? AND DATE(timestamp) = CURDATE()");
    $stmtCheck->execute([$userId]);
    $attendanceRecord = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($attendanceRecord) {
        // Record exists, allow status update
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
            $status = $_POST['status']; // 'present', 'absent', 'late'

            try {
                // Update existing attendance record
                $stmt = $pdo->prepare("UPDATE attendance SET status = ?, timestamp = CURRENT_TIMESTAMP WHERE user_id = ? AND DATE(timestamp) = CURDATE()");
                $stmt->execute([$status, $userId]);
                $message = "Attendance updated successfully as " . ucfirst($status) . ".";
                
                // Refresh the page to prevent duplicate submissions
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        } else {
            $message = "Attendance already marked for today.";
        }
    } else {
        // No record exists, allow initial marking
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
            $status = $_POST['status']; // 'present', 'absent', 'late'

            try {
                // Insert new attendance record
                $stmt = $pdo->prepare("INSERT INTO attendance (user_id, status, timestamp) VALUES (?, ?, CURRENT_TIMESTAMP)");
                $stmt->execute([$userId, $status]);
                $message = "Attendance marked successfully as " . ucfirst($status) . ".";
                
                // Refresh the page to prevent duplicate submissions
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
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
            <!-- Display success message if set -->
            <?php if (!empty($message)): ?>
                <div class="message success">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Display error message if set -->
            <?php if (!empty($error)): ?>
                <div class="message error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Status:</label>
                    <div class="status-options">
                        <div class="status-option">
                            <input type="radio" id="present" name="status" value="present" required 
                                <?php echo (isset($attendanceRecord) && $attendanceRecord['status'] == 'present') ? 'checked' : ''; ?>>
                            <label for="present">
                                <i class="fas fa-check-circle status-icon present"></i>
                                <span>Present</span>
                            </label>
                        </div>
                        <div class="status-option">
                            <input type="radio" id="late" name="status" value="late"
                                <?php echo (isset($attendanceRecord) && $attendanceRecord['status'] == 'late') ? 'checked' : ''; ?>>
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