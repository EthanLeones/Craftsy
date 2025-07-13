<?php 
$page_title = 'Register';
require_once 'includes/session.php';
include 'header.php'; 

// Display session alerts as toast notifications
if (isset($_SESSION['alert'])) {
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            showToast("' . addslashes($_SESSION['alert']['message']) . '", "' . ($_SESSION['alert']['type'] ?? 'error') . '");
        });
    </script>';
    unset($_SESSION['alert']);
}
?>

<div class="login-page-wrapper">
    <h1 class="login-page-title">Create Your Account</h1>
    
    <form action="register_process.php" method="post" class="modern-form">
        <div class="modern-form-group">
            <input type="text" id="name" name="name" class="modern-form-input" placeholder="Enter your full name" required>
        </div>
        
        <div class="modern-form-group">
            <input type="text" id="username" name="username" class="modern-form-input" placeholder="Choose a username" required>
        </div>
        
        <div class="modern-form-group">
            <input type="email" id="email" name="email" class="modern-form-input" placeholder="Enter your email" required>
        </div>
        
        <div class="modern-form-group">
            <input type="password" id="password" name="password" class="modern-form-input" placeholder="Enter your password" required>
        </div>
        
        <div class="modern-form-group">
            <input type="password" id="confirm_password" name="confirm_password" class="modern-form-input" placeholder="Confirm your password" required>
        </div>
        
        <button type="submit" name="register" class="modern-form-btn">Create Account</button>
        
        <div class="modern-form-links">
            <p>Already have an account? <a href="login.php">Sign In</a></p>
        </div>
    </form>
</div>

<script>
// Auto-focus on first input
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('name').focus();
});

// Form validation and enhanced UX
document.querySelector('.modern-form').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('.modern-form-btn');
    const password = this.querySelector('#password').value.trim();
    const confirmPassword = this.querySelector('#confirm_password').value.trim();
    
    // Validate password length
    if (password.length < 6) {
        e.preventDefault();
        showToast('Password must be at least 6 characters long', 'error');
        return;
    }
    
    // Validate password match
    if (password !== confirmPassword) {
        e.preventDefault();
        showToast('Passwords do not match', 'error');
        return;
    }
    
    // Add loading state
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creating Account...';
    submitBtn.style.opacity = '0.7';
});
</script>

<?php include 'footer.php'; ?>