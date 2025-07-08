<?php
require_once 'includes/session.php';
require_once 'config/database.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to send a message.']);
    exit();
}

$user_id = getCurrentUserId();
$message = trim($_POST['message'] ?? '');
$thread_id = intval($_POST['thread_id'] ?? 0);

if (!$thread_id || $message === '') {
    echo json_encode(['success' => false, 'message' => 'Missing thread or message.']);
    exit();
}

try {
    $conn = getDBConnection();

    $stmt = $conn->prepare("INSERT INTO inquiry_messages (thread_id, sender_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$thread_id, $user_id, $message]);

    $stmt2 = $conn->prepare("UPDATE inquiry_threads SET updated_at = NOW() WHERE id = ?");
    $stmt2->execute([$thread_id]);
    echo json_encode(['success' => true, 'message' => 'Message sent.']);
} catch (PDOException $e) {
    error_log('Contact chat error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}

?> 