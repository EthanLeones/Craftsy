<?php
require_once '../includes/session.php';

// Admin authentication check
// requireAdminLogin(); // Assuming a function to check if admin is logged in
// if (!isAdmin()) { // Assuming an isAdmin() function
//     header('Location: ../login.php'); // Redirect non-admins
//     exit();
// }

// Basic admin check based on session variable set in login_process.php
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Redirect to login page if not logged in as admin
    header('Location: ../login.php?error=unauthorized_admin');
    exit();
}

// You can optionally fetch admin user details here if needed for display
// $admin_user = getCurrentUser(); // Assuming getCurrentUser() fetches user details

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Dashboard'; ?> | Craftsy Nook Admin</title>
    <link rel="stylesheet" href="../css/style.css"> <!-- Link to front-end styles for basic resets/common elements -->
    <link rel="stylesheet" href="../css/admin.css"> <!-- Link to dedicated admin styles -->
    <!-- Add any other head elements like favicons, fonts here -->
</head>
<body>
    <?php
    // Basic admin authentication check should be placed here or before including this header
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        // Redirect to login page if not logged in as admin
        header('Location: ../login.php?error=unauthorized_admin');
        exit();
    }
    ?>

    <div class="admin-wrapper">
        <!-- Sidebar will be included here -->
        <div class="admin-main-content">
            <!-- Page content will go here -->
        </div>
    </div>

<?php
// Check for session alert message and display as JavaScript alert
if (isset($_SESSION['alert'])) {
    $alert_type = $_SESSION['alert']['type'];
    $alert_message = $_SESSION['alert']['message'];
    echo "<script>if (typeof showToast === 'function') { showToast('" . addslashes($alert_message) . "', '" . ($alert_type === 'success' ? 'success' : 'error') . "'); }</script>";
    unset($_SESSION['alert']); // Clear the session variable
}
?>
</body>
</html> 