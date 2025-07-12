<?php
require_once 'includes/session.php';
require_once 'config/database.php';
requireLogin();
$page_title = 'My Profile';
include 'header.php';

$user = null;
$user_addresses = [];
$user_id = getCurrentUserId();

if ($user_id) {
    try {
        $conn = getDBConnection();
        
        $stmt = $conn->prepare("SELECT id, username, name, email FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt_addresses = $conn->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
        $stmt_addresses->execute([$user_id]);
        $user_addresses = $stmt_addresses->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Error fetching user profile or addresses: " . $e->getMessage());
    }
}

?>

        <div class="profile-page-wrapper">
            <h1 class="profile-page-title">My Profile</h1>

            <?php if ($user): ?>
                <div class="profile-main-container">
                    <form action="update_profile.php" method="post" class="profile-form">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                        
                        <div class="profile-form-grid">
                            <!-- Personal Information Section -->
                            <div class="profile-form-section">
                                <h2 class="profile-section-title">Personal Information</h2>
                                <div class="profile-form-group">
                                    <label for="username">Username</label>
                                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                </div>
                                <div class="profile-form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                                <div class="profile-form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                            </div>

                            <!-- Password Change Section -->
                            <div class="profile-form-section">
                                <h2 class="profile-section-title">Change Password</h2>
                                <div class="profile-form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" id="current_password" name="current_password">
                                </div>
                                <div class="profile-form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" id="new_password" name="new_password">
                                </div>
                                <div class="profile-form-group">
                                    <label for="confirm_new_password">Confirm New Password</label>
                                    <input type="password" id="confirm_new_password" name="confirm_new_password">
                                </div>
                            </div>
                        </div>

                        <div class="profile-form-actions">
                            <button type="submit" class="profile-save-btn">Save Changes</button>
                        </div>
                    </form>
                </div>

                <!-- Address Management Section -->
                <div class="address-management-section">
                    <h2 class="address-section-title">My Addresses</h2>

                    <div class="address-list">
                        <?php if (empty($user_addresses)): ?>
                            <p class="no-addresses-message">You have no saved addresses yet.</p>
                        <?php else: ?>
                            <?php foreach ($user_addresses as $address): ?>
                                <div class="address-item <?php echo $address['is_default'] ? 'default' : ''; ?>">
                                    <div class="address-header">
                                        <span class="address-label"><?php echo $address['is_default'] ? 'Default Address' : 'Address'; ?></span>
                                    </div>
                                    <div class="address-content">
                                        <div class="address-text">
                                            <?php echo htmlspecialchars($address['address_line1']); ?><br>
                                            <?php if (!empty($address['address_line2'])): ?>
                                                <?php echo htmlspecialchars($address['address_line2']); ?><br>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($address['city']); ?>, <?php echo htmlspecialchars($address['state_province']); ?> <?php echo htmlspecialchars($address['postal_code']); ?><br>
                                            <?php echo htmlspecialchars($address['country']); ?><br>
                                            Contact: <?php echo htmlspecialchars($address['contact_number']); ?>
                                        </div>
                                        <div class="address-actions">
                                            <form action="edit_address.php" method="post" style="display: inline;">
                                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($address['id']); ?>">
                                                <button type="submit" class="address-action-btn">Edit</button>
                                            </form>
                                            <form action="delete_address.php" method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this address?');">
                                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($address['id']); ?>">
                                                <button type="submit" class="address-action-btn delete">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <button class="add-address-btn" id="add-address-button">Add New Address</button>

                    <div id="add-address-form" class="add-address-form" style="display: none;">
                        <h3 class="add-address-title">Add New Address</h3>
                        <form action="add_address.php" method="post" class="new-address-form">
                            <div class="address-form-grid">
                                <div class="profile-form-group">
                                    <label for="new_address_line1">Address Line 1</label>
                                    <input type="text" id="new_address_line1" name="address_line1" required>
                                </div>
                                <div class="profile-form-group">
                                    <label for="new_address_line2">Address Line 2 (Optional)</label>
                                    <input type="text" id="new_address_line2" name="address_line2">
                                </div>
                                <div class="profile-form-group">
                                    <label for="new_city">City</label>
                                    <input type="text" id="new_city" name="city" required>
                                </div>
                                <div class="profile-form-group">
                                    <label for="new_state_province">State/Province</label>
                                    <input type="text" id="new_state_province" name="state_province" required>
                                </div>
                                <div class="profile-form-group">
                                    <label for="new_postal_code">Postal Code</label>
                                    <input type="text" id="new_postal_code" name="postal_code" required>
                                </div>
                                <div class="profile-form-group">
                                    <label for="new_country">Country</label>
                                    <input type="text" id="new_country" name="country" required>
                                </div>
                                <div class="profile-form-group">
                                    <label for="new_contact_number">Contact Number</label>
                                    <input type="text" id="new_contact_number" name="contact_number" required>
                                </div>
                            </div>
                            <div class="profile-form-group checkbox-group">
                                <input type="checkbox" id="new_is_default" name="is_default" value="1">
                                <label for="new_is_default">Set as default address</label>
                            </div>
                            <div class="address-form-actions">
                                <button type="submit" class="profile-save-btn">Save Address</button>
                                <button type="button" class="profile-cancel-btn" id="cancel-add-address">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

            <?php else: ?>
                <p class="error-message">Unable to load profile information.</p>
            <?php endif; ?>
        </div>

</div> <!-- Close container from header.php -->

<?php include 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle session alerts with toast notifications
    <?php
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        echo "showToast('" . addslashes($alert['message']) . "', '" . ($alert['type'] === 'success' ? 'success' : 'error') . "');";
    }
    ?>

    // Address form toggle functionality
    const addAddressButton = document.getElementById('add-address-button');
    const addAddressForm = document.getElementById('add-address-form');
    const cancelAddAddressButton = document.getElementById('cancel-add-address');

    if (addAddressButton && addAddressForm && cancelAddAddressButton) {
        addAddressButton.addEventListener('click', function() {
            addAddressForm.style.display = 'block';
            addAddressButton.style.display = 'none';
        });

        cancelAddAddressButton.addEventListener('click', function() {
            addAddressForm.style.display = 'none';
            addAddressButton.style.display = 'block';
            addAddressForm.querySelector('form').reset();
        });
    }
});
</script>