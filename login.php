<?php
$page_title = 'Login';
require_once 'includes/session.php';
include 'header.php';

// Display session alerts as toast notifications
if (isset($_SESSION['alert_message'])) {
    $message = $_SESSION['alert_message'];
    $type = $_SESSION['alert_type'] ?? 'success';

    echo '<script>
        console.log("Session message found:", "' . addslashes($message) . '", "' . $type . '");
        
        window.addEventListener("load", function() {
            console.log("Page loaded, calling showToast");
            if (typeof showToast === "function") {
                console.log("showToast function found, calling it now");
                showToast("' . addslashes($message) . '", "' . $type . '");
            } else {
                console.error("showToast function not found");
                // Fallback alert
                alert("' . addslashes($message) . '");
            }
        });
        
        // Also try immediately
        if (typeof showToast === "function") {
            console.log("Calling showToast immediately");
            showToast("' . addslashes($message) . '", "' . $type . '");
        }
    </script>';
    unset($_SESSION['alert_message']);
    unset($_SESSION['alert_type']);
} else {
    echo '<script>console.log("No session alert message found");</script>';
}
?>

<div class="login-page-wrapper">
    <h1 class="login-page-title">Welcome Back</h1>


    <form action="login_process.php" method="post" class="modern-form">
        <div class="modern-form-group">
            <input type="text" id="username" name="username" class="modern-form-input" placeholder="Enter your username" required>
        </div>

        <div class="modern-form-group">
            <input type="password" id="password" name="password" class="modern-form-input" placeholder="Enter your password" required>
        </div>

        <button type="submit" name="login" class="modern-form-btn">Sign In</button>

        <div class="modern-form-links">
            <p><a href="forgot_password.php">Forgot Password?</a></p>
            <p>Don't have an account? <a href="register.php">Create Account</a></p>
        </div>
    </form>
</div>

<script>
    // Auto-focus on first input
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('username').focus();
    });

    // Form validation and enhanced UX
    document.querySelector('.modern-form').addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('.modern-form-btn');
        const username = this.querySelector('#username').value.trim();
        const password = this.querySelector('#password').value.trim();

        if (!username || !password) {
            e.preventDefault();
            showToast('Please fill in all fields', 'error');
            return;
        }

        if (username.length < 3) {
            e.preventDefault();
            showToast('Username must be at least 3 characters long', 'error');
            return;
        }

        if (password.length < 6) {
            e.preventDefault();
            showToast('Password must be at least 6 characters long', 'error');
            return;
        }

        // Add loading state
        submitBtn.disabled = true;
        submitBtn.textContent = 'Signing In...';
        submitBtn.style.opacity = '0.7';

        // Show loading toast
        showToast('Attempting to sign in...', 'info', 2000);
    });

    // Add input event listeners for real-time feedback
    document.getElementById('username').addEventListener('input', function() {
        this.style.borderColor = this.value.length >= 3 ? '#28a745' : '#dc3545';
    });

    document.getElementById('password').addEventListener('input', function() {
        this.style.borderColor = this.value.length >= 6 ? '#28a745' : '#dc3545';
    });
</script>

<?php include 'footer.php'; ?>