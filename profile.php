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
        
        // Fetch user data
        $stmt = $conn->prepare("SELECT id, username, name, email FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch user addresses
        $stmt_addresses = $conn->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
        $stmt_addresses->execute([$user_id]);
        $user_addresses = $stmt_addresses->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Error fetching user profile or addresses: " . $e->getMessage());
        // Handle error gracefully
    }
}

?>

        <h1 class="page-title">My Profile</h1>

        <div class="profile-container">
            <h2>Edit Your Information</h2>
            <?php if ($user): ?>
                <form action="update_profile.php" method="post">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="name">Full Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                     <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <!-- Add more fields as needed (e.g., contact) -->
                    
                    <h3>Change Password</h3>
                    <div class="form-group">
                        <label for="current_password">Current Password:</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    <div class="form-group">
                        <label for="confirm_new_password">Confirm New Password:</label>
                        <input type="password" id="confirm_new_password" name="confirm_new_password">
                    </div>

                    <button type="submit">Save Changes</button>
                </form>

                 <h2 style="margin-top: 40px;">My Addresses</h2>

                 <div class="address-list">
                     <?php if (empty($user_addresses)): ?>
                         <p style="text-align: center;">You have no saved addresses yet.</p>
                     <?php else: ?>
                         <?php foreach ($user_addresses as $address): ?>
                             <div class="address-item <?php echo $address['is_default'] ? 'default' : ''; ?>">
                                 <p><strong><?php echo $address['is_default'] ? 'Default Address' : 'Address'; ?></strong></p>
                                 <pre><?php echo htmlspecialchars($address['address_line1']); ?><br><?php echo htmlspecialchars($address['address_line2']); ?><br><?php echo htmlspecialchars($address['city']); ?>, <?php echo htmlspecialchars($address['state_province']); ?> <?php echo htmlspecialchars($address['postal_code']); ?><br><?php echo htmlspecialchars($address['country']); ?><br>Contact: <?php echo htmlspecialchars($address['contact_number']); ?></pre>
                                 <div class="address-actions">
                                     <a href="edit_address.php?id=<?php echo htmlspecialchars($address['id']); ?>" class="button small">Edit</a>
                                     <a href="delete_address.php?id=<?php echo htmlspecialchars($address['id']); ?>" class="button small danger" onclick="return confirm('Are you sure you want to delete this address?');">Delete</a>
                                 </div>
                             </div>
                         <?php endforeach; ?>
                     <?php endif; ?>
                 </div>

                 <button class="button" id="add-address-button">Add New Address</button>

                 <div id="add-address-form" style="display: none; margin-top: 20px;">
                      <h3>Add New Address</h3>
                      <form action="add_address.php" method="post">
                           <div class="form-group">
                                <label for="new_address_line1">Address Line 1:</label>
                                <input type="text" id="new_address_line1" name="address_line1" required>
                           </div>
                           <div class="form-group">
                                <label for="new_address_line2">Address Line 2 (Optional):</label>
                                <input type="text" id="new_address_line2" name="address_line2">
                           </div>
                           <div class="form-group">
                                <label for="new_city">City:</label>
                                <input type="text" id="new_city" name="city" required>
                           </div>
                           <div class="form-group">
                                <label for="new_state_province">State/Province:</label>
                                <input type="text" id="new_state_province" name="state_province" required>
                           </div>
                           <div class="form-group">
                                <label for="new_postal_code">Postal Code:</label>
                                <input type="text" id="new_postal_code" name="postal_code" required>
                           </div>
                           <div class="form-group">
                                <label for="new_country">Country:</label>
                                <input type="text" id="new_country" name="country" required>
                           </div>
                            <div class="form-group">
                                <label for="new_contact_number">Contact Number:</label>
                                <input type="text" id="new_contact_number" name="contact_number" required>
                            </div>
                           <div class="form-group">
                                <input type="checkbox" id="new_is_default" name="is_default" value="1">
                                <label for="new_is_default">Set as default address</label>
                           </div>
                           <button type="submit">Save Address</button>
                           <button type="button" class="button secondary" id="cancel-add-address">Cancel</button>
                      </form>
                 </div>


            <?php else: ?>
                <p style="text-align: center;">Unable to load profile information.</p>
            <?php endif; ?>
        </div>

</div> <!-- Close container from header.php -->

<?php include 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addAddressButton = document.getElementById('add-address-button');
    const addAddressForm = document.getElementById('add-address-form');
    const cancelAddAddressButton = document.getElementById('cancel-add-address');

    addAddressButton.addEventListener('click', function() {
        addAddressForm.style.display = 'block';
        addAddressButton.style.display = 'none';
    });

    cancelAddAddressButton.addEventListener('click', function() {
        addAddressForm.style.display = 'none';
        addAddressButton.style.display = 'block';
        // Optionally clear form fields here
         addAddressForm.querySelector('form').reset();
    });
});
</script> 