<?php
global $pdo;
session_start();
include '../includes/config.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}

$message = '';
$error = '';

try {
    // Fetch all students
    $stmtStudents = $pdo->query("SELECT id, name FROM users WHERE role = 'student' ORDER BY name ASC");
    $students = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Begin transaction
        $pdo->beginTransaction();

        try {
            $date = $_POST['attendance_date'];

            // Delete any existing attendance records for this date
            $stmtDelete = $pdo->prepare("DELETE FROM attendance WHERE DATE(timestamp) = ?");
            $stmtDelete->execute([$date]);

            // Insert new attendance records
            $stmtInsert = $pdo->prepare("INSERT INTO attendance (user_id, status, timestamp) VALUES (?, ?, ?)");

            foreach ($_POST['status'] as $studentId => $status) {
                $timestamp = $date . ' ' . date('H:i:s');
                $stmtInsert->execute([$studentId, $status, $timestamp]);
            }

            // Commit transaction
            $pdo->commit();
            $message = "Attendance successfully recorded for " . date('F j, Y', strtotime($date));

        } catch (Exception $e) {
            // Rollback in case of error
            $pdo->rollBack();
            $error = "Error recording attendance: " . $e->getMessage();
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
    <title>Mark Attendance</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/mark_attendance.css">
</head>
<body>
<header class="header">
    <h1 class="header-title">Mark Attendance</h1>
    <div class="user-welcome">
        <i class="fas fa-user-circle"></i>
        Welcome, <span><?php echo $_SESSION['user_name']; ?></span>
        <div class="badge">Teacher</div>
    </div>
</header>

<main class="dashboard-container">
    <section class="card">
        <div class="card-header">
            <h2 class="card-title">Mark Student Attendance</h2>
        </div>
        <div class="card-body">
            <?php if (!empty($message)): ?>
                <div class="success-message"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="form-header">
                    <label for="attendance_date">Select Date:</label>
                    <input type="date" id="attendance_date" name="attendance_date" class="date-input" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="student-list">
                    <?php foreach ($students as $student): ?>
                        <div class="student-item">
                            <div class="student-name"><?php echo htmlspecialchars($student['name']); ?></div>
                            <select name="status[<?php echo $student['id']; ?>]" class="status-select">
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                            </select>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="submit" class="submit-btn">Save Attendance</button>
            </form>

            <a href="teacher_dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </section>
</main>
</body>
</html>