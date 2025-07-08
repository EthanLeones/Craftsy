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
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_name'] = $user['name']; 
            $_SESSION['user_role'] = $user['role']; 
            
            error_log("User logged in successfully: " . $user['username']);

            if (isset($user['role']) && $user['role'] === 'admin') {
                $_SESSION['is_admin'] = true;
            } else {
                $_SESSION['is_admin'] = false;
            }
            

            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Login successful!'];

            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit();
        } else {
            error_log("Login failed for username: " . $username);
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Invalid username or password.'];
            header('Location: login.php');
            exit();
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
         $_SESSION['alert'] = ['type' => 'error', 'message' => 'An unexpected error occurred during login.'];
        header('Location: error.php'); // Redirect to a generic error page for database errors
        exit();
    }
} else {
     $_SESSION['alert'] = ['type' => 'error', 'message' => 'Invalid request method.'];
    header('Location: login.php');
    exit();
}
