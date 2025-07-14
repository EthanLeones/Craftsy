<?php 
$page_title = 'Register';
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
    </script>';
    unset($_SESSION['alert_message']);
    unset($_SESSION['alert_type']);
} else {
    echo '<script>console.log("No session alert message found");</script>';
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
    const name = this.querySelector('#name').value.trim();
    const username = this.querySelector('#username').value.trim();
    const email = this.querySelector('#email').value.trim();
    const password = this.querySelector('#password').value.trim();
    const confirmPassword = this.querySelector('#confirm_password').value.trim();
    
    // Only show toast for client-side validation errors
    if (!name || !username || !email || !password || !confirmPassword) {
        e.preventDefault();
        showToast('Please fill in all fields', 'error');
        return;
    }

    if (name.length < 2) {
        e.preventDefault();
        showToast('Name must be at least 2 characters long', 'error');
        return;
    }

    if (username.length < 3) {
        e.preventDefault();
        showToast('Username must be at least 3 characters long', 'error');
        return;
    }

    // Basic email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        showToast('Please enter a valid email address', 'error');
        return;
    }
    
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

// Add input event listeners for real-time feedback
document.getElementById('name').addEventListener('input', function() {
    this.style.borderColor = this.value.length >= 2 ? '#28a745' : '#dc3545';
});

document.getElementById('username').addEventListener('input', function() {
    this.style.borderColor = this.value.length >= 3 ? '#28a745' : '#dc3545';
});

document.getElementById('email').addEventListener('input', function() {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    this.style.borderColor = emailRegex.test(this.value) ? '#28a745' : '#dc3545';
});

document.getElementById('password').addEventListener('input', function() {
    this.style.borderColor = this.value.length >= 6 ? '#28a745' : '#dc3545';
    
    // Also check confirm password if it has a value
    const confirmPassword = document.getElementById('confirm_password');
    if (confirmPassword.value) {
        confirmPassword.style.borderColor = this.value === confirmPassword.value ? '#28a745' : '#dc3545';
    }
});

document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    this.style.borderColor = this.value === password ? '#28a745' : '#dc3545';
});

if (!document.getElementById('toast-style')) {
    const style = document.createElement('style');
    style.id = 'toast-style';
    style.textContent = `
    .toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        left: auto;
        transform: none;
        min-width: 300px;
        max-width: 450px;
        padding: 14px 24px;
        border-radius: 6px;
        color: #fff;
        font-size: 1rem;
        z-index: 9999;
        opacity: 0;
        pointer-events: none;
        background: #333;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transition: opacity 0.3s, right 0.4s, top 0.3s;
        display: flex !important;
        align-items: center;
        justify-content: space-between;
        word-wrap: break-word;
    }
    .toast.show {
        opacity: 1;
        pointer-events: auto;
        right: 20px;
        top: auto;
    }
    .toast.success { background: #28a745; }
    .toast.error { background: #dc3545; }
    .toast.info { background: #007bff; }
    .toast.warning { background: #f39c12; }
    .toast button {
        background: none !important;
        border: none !important;
        color: inherit !important;
        font-size: 18px !important;
        font-weight: bold !important;
        margin-left: 10px !important;
        cursor: pointer !important;
        padding: 0 5px !important;
        text-transform: none !important;
        letter-spacing: normal !important;
        transition: opacity 0.3s ease !important;
        flex-shrink: 0;
    }
    .toast button:hover {
        opacity: 0.7 !important;
        background: none !important;
        color: inherit !important;
        transform: none !important;
        box-shadow: none !important;
    }
    .toast:not(:first-of-type) {
        bottom: calc(20px + (80px * var(--toast-index, 0)));
    }
    @media (max-width: 600px) {
        .toast {
            left: 50%;
            right: auto;
            transform: translateX(-50%);
            min-width: 90vw;
            max-width: 98vw;
            font-size: 0.95rem;
            padding: 12px 10px;
        }
        .toast.show {
            right: auto;
            left: 50%;
            transform: translateX(-50%);
        }
    }
    `;
    document.head.appendChild(style);
}
</script>

<?php include 'footer.php'; ?>