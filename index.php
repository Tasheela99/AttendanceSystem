<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin/dashboard.php");
        exit;
    } elseif ($_SESSION['user_role'] === 'student') {
        header("Location: student/attendance.php");
        exit;
    } elseif ($_SESSION['user_role'] === 'teacher') {
        header("Location: teacher/teacher_dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Attendance Portal</title>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="navbar">
    <div class="logo">
        <i class="fas fa-graduation-cap"></i>
        <span>AttendEase</span>
    </div>
    <div class="nav-links">
        <a href="login.php" class="nav-button"><i class="fas fa-sign-in-alt"></i> Login</a>
        <a href="register.php" class="nav-button accent"><i class="fas fa-user-plus"></i> Register</a>
    </div>
</div>

<div class="hero-section">
    <div class="hero-content">
        <h1>Student Attendance Made Simple</h1>
        <p>Track attendance, manage classes, and generate reports with our easy-to-use platform.</p>
        <div class="cta-buttons">
            <a href="login.php" class="cta-button primary">
                <i class="fas fa-sign-in-alt"></i> Get Started
            </a>
            <a href="#features" class="cta-button secondary">
                <i class="fas fa-info-circle"></i> Learn More
            </a>
        </div>
    </div>
    <div class="hero-image">
        <img src="assets/img/attendance-image.svg" alt="Attendance System"
             onerror="this.onerror=null; this.src='data:image/svg+xml;charset=utf-8,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 400 300\' fill=\'%234a6cf7\'%3E%3Cpath d=\'M195.5 29c45.3 0 82 36.7 82 82s-36.7 82-82 82-82-36.7-82-82 36.7-82 82-82zm18.5 29.5h-35c-5.5 0-10 4.5-10 10v87c0 5.5 4.5 10 10 10h73c5.5 0 10-4.5 10-10v-49c0-2.7-1.1-5.3-3-7.2l-38-38c-1.9-1.9-4.4-3-7-3zm-30 20h20v30h30v40h-50v-70z\'/%3E%3C/svg%3E';">
    </div>
</div>

<div class="features-section" id="features">
    <h2>Why Choose Our Attendance System?</h2>
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-fingerprint"></i></div>
            <h3>Biometric Authentication</h3>
            <p>Secure fingerprint-based authentication ensures accurate attendance records.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-chart-pie"></i></div>
            <h3>Detailed Analytics</h3>
            <p>Generate comprehensive reports and analytics on attendance patterns.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-bell"></i></div>
            <h3>Automated Notifications</h3>
            <p>Instant alerts for absences, keeping everyone informed in real-time.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
            <h3>Mobile Friendly</h3>
            <p>Access attendance records from anywhere, on any device, at any time.</p>
        </div>
    </div>
</div>

<div class="testimonials-section">
    <h2>What Our Users Say</h2>
    <div class="testimonials-container">
        <div class="testimonial-card">
            <div class="quote"><i class="fas fa-quote-left"></i></div>
            <p>"This attendance system has revolutionized how we track student presence. It's intuitive and saves us hours each week."</p>
            <div class="testimonial-author">
                <div class="testimonial-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="author-info">
                    <h4>Sarah Johnson</h4>
                    <p>School Administrator</p>
                </div>
            </div>
        </div>
        <div class="testimonial-card">
            <div class="quote"><i class="fas fa-quote-left"></i></div>
            <p>"As a teacher, I love how easy it is to track attendance and generate reports. The biometric feature ensures accuracy."</p>
            <div class="testimonial-author">
                <div class="testimonial-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="author-info">
                    <h4>Michael Chen</h4>
                    <p>Mathematics Teacher</p>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>AttendEase</h3>
            <p>Making attendance tracking simple, secure, and efficient for educational institutions.</p>
        </div>
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
                <li><a href="#features">Features</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Contact Us</h3>
            <p><i class="fas fa-envelope"></i> support@attendease.com</p>
            <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> AttendEase. All rights reserved.</p>
    </div>
</footer>

<?php if (isset($_GET['message'])): ?>
    <div class="toast-message">
        <i class="fas fa-check-circle"></i>
        <span><?php echo htmlspecialchars(urldecode($_GET['message'])); ?></span>
        <button class="toast-close" onclick="this.parentElement.style.display='none';">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <script>
        setTimeout(function() {
            document.querySelector('.toast-message').style.display = 'none';
        }, 5000);
    </script>
<?php endif; ?>
</body>
</html>