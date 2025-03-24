<?php
// admin/reports.php

global $pdo;
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

try {
    // Example report query (you can customize this)
    $stmtAttendanceReport = $pdo->query("SELECT users.name, attendance.timestamp, attendance.status FROM attendance JOIN users ON attendance.user_id = users.id ORDER BY attendance.timestamp DESC");
    $attendanceReport = $stmtAttendanceReport->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Attendance Reports</title>
    <link rel="stylesheet" href="/assets/css/reports.css"> <!-- Link to the external CSS -->
</head>
<body>

<!-- Header Section -->
<div class="header">
    <div class="header-title">
        <h1>Attendance Reports</h1>
    </div>
    <a href="dashboard.php" class="back-button">
        <i class="fa fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<!-- Table Section -->
<div class="table-container">
    <table>
        <thead>
        <tr>
            <th>User Name</th>
            <th>Timestamp</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($attendanceReport as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
