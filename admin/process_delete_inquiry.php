<?php
require_once '../config/database.php';
require_once '../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: inquiries.php');
    exit();
}

$inquiry_id = $_POST['id'] ?? null;

if ($inquiry_id === null || !is_numeric($inquiry_id)) {
    header('Location: inquiries.php?error=delete_invalid_id');
    exit();
}

$inquiry_id = (int)$inquiry_id;

$conn = getDBConnection();

try {
    $conn->beginTransaction();

    $stmt_delete_responses = $conn->prepare("DELETE FROM inquiry_responses WHERE inquiry_id = ?");
    $stmt_delete_responses->execute([$inquiry_id]);

    $stmt_delete_inquiry = $conn->prepare("DELETE FROM inquiries WHERE id = ?");
    $stmt_delete_inquiry->execute([$inquiry_id]);

    if ($stmt_delete_inquiry->rowCount() > 0) {
        $conn->commit();
        header('Location: inquiries.php?success=inquiry_deleted');
        exit();
    } else {
        $conn->rollBack();
        header('Location: inquiries.php?error=inquiry_not_found');
        exit();
    }

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Delete inquiry error: " . $e->getMessage());
    header('Location: error.php');
    exit();
}

?>