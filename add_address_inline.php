<?php
require_once 'includes/session.php';
require_once 'config/database.php';
requireLogin();
$page_title = 'Add Address';
include 'header.php';
?>

<h1 class="page-title">Add New Address</h1>

<div class="form-container" style="max-width: 600px; margin: 0 auto; background-color: #f9f9f9; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
    <form action="add_address2.php" method="post">
        <div class="form-group">
            <label for="new_address_line1">Address Line 1</label>
            <input type="text" id="new_address_line1" name="address_line1" required>
        </div>

        <div class="form-group">
            <label for="new_address_line2">Address Line 2 <span style="color: #888;">(Optional)</span></label>
            <input type="text" id="new_address_line2" name="address_line2">
        </div>

        <div class="form-group">
            <label for="new_city">City</label>
            <input type="text" id="new_city" name="city" required>
        </div>

        <div class="form-group">
            <label for="new_state_province">State/Province</label>
            <input type="text" id="new_state_province" name="state_province" required>
        </div>

        <div class="form-group">
            <label for="new_postal_code">Postal Code</label>
            <input type="text" id="new_postal_code" name="postal_code" required>
        </div>

        <div class="form-group">
            <label for="new_country">Country</label>
            <input type="text" id="new_country" name="country" required>
        </div>

        <div class="form-group">
            <label for="new_contact_number">Contact Number</label>
            <input type="text" id="new_contact_number" name="contact_number" required>
        </div>

        <div class="form-group checkbox-group">
            <input type="checkbox" id="new_is_default" name="is_default" value="1" checked>
            <label for="new_is_default">Set as default address</label>
        </div>

        <div class="form-actions" style="margin-top: 20px;">
            <button type="submit" class="button">Save Address</button>
            <a href="checkout.php" class="button secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>
<style>
    .form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
    color: #333;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="password"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 1em;
}

.checkbox-group {
    display: flex;
    align-items: center;
}

.checkbox-group input[type="checkbox"] {
    margin-right: 8px;
}

.button {
    background-color: #9f86c0;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: background-color 0.3s ease;
}

.button:hover {
    background-color: #5e548e;
}

.button.secondary {
    background-color: #e0b1cb;
    color: #231942;
    margin-left: 10px;
}

.button.secondary:hover {
    background-color: #be95c4;
}
</style>