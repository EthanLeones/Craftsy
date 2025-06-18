<?php
require_once '../config/database.php';
require_once '../includes/session.php'; // Assuming session is used for admin login

// Admin authentication check
// requireAdminLogin(); // Implement this function
// if (!isAdmin()) {
//     header('Location: ../login.php');
//     exit();
// }

// It's safer to use POST for deletions, but using GET for simplicity based on typical link usage.
// Consider changing this to POST in a production environment with a form.
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { // Consider changing to POST
    header('Location: inquiries.php');
    exit();
}

$inquiry_id = $_GET['id'] ?? null;

if ($inquiry_id === null || !is_numeric($inquiry_id)) {
    header('Location: inquiries.php?error=delete_invalid_id');
    exit();
}

$inquiry_id = (int)$inquiry_id;

$conn = getDBConnection();

try {
    // Start transaction
    $conn->beginTransaction();

    // Delete inquiry responses first (due to foreign key constraint)
    $stmt_delete_responses = $conn->prepare("DELETE FROM inquiry_responses WHERE inquiry_id = ?");
    $stmt_delete_responses->execute([$inquiry_id]);

    // Delete the inquiry from the database
    $stmt_delete_inquiry = $conn->prepare("DELETE FROM inquiries WHERE id = ?");
    $stmt_delete_inquiry->execute([$inquiry_id]);

    // Check if inquiry was actually deleted
    if ($stmt_delete_inquiry->rowCount() > 0) {
        // Commit the transaction
        $conn->commit();

        // Redirect on success
        header('Location: inquiries.php?success=inquiry_deleted');
        exit();
    } else {
         // No rows affected, inquiry not found or already deleted
         $conn->rollBack();
         header('Location: inquiries.php?error=inquiry_not_found');
         exit();
    }

} catch (PDOException $e) {
    // Roll back the transaction on error
     if ($conn->inTransaction()) {
         $conn->rollBack();
     }
    error_log("Delete inquiry error: " . $e->getMessage());
    header('Location: error.php'); // Redirect to a generic error page
    exit();
}

?> 