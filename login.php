<?php
// login.php - Login and Attendance Marking
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ensure Composer dependencies are installed
session_start();
include 'includes/config.php'; // Database configuration

global $pdo;

function logEmailStatus($message) {
    file_put_contents('email_log.txt', date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

function sendEmailToTeachers($pdo, $studentName, $studentEmail): void
{
    try {
        // Get all teachers' emails
        $stmtTeachers = $pdo->prepare("SELECT name, email FROM users WHERE role = 'teacher'");
        $stmtTeachers->execute();
        $teachers = $stmtTeachers->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($teachers)) {
            $mail = new PHPMailer(true);

            // Mercury Mail Server Configuration (XAMPP)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'tasheelajay1999@gmail.com';  // Your Gmail address
            $mail->Password   = 'xefo pjhe iwmi mqzh';    // Use an App Password (not your regular password)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;


            // Email settings
            $mail->setFrom('tasheelajay1999@gmail.com', 'Student Portal');
            $mail->Subject = 'Student Login Notification: ' . $studentName;

            // Add teachers as BCC recipients
            foreach ($teachers as $teacher) {
                $mail->addBCC($teacher['email'], $teacher['name']);
            }

            // Email content
            $mail->isHTML(true);
            $mail->Body = "
                <h3>Student Login Alert</h3>
                <p>Student <strong>{$studentName}</strong> has logged in.</p>
                <p>Details:</p>
                <ul>
                    <li>Email: {$studentEmail}</li>
                    <li>Login Time: " . date('Y-m-d H:i:s') . "</li>
                </ul>
            ";

            if ($mail->send()) {
                logEmailStatus("Email sent successfully to teachers about student {$studentName} ({$studentEmail}).");
            } else {
                logEmailStatus("Email sending failed for student {$studentName}: " . $mail->ErrorInfo);
            }
        } else {
            logEmailStatus("No teachers found in the database to send an email.");
        }
    } catch (Exception $e) {
        logEmailStatus("Email Error: " . $e->getMessage());
    }
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT id, name, role, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Send notifications for student logins
            if ($user['role'] === 'student') {
                sendEmailToTeachers($pdo, $user['name'], $email);
                header("Location: student/attendance.php");
                exit;
            }

            // Redirect based on role
            switch ($user['role']) {
                case 'admin':
                    header("Location: admin/dashboard.php");
                    break;
                case 'teacher':
                    header("Location: teacher/teacher_dashboard.php");
                    break;
                default:
                    header("Location: index.php");
            }
            exit;
        } else {
            $error = "Invalid credentials";
            logEmailStatus("Login failed for email: {$email}");
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
        logEmailStatus("Database error: " . $e->getMessage());
    }
}

// Handle attendance marking (if implemented)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['mark_attendance'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO attendance (user_id, status) VALUES (?, 'present')");
        $stmt->execute([$_SESSION['user_id'] ?? null]);

        header("Location: index.php?success=Attendance+marked");
        exit;
    } catch (PDOException $e) {
        $error = "Attendance error: " . $e->getMessage();
        logEmailStatus("Attendance error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Mark Attendance</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="container">
    <div class="login-card">
        <div class="login-header">
            <h1><i class="fas fa-user-graduate"></i> Student Portal</h1>
            <p class="subtitle">Login / Mark Attendance</p>
        </div>

        <?php if (isset($error)) { echo "<div class='alert alert-error'><i class='fas fa-exclamation-circle'></i> $error</div>"; } ?>
        <?php if (isset($_GET['message'])) { echo "<div class='alert alert-success'><i class='fas fa-check-circle'></i> ".htmlspecialchars(urldecode($_GET['message']))."</div>"; } ?>

        <form method="post" class="login-form">
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" id="email" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-fingerprint"></i> Place Your Finger Here</label>
                <input type="password" name="password" id="password" placeholder="●●●●●●●●" required>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>

        <div class="login-footer">
            <p>Don't have an account? <a href="register.php">Register</a></p>
        </div>
    </div>
</div>
</body>
</html>