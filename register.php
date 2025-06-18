<?php $page_title = 'Register'; ?>
<?php include 'header.php'; ?>

        <div class="register-container">
            <h2>Create Your Account</h2>
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
// Check for session alert message and display as JavaScript alert
if (isset($_SESSION['alert'])) {
    $alert_type = $_SESSION['alert']['type'];
    $alert_message = $_SESSION['alert']['message'];
    echo "<script>alert(\'" . addslashes($alert_message) . "\');</script>";
    unset($_SESSION['alert']); // Clear the session variable
}
?> 