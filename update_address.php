<?php
require_once 'includes/session.php';
require_once 'config/database.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Invalid request method.'];
    header('Location: profile.php'); 
    exit();
}

$user_id = getCurrentUserId();
$address_id = $_POST['address_id'] ?? null;
$address_line1 = $_POST['address_line1'] ?? '';
$address_line2 = $_POST['address_line2'] ?? '';
$city = $_POST['city'] ?? '';
$state_province = $_POST['state_province'] ?? '';
$postal_code = $_POST['postal_code'] ?? '';
$country = $_POST['country'] ?? '';
$contact_number = $_POST['contact_number'] ?? '';
$is_default = isset($_POST['is_default']) ? 1 : 0;

if ($address_id === null || empty($address_line1) || empty($city) || empty($state_province) || empty($postal_code) || empty($country) || empty($contact_number)) {
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Please fill in all required address fields.'];
    header('Location: profile.php');
    exit();
}

$conn = getDBConnection();

try {
    $stmt_check = $conn->prepare("SELECT id FROM user_addresses WHERE id = ? AND user_id = ?");
    $stmt_check->execute([$address_id, $user_id]);
    if ($stmt_check->rowCount() === 0) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'You do not have permission to edit this address.'];
        header('Location: error.php'); // Or redirect with an error indicating unauthorized access
        exit();
    }

    if ($is_default) {
        $stmt_unset_default = $conn->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ? AND is_default = 1");
        $stmt_unset_default->execute([$user_id]);
    }

    $stmt = $conn->prepare("UPDATE user_addresses SET address_line1 = ?, address_line2 = ?, city = ?, state_province = ?, postal_code = ?, country = ?, contact_number = ?, is_default = ? WHERE id = ?");
    $stmt->execute([$address_line1, $address_line2, $city, $state_province, $postal_code, $country, $contact_number, $is_default, $address_id]);

    $_SESSION['alert'] = ['type' => 'success', 'message' => 'Address updated successfully!'];
    header('Location: profile.php');
    exit();

} catch (PDOException $e) {
    error_log("Update address error: " . $e->getMessage());
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'An unexpected error occurred while updating your address.'];
    header('Location: error.php'); // Redirect to a generic error page
    exit();
}

?> 