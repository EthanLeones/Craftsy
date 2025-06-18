<?php
require_once 'includes/session.php';
require_once 'config/database.php';
requireLogin();

$address = null;
$user_id = getCurrentUserId();
$address_id = $_GET['id'] ?? null;

if ($address_id && $user_id) {
    try {
        $conn = getDBConnection();
        // Fetch address for the current user and specific address ID
        $stmt = $conn->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?");
        $stmt->execute([$address_id, $user_id]);
        $address = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching address for edit: " . $e->getMessage());
        // Handle error gracefully
    }
}

$page_title = $address ? 'Edit Address' : 'Address Not Found';
include 'header.php';

?>

        <h1 class="page-title">Edit Address</h1>

        <div class="address-form-container">
            <?php if ($address): ?>
                <form action="update_address.php" method="post">
                    <input type="hidden" name="address_id" value="<?php echo htmlspecialchars($address['id']); ?>">
                    <div class="form-group">
                        <label for="address_line1">Address Line 1:</label>
                        <input type="text" id="address_line1" name="address_line1" value="<?php echo htmlspecialchars($address['address_line1']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="address_line2">Address Line 2 (Optional):</label>
                        <input type="text" id="address_line2" name="address_line2" value="<?php echo htmlspecialchars($address['address_line2']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="city">City:</label>
                        <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($address['city']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="state_province">State/Province:</label>
                        <input type="text" id="state_province" name="state_province" value="<?php echo htmlspecialchars($address['state_province']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="postal_code">Postal Code:</label>
                        <input type="text" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($address['postal_code']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="country">Country:</label>
                        <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($address['country']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="contact_number">Contact Number:</label>
                        <input type="text" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($address['contact_number']); ?>" required>
                    </div>
                     <div class="form-group">
                         <input type="checkbox" id="is_default" name="is_default" value="1" <?php echo $address['is_default'] ? 'checked' : ''; ?>>
                         <label for="is_default">Set as default address</label>
                     </div>
                    <button type="submit">Save Changes</button>
                     <a href="profile.php" class="button secondary">Cancel</a>
                </form>
            <?php else: ?>
                <p style="text-align: center;">Address not found or you do not have permission to edit it.</p>
            <?php endif; ?>
        </div>

</div> <!-- Close container from header.php -->

<?php include 'footer.php'; ?> 