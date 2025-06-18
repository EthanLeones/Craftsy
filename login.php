<?php $page_title = 'Login'; ?>
<?php include 'header.php'; ?>

        <div class="login-container">
            <h2>Login to Your Account</h2>
            <form action="login_process.php" method="post">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" name="login">Login</button>
            </form>
            <p class="forgot-password-link"><a href="forgot_password.php">Forgot Password?</a></p>
            <p class="register-link">Don't have an account? <a href="register.php">Register here</a></p>
        </div>

</div> <!-- Close container from header.php -->

<?php include 'footer.php'; ?>

<script>
<?php
if (isset($_SESSION['alert'])) {
    $alert_type = $_SESSION['alert']['type'];
    $alert_message = $_SESSION['alert']['message'];
    echo "showToast('" . addslashes($alert_message) . "', '" . ($alert_type === 'success' ? 'success' : 'error') . "');";
    unset($_SESSION['alert']);
}
?>
</script>
