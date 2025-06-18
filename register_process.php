<?php
require_once 'config/database.php';
require_once 'includes/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic validation
    if (empty($name) || empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'All fields are required.'];
        header('Location: register.php');
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Passwords do not match.'];
        header('Location: register.php');
        exit();
    }

    if (strlen($password) < 6) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Password must be at least 6 characters long.'];
        header('Location: register.php');
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
         $_SESSION['alert'] = ['type' => 'error', 'message' => 'Invalid email format.'];
        header('Location: register.php');
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $conn = getDBConnection();

        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
             $_SESSION['alert'] = ['type' => 'error', 'message' => 'Username or email already exists.'];
            header('Location: register.php');
            exit();
        }

        // Insert new user
        // Default role to 'customer' to match database schema
        $stmt = $conn->prepare("INSERT INTO users (name, username, email, password, role) VALUES (?, ?, ?, ?, 'customer')");
        $stmt->execute([$name, $username, $email, $hashed_password]);
        
        // Log successful registration
        error_log("User registered successfully: " . $username);

        // Registration successful - redirect to login
         $_SESSION['alert'] = ['type' => 'success', 'message' => 'Registration successful! Please login.'];
        header('Location: login.php');
        exit();

    } catch (PDOException $e) {
        // Log detailed error information
        error_log("Registration error details: " . $e->getMessage());
        error_log("SQL State: " . $e->getCode());
        error_log("Error Info: " . print_r($e->errorInfo, true));
        
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'An unexpected error occurred during registration.'];
        header('Location: error.php'); // Redirect to a generic error page for database errors
        exit();
    }

} else {
    // Invalid request method
     $_SESSION['alert'] = ['type' => 'error', 'message' => 'Invalid request method.'];
    header('Location: register.php');
    exit();
} 