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

        // Only show toast for client-side validation errors
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
    });

    // Add input event listeners for real-time feedback
    document.getElementById('username').addEventListener('input', function() {
        this.style.borderColor = this.value.length >= 3 ? '#28a745' : '#dc3545';
    });

    document.getElementById('password').addEventListener('input', function() {
        this.style.borderColor = this.value.length >= 6 ? '#28a745' : '#dc3545';
    });

    document.querySelector('.toast')

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