<?php
require_once 'includes/session.php';
require_once 'config/database.php';
requireLogin();

// Should ideally be a POST request for deletion, but using GET for simplicity in this example link
// In a real application, use a form with POST method for deletion
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { // Consider changing to POST in production
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Invalid request method.'];
    header('Location: profile.php');
    exit();
}

$user_id = getCurrentUserId();
$address_id = $_GET['id'] ?? null;

if ($address_id === null) {
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Missing address ID.'];
    header('Location: profile.php');
    exit();
}

$conn = getDBConnection();

try {
    // Verify that the address belongs to the current user before deleting
    $stmt_check = $conn->prepare("SELECT id FROM user_addresses WHERE id = ? AND user_id = ?");
    $stmt_check->execute([$address_id, $user_id]);
    if ($stmt_check->rowCount() === 0) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'You do not have permission to delete this address.'];
        header('Location: error.php'); // Or redirect with an error indicating unauthorized access
        exit();
    }

    // Delete the address
    $stmt = $conn->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$address_id, $user_id]);

    $_SESSION['alert'] = ['type' => 'success', 'message' => 'Address deleted successfully!'];
    header('Location: profile.php');
    exit();

} catch (PDOException $e) {
    error_log("Delete address error: " . $e->getMessage());
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'An unexpected error occurred while deleting your address.'];
    header('Location: error.php'); // Redirect to a generic error page
    exit();
}

?> 