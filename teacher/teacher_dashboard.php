<?php
global $pdo;
session_start();
include '../includes/config.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}

try {
    // Get total number of students
    $stmtStudents = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
    $totalStudents = $stmtStudents->fetchColumn();

    // Get total number of users
    $stmtUsers = $pdo->query("SELECT COUNT(*) FROM users");
    $totalUsers = $stmtUsers->fetchColumn();

    // Get attendance records today
    $stmtTodayAttendance = $pdo->query("SELECT COUNT(*) FROM attendance WHERE DATE(timestamp) = CURDATE() AND status = 'present'");
    $todayAttendance = $stmtTodayAttendance->fetchColumn();

    $stmtLateAttendance = $pdo->query("SELECT COUNT(*) FROM attendance WHERE DATE(timestamp) = CURDATE() AND status = 'late'");
    $lateAttendance = $stmtLateAttendance->fetchColumn();

    $absent = $pdo->query("SELECT COUNT(*) FROM attendance WHERE DATE(timestamp) = CURDATE() AND status = 'absent'");
    $absentAttendance = $absent->fetchColumn();

    // Get total attendance records
    $stmtTotalAttendance = $pdo->query("SELECT COUNT(*) FROM attendance");
    $totalAttendance = $stmtTotalAttendance->fetchColumn();

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Add Inter font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<header class="header">
    <h1 class="header-title">Teacher Dashboard</h1>
    <div class="user-welcome">
        <i class="fas fa-user-circle"></i>
        Welcome, <span><?php echo $_SESSION['user_name']; ?></span>
        <div class="badge">Teacher</div>
    </div>
</header>

<main class="dashboard-container">
    <!-- Statistics Section -->
    <section class="card">
        <div class="card-header">
            <h2 class="card-title">Class Overview</h2>
        </div>
        <div class="card-body">
            <div class="stats-grid">
                <div class="stat-card stat-users">
                    <i class="fas fa-user-graduate icon"></i>
                    <h3><?php echo $totalStudents; ?></h3>
                    <p>Total Students</p>
                </div>

                <div class="stat-card stat-attendance">
                    <i class="fas fa-calendar-check icon"></i>
                    <h3><?php echo $todayAttendance; ?></h3>
                    <p>Today's Attendance</p>
                </div>

                <div class="stat-card stat-attendance">
                    <i class="fas fa-calendar-check icon"></i>
                    <h3><?php echo $lateAttendance; ?></h3>
                    <p>Today's Late Attendance</p>
                </div>

                <div class="stat-card stat-attendance">
                    <i class="fas fa-calendar-check icon"></i>
                    <h3><?php echo $absentAttendance; ?></h3>
                    <p>Today's Absent</p>
                </div>

                <div class="stat-card stat-notifications">
                    <i class="fas fa-clipboard-list icon"></i>
                    <h3><?php echo $totalAttendance; ?></h3>
                    <p>Total Attendance Records</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Actions Section -->
    <section class="card">
        <div class="card-header">
            <h2 class="card-title">Teacher Actions</h2>
        </div>
        <div class="card-body">
            <ul class="action-links">
                <li>
                    <a href="view_attendance.php" class="action-link">
                        <i class="fas fa-eye"></i> View Attendance Records
                    </a>
                </li>
                <li>
                    <a href="mark_attendance.php" class="action-link">
                        <i class="fas fa-user-check"></i> Mark Student Attendance
                    </a>
                </li>
                <li>
                    <a href="students_list.php" class="action-link">
                        <i class="fas fa-users"></i> View Student List
                    </a>
                </li>
                <li>
                    <a href="../logout.php" class="action-link logout-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </section>
</main>
</body>
</html>