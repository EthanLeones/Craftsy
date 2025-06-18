<?php $page_title = 'Error'; ?>
<?php include 'header.php'; ?>

        <div class="error-container">
            <h1>Oops! Something went wrong</h1>
            <?php
            $error = $_GET['error'] ?? 'unknown';
            
            switch ($error) {
                case 'empty_fields':
                    echo '<p>Please fill in all required fields.</p>';
                    break;
                case 'invalid_credentials':
                    echo '<p>Invalid username or password.</p>';
                    break;
                case 'password_mismatch':
                    echo '<p>Passwords do not match.</p>';
                    break;
                case 'password_too_short':
                    echo '<p>Password must be at least 8 characters long.</p>';
                    break;
                case 'user_exists':
                    echo '<p>Username or email already exists.</p>';
                    break;
                case 'login_required':
                    echo '<p>Please log in to access this page.</p>';
                    break;
                default:
                    echo '<p>An unexpected error occurred. Please try again later.</p>';
            }
            ?>
            <div class="error-actions">
                <a href="javascript:history.back()" class="button">Go Back</a>
                <a href="index.php" class="button">Go to Homepage</a>
            </div>
        </div>

</div> <!-- Close container from header.php -->

<?php include 'footer.php'; ?> 