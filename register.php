<?php $page_title = 'Register'; ?>
<?php include 'header.php'; ?>

<div class="register-container">
    <h2>Create Your Account</h2>
    <div id="register-error-message" style="display:none; background: #ffe0e0; color: #a94442; border: 1px solid #f5c6cb; padding: 12px 18px; border-radius: 6px; margin-bottom: 18px; font-weight: 500;"></div>
    <form action="register_process.php" method="post">
        <div class="form-group">
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" name="register">Register</button>
        
    </form>
    <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
</div>

</div> <!-- Close container from header.php -->

<?php include 'footer.php'; ?>

<?php
if (isset($_SESSION['alert'])) {
    $alert_type = $_SESSION['alert']['type'];
    $alert_message = $_SESSION['alert']['message'];
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            var msgDiv = document.getElementById('register-error-message');
            if (msgDiv) {
                msgDiv.textContent = '" . addslashes($alert_message) . "';
                msgDiv.style.display = 'block';
            }
        });
    </script>";
    unset($_SESSION['alert']);
}
?> 
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action="register_process.php"]');
    const errorDiv = document.getElementById('register-error-message');
    form.addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('confirm_password').value;
        if (password.length < 6) {
            errorDiv.textContent = 'Password must be at least 6 characters long.';
            errorDiv.style.display = 'block';
            e.preventDefault();
            return false;
        }
        if (password !== confirm) {
            errorDiv.textContent = 'Passwords do not match.';
            errorDiv.style.display = 'block';
            e.preventDefault();
            return false;
        }
        errorDiv.style.display = 'none';
    });
});
</script>