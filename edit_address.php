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

<style>
/* Edit Address Page - Minimalistic & Modern Design */
.edit-address-wrapper {
    max-width: 800px;
    margin: 0 auto;
    padding: 80px 40px;
    background: #ffffff;
    min-height: 70vh;
}

.edit-address-title {
    font-size: 2rem;
    color: #000000;
    text-align: center;
    margin-bottom: 80px;
    font-weight: 200;
    letter-spacing: 4px;
    text-transform: uppercase;
}

.edit-address-container {
    background-color: transparent;
    padding: 0;
    border-radius: 0;
    box-shadow: none;
    margin: 0;
}

.edit-address-form {
    width: 100%;
}

.edit-address-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px 60px;
    margin-bottom: 40px;
}

.edit-address-form-group {
    margin-bottom: 40px;
    position: relative;
}

.edit-address-form-group.full-width {
    grid-column: 1 / -1;
}

.edit-address-form-group label {
    display: block;
    font-size: 0.75rem;
    color: #666666;
    margin-bottom: 12px;
    font-weight: 400;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.edit-address-form-group input[type="text"] {
    width: 100%;
    padding: 20px 0 15px 0;
    border: none;
    border-bottom: 1px solid #f0f0f0;
    background-color: transparent;
    font-size: 1.1rem;
    color: #000000;
    box-sizing: border-box;
    transition: all 0.4s ease;
    font-weight: 300;
}

.edit-address-form-group input[type="text"]:focus {
    outline: none;
    border-bottom-color: #000000;
    transform: translateY(-2px);
}

.edit-address-form-group input::placeholder {
    color: #cccccc;
    font-weight: 300;
}

.edit-address-checkbox-group {
    grid-column: 1 / -1;
    display: flex;
    align-items: center;
    gap: 15px;
    margin-top: 20px;
    justify-content: center;
}

.edit-address-checkbox-group input[type="checkbox"] {
    width: auto;
    margin: 0;
    transform: scale(1.2);
    accent-color: #000000;
}

.edit-address-checkbox-group label {
    margin: 0;
    color: #666666;
    font-size: 0.85rem;
    font-weight: 300;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.edit-address-form-actions {
    text-align: center;
    margin-top: 80px;
    display: flex;
    justify-content: center;
    gap: 30px;
}

.edit-address-save-btn {
    background-color: #000000;
    color: #ffffff;
    padding: 18px 60px;
    border: none;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.4s ease;
    font-weight: 300;
    text-transform: uppercase;
    letter-spacing: 2px;
    border-radius: 0;
    text-decoration: none;
    display: inline-block;
}

.edit-address-save-btn:hover {
    background-color: #333333;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.edit-address-cancel-btn {
    background-color: transparent;
    color: #666666;
    padding: 18px 60px;
    border: 1px solid #e0e0e0;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.4s ease;
    font-weight: 300;
    text-transform: uppercase;
    letter-spacing: 2px;
    border-radius: 0;
    text-decoration: none;
    display: inline-block;
}

.edit-address-cancel-btn:hover {
    border-color: #000000;
    color: #000000;
    transform: translateY(-2px);
}

.edit-address-error {
    text-align: center;
    color: #dc3545;
    font-size: 0.9rem;
    margin: 60px 0;
    font-weight: 300;
    letter-spacing: 1px;
}

@media (max-width: 768px) {
    .edit-address-wrapper {
        padding: 60px 20px;
    }
    
    .edit-address-form-grid {
        grid-template-columns: 1fr;
        gap: 40px;
    }
    
    .edit-address-title {
        font-size: 1.6rem;
        letter-spacing: 3px;
        margin-bottom: 60px;
    }
    
    .edit-address-form-group {
        margin-bottom: 35px;
    }
    
    .edit-address-form-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .edit-address-cancel-btn {
        margin-top: 15px;
    }
}

@media (max-width: 480px) {
    .edit-address-wrapper {
        padding: 40px 15px;
    }
    
    .edit-address-title {
        font-size: 1.4rem;
        letter-spacing: 2px;
    }
    
    .edit-address-save-btn,
    .edit-address-cancel-btn {
        padding: 15px 40px;
        font-size: 0.8rem;
    }
}
</style>

<div class="edit-address-wrapper">
    <h1 class="edit-address-title">Edit Address</h1>

    <div class="edit-address-container">
        <?php if ($address): ?>
            <form action="update_address.php" method="post" class="edit-address-form">
                <input type="hidden" name="address_id" value="<?= htmlspecialchars($address['id']) ?>">

                <div class="edit-address-form-grid">
                    <div class="edit-address-form-group">
                        <label for="address_line1">Address Line 1</label>
                        <input type="text" id="address_line1" name="address_line1" value="<?= htmlspecialchars($address['address_line1']) ?>" required>
                    </div>

                    <div class="edit-address-form-group">
                        <label for="address_line2">Address Line 2 (Optional)</label>
                        <input type="text" id="address_line2" name="address_line2" value="<?= htmlspecialchars($address['address_line2']) ?>">
                    </div>

                    <div class="edit-address-form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" value="<?= htmlspecialchars($address['city']) ?>" required>
                    </div>

                    <div class="edit-address-form-group">
                        <label for="state_province">State/Province</label>
                        <input type="text" id="state_province" name="state_province" value="<?= htmlspecialchars($address['state_province']) ?>" required>
                    </div>

                    <div class="edit-address-form-group">
                        <label for="postal_code">Postal Code</label>
                        <input type="text" id="postal_code" name="postal_code" value="<?= htmlspecialchars($address['postal_code']) ?>" required>
                    </div>

                    <div class="edit-address-form-group">
                        <label for="country">Country</label>
                        <input type="text" id="country" name="country" value="<?= htmlspecialchars($address['country']) ?>" required>
                    </div>

                    <div class="edit-address-form-group full-width">
                        <label for="contact_number">Contact Number</label>
                        <input type="text" id="contact_number" name="contact_number" value="<?= htmlspecialchars($address['contact_number']) ?>" required>
                    </div>
                </div>

                <div class="edit-address-checkbox-group">
                    <input type="checkbox" id="is_default" name="is_default" value="1" <?= $address['is_default'] ? 'checked' : '' ?>>
                    <label for="is_default">Set as default address</label>
                </div>

                <div class="edit-address-form-actions">
                    <button type="submit" class="edit-address-save-btn">Save Changes</button>
                    <a href="profile.php" class="edit-address-cancel-btn">Cancel</a>
                </div>
            </form>
        <?php else: ?>
            <p class="edit-address-error">Address not found or you do not have permission to edit it.</p>
        <?php endif; ?>
    </div>
</div>

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
});
</script>

