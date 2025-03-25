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

function logAttendanceStatus($message) {
    file_put_contents('attendance_log.txt', date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
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
            $mail->Username   = '';
            $mail->Password   = '';
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
            $mail->Body = '
<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f4f4;">
    <div style="background-color: white; border-radius: 8px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div style="background-color: #4a6cf7; color: white; text-align: center; padding: 15px; border-radius: 8px 8px 0 0;">
            <h1 style="margin: 0; font-size: 24px;">Student Login Alert</h1>
        </div>
        
        <div style="padding: 20px;">
            <p style="margin-bottom: 15px;">Hello,</p>
            <p style="margin-bottom: 15px;">A student has just logged into the AttendEase system. Here are the details:</p>
            
            <div style="background-color: #f9f9f9; border-left: 4px solid #4a6cf7; padding: 15px; margin-top: 20px;">
                <p style="margin: 5px 0;"><strong>Student Name:</strong> ' . htmlspecialchars($studentName) . '</p>
                <p style="margin: 5px 0;"><strong>Email:</strong> ' . htmlspecialchars($studentEmail) . '</p>
                <p style="margin: 5px 0;"><strong>Login Time:</strong> ' . date('Y-m-d H:i:s') . '</p>
            </div>
            
            <p style="margin-top: 20px;">Please log in to the system for more information.</p>
        </div>
        
        <div style="text-align: center; color: #777; margin-top: 20px; font-size: 12px; border-top: 1px solid #eee; padding-top: 15px;">
            <p style="margin: 5px 0;">&copy; ' . date('Y') . ' AttendEase. All rights reserved.</p>
            <p style="margin: 5px 0;">This is an automated notification. Please do not reply.</p>
        </div>
    </div>
</div>
            ';

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

function markAttendance($pdo, $userId): bool
{
    try {
        // Check if attendance already marked for today
        $checkStmt = $pdo->prepare("
            SELECT id FROM attendance 
            WHERE user_id = ? 
            AND DATE(timestamp) = CURDATE()
        ");
        $checkStmt->execute([$userId]);

        if ($checkStmt->fetch()) {
            logAttendanceStatus("Attendance already marked for user ID {$userId} today.");
            return false;
        }

        // Mark attendance
        $stmt = $pdo->prepare("
            INSERT INTO attendance (user_id, status, timestamp) 
            VALUES (?, 'present', NOW())
        ");
        $result = $stmt->execute([$userId]);

        if ($result) {
            logAttendanceStatus("Attendance marked successfully for user ID {$userId}.");
            return true;
        } else {
            logAttendanceStatus("Failed to mark attendance for user ID {$userId}.");
            return false;
        }
    } catch (PDOException $e) {
        logAttendanceStatus("Attendance Error: " . $e->getMessage());
        return false;
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

            // Automatically mark attendance for students
            if ($user['role'] === 'student') {
                $attendanceMarked = markAttendance($pdo, $user['id']);

                // Send email to teachers
                sendEmailToTeachers($pdo, $user['name'], $email);

                // Redirect with attendance status
                $message = $attendanceMarked
                    ? "Login successful. Attendance marked."
                    : "Login successful. Attendance already marked today.";

                header("Location: student/attendance.php?message=" . urlencode($message));
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
            logAttendanceStatus("Login failed for email: {$email}");
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
        logAttendanceStatus("Database error: " . $e->getMessage());
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
<div class="main-container">
    <div class="left">
        <div class="container">
            <div class="login-card">
                <div class="login-header">
                    <h1><i class="fas fa-user-graduate"></i> AttendEase</h1>
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

    </div>
    <div class="right">
        <img src="assets/img/login.svg" alt="" width="1000px">
    </div>
</div>
</body>
</html>