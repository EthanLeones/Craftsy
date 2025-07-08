<?php
require_once '../includes/session.php';
require_once '../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}

$admin_id = $_SESSION['user_id'];
$message = trim($_POST['message'] ?? '');
$thread_id = intval($_POST['thread_id'] ?? 0);

if (!$thread_id || $message === '') {
    echo json_encode(['success' => false, 'message' => 'Missing thread or message.']);
    exit();
}

try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO inquiry_messages (thread_id, sender_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$thread_id, $admin_id, $message]);
    $stmt2 = $conn->prepare("UPDATE inquiry_threads SET updated_at = NOW() WHERE id = ?");
    $stmt2->execute([$thread_id]);
    echo json_encode(['success' => true, 'message' => 'Reply sent.']);
} catch (PDOException $e) {
    error_log('Admin chat reply error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error.']);
} 