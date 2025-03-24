<?php
global $pdo;
session_start();
include '../includes/config.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$message = '';
$error = '';

try {
    // Prepare the base query
    $query = "SELECT id, name, email, created_at FROM users WHERE role = 'student'";
    $params = [];

    // Add search filter if provided
    if (!empty($search)) {
        $query .= " AND (name LIKE ? OR email LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    // Add order by
    $query .= " ORDER BY name ASC";

    // Execute the query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/students_list.css">
</head>
<body>
<header class="header">
    <h1 class="header-title">Student List</h1>
    <div class="user-welcome">
        <i class="fas fa-user-circle"></i>
        Welcome, <span><?php echo $_SESSION['user_name']; ?></span>
        <div class="badge">Teacher</div>
    </div>
</header>

<main class="dashboard-container">
    <section class="card">
        <div class="card-header">
            <h2 class="card-title">All Students</h2>
        </div>
        <div class="card-body">
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php else: ?>
                <form method="get" class="search-container">
                    <input type="text" name="search" class="search-input" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>

                <div class="student-count">
                    Total Students: <span><?php echo count($students); ?></span>
                    <?php if (!empty($search)): ?>
                        (filtered from search: "<?php echo htmlspecialchars($search); ?>")
                    <?php endif; ?>
                </div>

                <?php if (empty($students)): ?>
                    <div class="no-records">No students found.</div>
                <?php else: ?>
                    <div class="table-container">
                        <table>
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo $student['id']; ?></td>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                                    <td>
                                        <a href="view_attendance.php?id=<?php echo $student['id']; ?>" class="action-btn">
                                            <i class="fas fa-calendar-check"></i> View Attendance
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
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