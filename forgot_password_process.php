<?php
require_once 'config/database.php';
require_once 'includes/session.php';
header('Content-Type: application/json');

$email = trim($_POST['email'] ?? '');
$new_password = $_POST['new_password'] ?? '';
$step = $_POST['step'] ?? '1';

try {
    $conn = getDBConnection();
    if ($step === '1') {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            echo json_encode(['success' => true, 'user_id' => $user['id']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Email not found.']);
        }
    } elseif ($step === '2') {
        $user_id = intval($_POST['user_id'] ?? 0);
        if (!$user_id || !$new_password) {
            echo json_encode(['success' => false, 'message' => 'Missing user or password.']);
            exit();
        }
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $user_id]);
        echo json_encode(['success' => true, 'message' => 'Password updated.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid step.']);
    }
} catch (PDOException $e) {
    error_log('Forgot password error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error.']);
} 