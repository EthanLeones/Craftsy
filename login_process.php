<?php
require_once 'config/database.php';
require_once 'includes/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $_SESSION['alert_message'] = 'Username and password cannot be empty.';
        $_SESSION['alert_type'] = 'error';
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
                $_SESSION['alert_message'] = 'Welcome back, Admin!';
                $_SESSION['alert_type'] = 'success';
            } else {
                $_SESSION['is_admin'] = false;
                $_SESSION['alert_message'] = 'Welcome back, ' . $user['name'] . '!';
                $_SESSION['alert_type'] = 'success';
            }


            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit();
        } else {
            error_log("Login failed for username: " . $username);

            if ($user) {
                $_SESSION['alert_message'] = 'Incorrect password. Please try again.';
            } else {
                $_SESSION['alert_message'] = 'Username not found. Please check your username or register for a new account.';
            }
            $_SESSION['alert_type'] = 'error';
            header('Location: login.php');
            exit();
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['alert_message'] = 'An unexpected error occurred during login.';
        $_SESSION['alert_type'] = 'error';
        header('Location: error.php');
        exit();
    }
} else {
    $_SESSION['alert_message'] = 'Invalid request method.';
    $_SESSION['alert_type'] = 'error';
    header('Location: login.php');
    exit();
}
