<?php
global $pdo;
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

try {
    $stmtUsers = $pdo->query("SELECT COUNT(*) FROM users");
    $totalUsers = $stmtUsers->fetchColumn();

    $stmtAttendance = $pdo->query("SELECT COUNT(*) FROM attendance");
    $totalAttendance = $stmtAttendance->fetchColumn();

    $stmtNotifications = $pdo->query("SELECT COUNT(*) FROM notifications");
    $totalNotifications = $stmtNotifications->fetchColumn();

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
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Add Inter font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<header class="header">
    <h1 class="header-title">Admin Dashboard</h1>
    <div class="user-welcome">
        <i class="fas fa-user-circle"></i>
        Welcome, <span><?php echo $_SESSION['user_name']; ?></span>
        <div class="badge">Admin</div>
    </div>
</header>

<main class="dashboard-container">
    <!-- Statistics Section -->
    <section class="card">
        <div class="card-header">
            <h2 class="card-title">Overview</h2>
        </div>
        <div class="card-body">
            <div class="stats-grid">
                <div class="stat-card stat-users">
                    <i class="fas fa-users icon"></i>
                    <h3><?php echo $totalUsers; ?></h3>
                    <p>Total Users</p>
                </div>

                <div class="stat-card stat-attendance">
                    <i class="fas fa-calendar-check icon"></i>
                    <h3><?php echo $totalAttendance; ?></h3>
                    <p>Attendance Records</p>
                </div>

                <div class="stat-card stat-notifications">
                    <i class="fas fa-bell icon"></i>
                    <h3><?php echo $totalNotifications; ?></h3>
                    <p>Notifications</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Actions Section -->
    <section class="card">
        <div class="card-header">
            <h2 class="card-title">Quick Actions</h2>
        </div>
        <div class="card-body">
            <ul class="action-links">
                <li>
                    <a href="manage_users.php" class="action-link">
                        <i class="fas fa-user-cog"></i> Manage Users
                    </a>
                </li>
                <li>
                    <a href="reports.php" class="action-link">
                        <i class="fas fa-chart-bar"></i> Generate Reports
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