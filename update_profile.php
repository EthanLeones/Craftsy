<?php
require_once 'includes/session.php';
require_once 'config/database.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Invalid request method.'];
    header('Location: profile.php');
    exit();
}

$user_id = getCurrentUserId();
$submitted_user_id = $_POST['user_id'] ?? null;
$username = $_POST['username'] ?? '';
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_new_password = $_POST['confirm_new_password'] ?? '';

// Ensure the user is only updating their own profile
if ($user_id === null || $submitted_user_id === null || (int)$user_id !== (int)$submitted_user_id) {
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'You are not authorized to perform this action.'];
    header('Location: error.php'); // Or redirect to login with an error
    exit();
}

$conn = getDBConnection();

try {
    // Start building the update query and parameters
    $update_fields = [];
    $update_params = [];

    // Update username, name, email if provided and different from current
    // (You might want more robust validation and checks for unique username/email here)
    $stmt_check = $conn->prepare("SELECT username, name, email, password FROM users WHERE id = ?");
    $stmt_check->execute([$user_id]);
    $current_user_data = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if ($username !== $current_user_data['username']) {
        // Check if new username already exists
        $stmt_user_check = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
        $stmt_user_check->execute([$username, $user_id]);
        if ($stmt_user_check->fetchColumn() > 0) {
             $_SESSION['alert'] = ['type' => 'error', 'message' => 'Username already taken.'];
             header('Location: profile.php');
             exit();
        }
        $update_fields[] = 'username = ?';
        $update_params[] = $username;
    }

     if ($name !== $current_user_data['name']) {
        $update_fields[] = 'name = ?';
        $update_params[] = $name;
    }

    if ($email !== $current_user_data['email']) {
        // Check if new email already exists
        $stmt_email_check = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
        $stmt_email_check->execute([$email, $user_id]);
        if ($stmt_email_check->fetchColumn() > 0) {
             $_SESSION['alert'] = ['type' => 'error', 'message' => 'Email address already in use.'];
             header('Location: profile.php');
             exit();
        }
         // Optional: Basic email format validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
             $_SESSION['alert'] = ['type' => 'error', 'message' => 'Invalid email format.'];
             header('Location: profile.php');
             exit();
        }
        $update_fields[] = 'email = ?';
        $update_params[] = $email;
    }

    // Handle password change
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_new_password)) {
        // Verify current password
        if (!password_verify($current_password, $current_user_data['password'])) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Incorrect current password.'];
            header('Location: profile.php');
            exit();
        }

        // Validate new password
        if (empty($new_password) || empty($confirm_new_password)) {
             $_SESSION['alert'] = ['type' => 'error', 'message' => 'New password and confirmation are required.'];
             header('Location: profile.php');
             exit();
        }
        if ($new_password !== $confirm_new_password) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'New passwords do not match.'];
            header('Location: profile.php');
            exit();
        }
        if (strlen($new_password) < 6) { // Changed from 8 to 6 for consistency with register
             $_SESSION['alert'] = ['type' => 'error', 'message' => 'New password must be at least 6 characters long.'];
             header('Location: profile.php');
             exit();
        }

        // Hash and add new password to update fields
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_fields[] = 'password = ?';
        $update_params[] = $hashed_password;
    }

    // If there are fields to update, build and execute the query
    if (!empty($update_fields)) {
        $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";
        $update_params[] = $user_id;

        $stmt = $conn->prepare($sql);
        $stmt->execute($update_params);
        
        // Update session username if it changed
        if ($username !== $current_user_data['username']) {
            $_SESSION['username'] = $username;
        }

        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Profile updated successfully!'];
        header('Location: profile.php');
        exit();

    } else {
        // No fields to update
        $_SESSION['alert'] = ['type' => 'info', 'message' => 'No changes were submitted.'];
        header('Location: profile.php');
        exit();
    }

} catch (PDOException $e) {
    error_log("Update profile error: " . $e->getMessage());
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'An unexpected error occurred while updating your profile.'];
    header('Location: error.php'); // Redirect to a generic error page
    exit();
}

?> 