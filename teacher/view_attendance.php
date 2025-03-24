<?php
global $pdo;
session_start();
include '../includes/config.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}

// Set default date to today
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

try {
    // Fetch attendance records for the selected date
    $stmt = $pdo->prepare("
        SELECT a.id, a.user_id, a.status, a.timestamp, u.name 
        FROM attendance a 
        JOIN users u ON a.user_id = u.id 
        WHERE DATE(a.timestamp) = ? 
        ORDER BY u.name ASC
    ");
    $stmt->execute([$date]);
    $attendanceRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/view_attendance.css">

</head>
<body>
<header class="header">
    <h1 class="header-title">View Attendance</h1>
    <div class="user-welcome">
        <i class="fas fa-user-circle"></i>
        Welcome, <span><?php echo $_SESSION['user_name']; ?></span>
        <div class="badge">Teacher</div>
    </div>
</header>

<main class="dashboard-container">
    <section class="card">
        <div class="card-header">
            <h2 class="card-title">Attendance Records</h2>
        </div>
        <div class="card-body">
            <?php if (isset($error)): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php else: ?>
                <form method="get" class="attendance-filter">
                    <label for="date">Select Date:</label>
                    <input type="date" id="date" name="date" class="date-picker" value="<?php echo $date; ?>">
                    <button type="submit" class="filter-btn">View Records</button>
                </form>

                <?php if (empty($attendanceRecords)): ?>
                    <div class="no-records">No attendance records found for this date.</div>
                <?php else: ?>
                    <table>
                        <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Status</th>
                            <th>Time</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($attendanceRecords as $record): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['name']); ?></td>
                                <td>
                                        <span class="status-badge status-<?php echo $record['status']; ?>">
                                            <?php echo ucfirst($record['status']); ?>
                                        </span>
                                </td>
                                <td><?php echo date('h:i A', strtotime($record['timestamp'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            <?php endif; ?>

            <a href="teacher_dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </section>
</main>
</body>
</html>