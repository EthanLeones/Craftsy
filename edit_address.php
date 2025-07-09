<?php
require_once 'includes/session.php';
require_once 'config/database.php';
requireLogin();

$page_title = 'Edit Address';
include 'header.php';

$address = null;
$user_id = getCurrentUserId();
$address_id = $_POST['id'] ?? null;

// Only allow POST access
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$address_id) {
    echo '<p style="text-align: center; color: red;">Invalid access. Please return to your profile.</p>';
    include 'footer.php';
    exit();
}

try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$address_id, $user_id]);
    $address = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching address for edit: " . $e->getMessage());
}
?>

<h1 class="page-title">Edit Address</h1>

<div class="form-container" style="max-width: 600px; margin: 0 auto; background-color: #f9f9f9; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
    <?php if ($address): ?>
        <form action="update_address.php" method="post">
            <input type="hidden" name="address_id" value="<?= htmlspecialchars($address['id']) ?>">

            <div class="form-group">
                <label for="address_line1">Address Line 1</label>
                <input type="text" id="address_line1" name="address_line1" value="<?= htmlspecialchars($address['address_line1']) ?>" required>
            </div>

            <div class="form-group">
                <label for="address_line2">Address Line 2 <span style="color: #888;">(Optional)</span></label>
                <input type="text" id="address_line2" name="address_line2" value="<?= htmlspecialchars($address['address_line2']) ?>">
            </div>

            <div class="form-group">
                <label for="city">City</label>
                <input type="text" id="city" name="city" value="<?= htmlspecialchars($address['city']) ?>" required>
            </div>

            <div class="form-group">
                <label for="state_province">State/Province</label>
                <input type="text" id="state_province" name="state_province" value="<?= htmlspecialchars($address['state_province']) ?>" required>
            </div>

            <div class="form-group">
                <label for="postal_code">Postal Code</label>
                <input type="text" id="postal_code" name="postal_code" value="<?= htmlspecialchars($address['postal_code']) ?>" required>
            </div>

            <div class="form-group">
                <label for="country">Country</label>
                <input type="text" id="country" name="country" value="<?= htmlspecialchars($address['country']) ?>" required>
            </div>

            <div class="form-group">
                <label for="contact_number">Contact Number</label>
                <input type="text" id="contact_number" name="contact_number" value="<?= htmlspecialchars($address['contact_number']) ?>" required>
            </div>

            <div class="form-group checkbox-group">
                <input type="checkbox" id="is_default" name="is_default" value="1" <?= $address['is_default'] ? 'checked' : '' ?>>
                <label for="is_default">Set as default address</label>
            </div>

            <div class="form-actions" style="margin-top: 20px;">
                <button type="submit" class="button">Save Changes</button>
                <a href="profile.php" class="button secondary">Cancel</a>
            </div>
        </form>
    <?php else: ?>
        <p style="text-align: center; color: red;">Address not found or you do not have permission to edit it.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
