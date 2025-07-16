<?php
require_once 'includes/session.php';
$page_title = 'Forgot Password';
include 'header.php';
?>

<div class="forgot-password-page-wrapper">
    <h1 class="forgot-password-page-title">Reset Password</h1>

    <form id="forgot-email-form" class="modern-form">
        <div class="modern-form-group">
            <input type="email" id="fp-email" name="email" class="modern-form-input" placeholder="Enter your email address" required autocomplete="email">
        </div>

        <div class="forgot-password-actions">
            <a href="login.php" class="back-to-login-btn">
                &larr; Back to Login
            </a>
            <button type="submit" class="modern-form-btn next-btn">
                Next &rarr;
            </button>
        </div>
    </form>

    <form id="reset-password-form" class="modern-form" style="display:none;">
        <input type="hidden" id="fp-user-id" name="user_id">
        <div class="modern-form-group">
            <input type="password" id="fp-new-password" name="new_password" class="modern-form-input" placeholder="Enter new password" required minlength="6">
        </div>
        <div class="modern-form-group">
            <input type="password" id="fp-confirm-password" class="modern-form-input" placeholder="Confirm new password" required minlength="6">
        </div>
        <button type="submit" class="modern-form-btn">Reset Password</button>
    </form>
</div>

<script>
    const emailForm = document.getElementById('forgot-email-form');
    const resetForm = document.getElementById('reset-password-form');
    const emailInput = document.getElementById('fp-email');
    const userIdInput = document.getElementById('fp-user-id');
    const newPasswordInput = document.getElementById('fp-new-password');
    const confirmPasswordInput = document.getElementById('fp-confirm-password');

    emailForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const email = emailInput.value.trim();

        // Client-side validation with toast
        if (!email) {
            showToast('Please enter your email address.', 'error');
            return;
        }

        // Basic email format validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showToast('Please enter a valid email address.', 'error');
            return;
        }

        emailForm.querySelector('button').disabled = true;
        const submitBtn = emailForm.querySelector('button');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Checking...';

        fetch('forgot_password_process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'step=1&email=' + encodeURIComponent(email)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    userIdInput.value = data.user_id;
                    emailForm.style.display = 'none';
                    resetForm.style.display = 'block';
                    showToast('Email found! Please enter your new password.', 'success');
                } else {
                    // More specific error messages
                    if (data.message) {
                        showToast(data.message, 'error');
                    } else {
                        showToast('Email address not found in our records. Please check and try again.', 'error');
                    }
                }
            })
            .catch(() => showToast('Connection error. Please check your internet connection and try again.', 'error'))
            .finally(() => {
                emailForm.querySelector('button').disabled = false;
                submitBtn.textContent = originalText;
            });
    });

    resetForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        // Enhanced password validation with toast messages
        if (!newPassword || !confirmPassword) {
            showToast('Please fill in both password fields.', 'error');
            return;
        }

        if (newPassword.length < 6) {
            showToast('Password must be at least 6 characters long.', 'error');
            return;
        }

        if (newPassword !== confirmPassword) {
            showToast('Passwords do not match. Please try again.', 'error');
            return;
        }

        resetForm.querySelector('button').disabled = true;
        const resetBtn = resetForm.querySelector('button');
        const originalResetText = resetBtn.textContent;
        resetBtn.textContent = 'Resetting...';

        fetch('forgot_password_process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'step=2&user_id=' + encodeURIComponent(userIdInput.value) + '&new_password=' + encodeURIComponent(newPassword)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Password reset successful! Redirecting to login page...', 'success');
                    setTimeout(() => window.location.href = 'login.php', 2000);
                } else {
                    showToast(data.message || 'Failed to reset password. Please try again.', 'error');
                }
            })
            .catch(() => showToast('Connection error. Please try again.', 'error'))
            .finally(() => {
                resetForm.querySelector('button').disabled = false;
                resetBtn.textContent = originalResetText;
            });
    });
</script>

<style>
    /* Forgot Password Specific Styles */
    .forgot-password-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 40px;
    }

    .back-to-login-btn {
        display: inline-block;
        padding: 18px 32px;
        background: #e0b1cb;
        color: #3f1a41;
        text-decoration: none;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.85rem;
        transition: all 0.4s ease;
        border-radius: 0;
        flex: 1;
        text-align: center;
    }

    .back-to-login-btn:hover {
        background: #d199b8;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(224, 177, 203, 0.3);
    }

    .next-btn {
        flex: 1;
        margin-bottom: 0;
    }

    /* Responsive adjustments */
    @media (max-width: 600px) {
        .forgot-password-actions {
            flex-direction: column;
            gap: 15px;
        }

        .back-to-login-btn,
        .next-btn {
            width: 100%;
            flex: none;
        }
    }
</style>



<?php
if (isset($_SESSION['alert'])) {
    $alert_type = $_SESSION['alert']['type'];
    $alert_message = $_SESSION['alert']['message'];
    echo "<script>alert(\'" . addslashes($alert_message) . "\');</script>";
    unset($_SESSION['alert']); // Clear the session variable
}
?>

<?php include 'footer.php'; ?>