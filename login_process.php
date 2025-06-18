<?php
require_once 'config/database.php';
require_once 'includes/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Username and password cannot be empty.'];
        header('Location: login.php');
        exit();
    }
    
    try {
        $conn = getDBConnection();
        
        // Get user from database
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Set user session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_name'] = $user['name']; // Assuming 'name' column exists
            $_SESSION['user_role'] = $user['role']; // Store the user's role
            
            // Log successful login
            error_log("User logged in successfully: " . $user['username']);

            // Check if the logged-in user is the admin (based on role)
            if (isset($user['role']) && $user['role'] === 'admin') {
                $_SESSION['is_admin'] = true;
            } else {
                $_SESSION['is_admin'] = false;
            }
            
            // Set success message
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Login successful!'];

            // Redirect based on role
            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit();
        } else {
            // Invalid credentials
            error_log("Login failed for username: " . $username);
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Invalid username or password.'];
            header('Location: login.php');
            exit();
        }
    } catch (PDOException $e) {
        // Log error and redirect to error page
        error_log("Login error: " . $e->getMessage());
         $_SESSION['alert'] = ['type' => 'error', 'message' => 'An unexpected error occurred during login.'];
        header('Location: error.php'); // Redirect to a generic error page for database errors
        exit();
    }
} else {
    // Invalid request method
     $_SESSION['alert'] = ['type' => 'error', 'message' => 'Invalid request method.'];
    header('Location: login.php');
    exit();
}
