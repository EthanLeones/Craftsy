<?php
require_once 'includes/session.php';
$page_title = 'Forgot Password';
include 'header.php';
?>

<div class="forgot-password-container" style=" margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #0001; padding: 32px;">
    <h1 class="page-title">Forgot Password</h1>
    <form id="forgot-email-form">
        <div class="form-group">
            <label for="fp-email">Enter your email address:</label>
            <input type="email" id="fp-email" name="email" required autocomplete="email">
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 10px;">
            <a href="login.php" style="display: inline-block; padding: 10px 22px; background: #e0b1cb; color: #231942; border-radius: 5px; text-decoration: none; font-weight: 600; transition: background 0.2s;">
                &larr; Back to Login
            </a>
            <button type="submit" style="padding: 10px 22px; border-radius: 5px; background: #5E548E; color: #fff; border: none; font-weight: 600;">
                Next &rarr;
            </button>
        </div>
    </form>
    <form id="reset-password-form" style="display:none;">
        <input type="hidden" id="fp-user-id" name="user_id">
        <div class="form-group">
            <label for="fp-new-password">New Password:</label>
            <input type="password" id="fp-new-password" name="new_password" required minlength="6">
        </div>
        <div class="form-group">
            <label for="fp-confirm-password">Confirm Password:</label>
            <input type="password" id="fp-confirm-password" required minlength="6">
        </div>
        <button type="submit">Reset Password</button>
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
    if (!email) return;
    emailForm.querySelector('button').disabled = true;
    fetch('forgot_password_process.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'step=1&email=' + encodeURIComponent(email)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            userIdInput.value = data.user_id;
            emailForm.style.display = 'none';
            resetForm.style.display = 'block';
            showToast('Email found. Please enter your new password.', 'success');
        } else {
            showToast(data.message || 'Email not found.', 'error');
        }
    })
    .catch(() => showToast('Server error.', 'error'))
    .finally(() => { emailForm.querySelector('button').disabled = false; });
});

resetForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const newPassword = newPasswordInput.value;
    const confirmPassword = confirmPasswordInput.value;
    if (newPassword !== confirmPassword) {
        showToast('Passwords do not match.', 'error');
        return;
    }
    resetForm.querySelector('button').disabled = true;
    fetch('forgot_password_process.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'step=2&user_id=' + encodeURIComponent(userIdInput.value) + '&new_password=' + encodeURIComponent(newPassword)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Password reset successful! Redirecting to login...', 'success');
            setTimeout(() => window.location.href = 'login.php', 1800);
        } else {
            showToast(data.message || 'Failed to reset password.', 'error');
        }
    })
    .catch(() => showToast('Server error.', 'error'))
    .finally(() => { resetForm.querySelector('button').disabled = false; });
});
</script>

<style>
.forgot-password-container { box-sizing: border-box; }
.forgot-password-container .form-group { margin-bottom: 18px; }
.forgot-password-container label { display: block; margin-bottom: 6px; font-weight: 500; }
.forgot-password-container input[type="email"],
.forgot-password-container input[type="password"] { width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; }
.forgot-password-container button { padding: 10px 22px; border-radius: 4px; background: #5E548E; color: #fff; border: none; font-weight: 600; }
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