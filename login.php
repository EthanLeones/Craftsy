<?php 
$page_title = 'Login';
require_once 'includes/session.php';
include 'header.php'; 

// Display session alerts as toast notifications
if (isset($_SESSION['alert_message'])) {
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            showToast("' . addslashes($_SESSION['alert_message']) . '", "' . ($_SESSION['alert_type'] ?? 'success') . '");
        });
    </script>';
    unset($_SESSION['alert_message']);
    unset($_SESSION['alert_type']);
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
    
    // Add loading state
    submitBtn.disabled = true;
    submitBtn.textContent = 'Signing In...';
    submitBtn.style.opacity = '0.7';
});
</script>

<?php include 'footer.php'; ?>