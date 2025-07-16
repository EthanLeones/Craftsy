<?php
require_once 'includes/session.php';
require_once 'config/database.php';
requireLogin();
$page_title = 'Add Address';
include 'header.php';
?>

<div class="profile-page-wrapper">
    <h1 class="profile-page-title">Add New Address</h1>

    <div class="profile-main-container">
        <form action="add_address2.php" method="post" class="profile-form">
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
                <input type="checkbox" id="new_is_default" name="is_default" value="1" checked>
                <label for="new_is_default">Set as default address</label>
            </div>

            <div class="profile-form-actions">
                <button type="submit" class="profile-save-btn">Save Address</button>
                <a href="checkout.php" class="profile-cancel-btn">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>