<?php
require_once 'includes/session.php';
require_once 'config/database.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Invalid request method.'];
    header('Location: profile.php'); // Redirect if not a POST request
    exit();
}

$user_id = getCurrentUserId();
$address_line1 = $_POST['address_line1'] ?? '';
$address_line2 = $_POST['address_line2'] ?? '';
$city = $_POST['city'] ?? '';
$state_province = $_POST['state_province'] ?? '';
$postal_code = $_POST['postal_code'] ?? '';
$country = $_POST['country'] ?? '';
$contact_number = $_POST['contact_number'] ?? '';
$is_default = isset($_POST['is_default']) ? 1 : 0;

// Basic validation
if (empty($address_line1) || empty($city) || empty($state_province) || empty($postal_code) || empty($country) || empty($contact_number)) {
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Please fill in all required address fields.'];
    header('Location: profile.php');
    exit();
}

$conn = getDBConnection();

try {
    // If setting as default, unset the current default address for this user
    if ($is_default) {
        $stmt_unset_default = $conn->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ? AND is_default = 1");
        $stmt_unset_default->execute([$user_id]);
    }

    // Insert the new address
    $stmt = $conn->prepare("INSERT INTO user_addresses (user_id, address_line1, address_line2, city, state_province, postal_code, country, contact_number, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $address_line1, $address_line2, $city, $state_province, $postal_code, $country, $contact_number, $is_default]);

    $_SESSION['alert'] = ['type' => 'success', 'message' => 'Address added successfully!'];
    header('Location: profile.php');
    exit();

} catch (PDOException $e) {
    error_log("Add address error: " . $e->getMessage());
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'An unexpected error occurred while adding your address.'];
    header('Location: error.php'); // Redirect to a generic error page
    exit();
}

?> 