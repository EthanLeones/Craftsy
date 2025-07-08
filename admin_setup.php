<?php
require_once 'config/database.php';

$admin_username = 'nancy2713';
$admin_password = 'craftsynook77'; 
$admin_email = 'admin@craftsynook.com'; 
$admin_name = 'Craftsy Nook Admin'; 

$conn = getDBConnection();

try {
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt_check->execute([$admin_username]);
    $user_exists = $stmt_check->fetchColumn();

    if ($user_exists == 0) {
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
        $stmt_insert = $conn->prepare("INSERT INTO users (username, name, email, password, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt_insert->execute([$admin_username, $admin_name, $admin_email, $hashed_password]);

        echo "Admin user '{$admin_username}' created successfully.\n";
    } else {
        echo "Admin user '{$admin_username}' already exists.\n";
    }

} catch (PDOException $e) {
    error_log("Admin setup error: " . $e->getMessage());
    echo "An error occurred during admin setup.\n" . $e->getMessage();
}

?> 