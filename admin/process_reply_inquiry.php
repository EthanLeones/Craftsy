<?php
require_once '../config/database.php';
require_once '../includes/session.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$inquiry_id = $_POST['inquiry_id'] ?? null;
$reply_text = trim($_POST['reply_text'] ?? '');

if ($inquiry_id === null || !is_numeric($inquiry_id) || empty($reply_text)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit();
}

$inquiry_id = (int)$inquiry_id;
$responded_by = $admin_name ?? 'Admin';

$conn = getDBConnection();

try {
    $conn->beginTransaction();

    $stmt_insert_reply = $conn->prepare("INSERT INTO inquiry_responses (inquiry_id, response_text, responded_by, responded_at) VALUES (?, ?, ?, NOW())");
    $stmt_insert_reply->execute([$inquiry_id, $reply_text, $responded_by]);

    $stmt_update_status = $conn->prepare("UPDATE inquiries SET status = 'replied' WHERE id = ?");
    $stmt_update_status->execute([$inquiry_id]);

    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Reply sent successfully!']);

} catch (PDOException $e) {
     if ($conn->inTransaction()) {
         $conn->rollBack();
     }
    error_log("Process reply inquiry error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

?>