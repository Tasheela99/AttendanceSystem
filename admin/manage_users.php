<?php
global $pdo;
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

try {
    $stmtUsers = $pdo->query("SELECT * FROM users");
    $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}

if (isset($_GET['delete'])) {
    $userIdToDelete = $_GET['delete'];
    try {
        $stmtDelete = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmtDelete->execute([$userIdToDelete]);
        header("Location: manage_users.php");
        exit;
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="/assets/css/manage_users.css">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Add Inter font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<header class="header">
    <h1 class="header-title">Manage Users</h1>
    <a href="dashboard.php" class="action-button secondary">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</header>

<main class="content-card">
    <div class="action-bar">
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Search users..." id="userSearch">
        </div>
        <a href="add_user.php" class="action-button">
            <i class="fas fa-user-plus"></i> Add New User
        </a>
    </div>

    <?php if (empty($users)): ?>
        <div class="empty-state">
            <i class="fas fa-users"></i>
            <p>No users found in the system.</p>
            <a href="add_user.php" class="action-button">Add Your First User</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="users-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td>
                            <div class="user-info">
                                <i class="fas fa-user-circle"></i>
                                <?php echo $user['name']; ?>
                            </div>
                        </td>
                        <td><?php echo $user['email']; ?></td>
                        <td>
                                    <span class="role-badge <?php echo strtolower($user['role']); ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn-action btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="manage_users.php?delete=<?php echo $user['id']; ?>"
                                   onclick="return confirm('Are you sure you want to delete this user?')"
                                   class="btn-action btn-delete">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Simple Pagination - can be enhanced with actual pagination logic -->
        <div class="pagination">
            <a href="#" class="active">1</a>
            <a href="#">2</a>
            <a href="#">3</a>
            <a href="#"><i class="fas fa-chevron-right"></i></a>
        </div>
    <?php endif; ?>
</main>

<script>
    // Simple client-side search functionality
    document.getElementById('userSearch').addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('.users-table tbody tr');

        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if(text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>
</body>
</html>