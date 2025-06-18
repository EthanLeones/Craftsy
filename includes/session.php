<?php
session_start();

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Function to get current username
function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

// Function to set user session
function setUserSession($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
}

// Function to clear user session
function clearUserSession() {
    session_unset();
    session_destroy();
}

// Function to require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Function to get cart count
function getCartCount() {
    if (!isLoggedIn()) {
        return 0;
    }
    
    require_once 'config/database.php';
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
    $stmt->execute([getCurrentUserId()]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['count'] ?? 0;
} 