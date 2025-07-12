<?php
require_once 'includes/session.php';
require_once 'config/database.php';
requireLogin();
$page_title = 'Checkout';
include 'header.php';

$cart_items = [];
$total_amount = 0;
$user_addresses = [];
$user_id = getCurrentUserId();

if ($user_id) {
    try {
        $conn = getDBConnection();

        // Fetch cart items for order summary
        $stmt_cart = $conn->prepare("SELECT c.*, p.name, p.price, p.image_url FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
        $stmt_cart->execute([$user_id]);
        $cart_items = $stmt_cart->fetchAll(PDO::FETCH_ASSOC);

        // Calculate total amount
        $total_amount = array_sum(array_map(function($item) { return $item['price'] * $item['quantity']; }, $cart_items));

        // Fetch user addresses
        $stmt_addresses = $conn->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
        $stmt_addresses->execute([$user_id]);
        $user_addresses = $stmt_addresses->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Error fetching checkout data: " . $e->getMessage());
        // Handle error gracefully
    }
}

?>

<style>
/* Checkout Page - Ultra Minimalistic & Modern Design */
.checkout-page-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 80px 40px;
    background: #ffffff;
    min-height: 80vh;
}

.checkout-page-title {
    font-size: 2rem;
    color: #000000;
    text-align: center;
    margin-bottom: 80px;
    font-weight: 200;
    letter-spacing: 4px;
    text-transform: uppercase;
}

.checkout-main-container {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 80px;
    margin-bottom: 60px;
}

.checkout-shipping-section {
    background-color: transparent;
}

.checkout-section-title {
    font-size: 0.8rem;
    color: #999999;
    margin-bottom: 40px;
    font-weight: 400;
    text-transform: uppercase;
    letter-spacing: 2px;
    border-bottom: 1px solid #f5f5f5;
    padding-bottom: 15px;
}

.checkout-address-list {
    margin-bottom: 60px;
}

.checkout-address-item {
    border: none;
    border-top: 1px solid #f8f8f8;
    padding: 30px 0;
    margin-bottom: 0;
    background-color: transparent;
    transition: all 0.4s ease;
    position: relative;
}

.checkout-address-item:hover {
    background-color: #fafafa;
    padding-left: 20px;
    padding-right: 20px;
}

.checkout-address-item.default {
    border-top-color: #000000;
    border-top-width: 2px;
}

.checkout-address-item:first-child {
    border-top: none;
}

.checkout-address-item input[type="radio"] {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

.checkout-address-item label {
    cursor: pointer;
    display: block;
    position: relative;
    padding-left: 30px;
    color: #333333;
    font-weight: 300;
    line-height: 1.8;
    font-size: 0.95rem;
}

.checkout-address-item label::before {
    content: '';
    position: absolute;
    left: 0;
    top: 5px;
    width: 16px;
    height: 16px;
    border: 1px solid #e0e0e0;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.checkout-address-item input[type="radio"]:checked + label::before {
    border-color: #000000;
    background-color: #000000;
}

.checkout-address-item input[type="radio"]:checked + label::after {
    content: '';
    position: absolute;
    left: 4px;
    top: 9px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #ffffff;
}

.checkout-address-item label pre {
    margin: 0;
    font-family: inherit;
    white-space: pre-line;
}

.checkout-no-address {
    text-align: center;
    color: #666666;
    font-size: 0.95rem;
    margin: 40px 0;
    font-weight: 300;
}

.checkout-no-address a {
    color: #000000;
    text-decoration: underline;
    font-weight: 400;
}

.checkout-payment-methods {
    margin-bottom: 40px;
}

.checkout-payment-method {
    border: none;
    border-top: 1px solid #f8f8f8;
    padding: 25px 0;
    margin-bottom: 0;
    background-color: transparent;
    transition: all 0.4s ease;
    position: relative;
}

.checkout-payment-method:hover {
    background-color: #fafafa;
    padding-left: 20px;
    padding-right: 20px;
}

.checkout-payment-method:first-child {
    border-top: none;
}

.checkout-payment-method input[type="radio"] {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

.checkout-payment-method label {
    cursor: pointer;
    display: block;
    position: relative;
    padding-left: 30px;
    color: #333333;
    font-weight: 400;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.checkout-payment-method label::before {
    content: '';
    position: absolute;
    left: 0;
    top: 2px;
    width: 16px;
    height: 16px;
    border: 1px solid #e0e0e0;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.checkout-payment-method input[type="radio"]:checked + label::before {
    border-color: #000000;
    background-color: #000000;
}

.checkout-payment-method input[type="radio"]:checked + label::after {
    content: '';
    position: absolute;
    left: 4px;
    top: 6px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #ffffff;
}

.checkout-payment-instructions {
    margin-top: 40px;
}

.checkout-payment-instructions img {
    max-width: 100%;
    height: auto;
    border: 1px solid #f0f0f0;
    transition: opacity 0.4s ease;
}

.checkout-proof-upload {
    margin-top: 40px;
    padding: 30px;
    background-color: #fafafa;
    border: 1px solid #f0f0f0;
}

.checkout-proof-upload label {
    display: block;
    font-size: 0.75rem;
    color: #666666;
    margin-bottom: 12px;
    font-weight: 400;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.checkout-proof-upload input[type="file"] {
    width: 100%;
    padding: 15px 0;
    border: none;
    border-bottom: 1px solid #e0e0e0;
    background-color: transparent;
    font-size: 0.95rem;
    color: #333333;
    transition: border-bottom-color 0.3s ease;
}

.checkout-proof-upload small {
    color: #999999;
    font-size: 0.75rem;
    margin-top: 10px;
    display: block;
    letter-spacing: 0.5px;
}

.checkout-summary-section {
    background-color: #fafafa;
    padding: 40px;
    position: sticky;
    top: 120px;
    height: fit-content;
}

.checkout-summary-title {
    font-size: 0.8rem;
    color: #999999;
    margin-bottom: 40px;
    font-weight: 400;
    text-transform: uppercase;
    letter-spacing: 2px;
    text-align: center;
}

.checkout-summary-items {
    margin-bottom: 40px;
}

.checkout-summary-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px 0;
    border-bottom: 1px solid #f0f0f0;
}

.checkout-summary-item:last-child {
    border-bottom: none;
}

.checkout-summary-item img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    flex-shrink: 0;
}

.checkout-summary-item-details {
    flex: 1;
}

.checkout-summary-item-details h4 {
    margin: 0 0 5px 0;
    font-size: 0.9rem;
    color: #000000;
    font-weight: 400;
}

.checkout-summary-item-details p {
    margin: 0;
    font-size: 0.8rem;
    color: #666666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.checkout-summary-item-price {
    font-size: 0.9rem;
    color: #000000;
    font-weight: 400;
}

.checkout-summary-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 25px 0;
    border-top: 2px solid #000000;
    margin-bottom: 40px;
}

.checkout-summary-total span:first-child {
    font-size: 0.8rem;
    color: #999999;
    text-transform: uppercase;
    letter-spacing: 2px;
}

.checkout-summary-total span:last-child {
    font-size: 1.3rem;
    color: #000000;
    font-weight: 400;
}

.checkout-summary-actions {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.checkout-btn {
    padding: 18px 0;
    border: none;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.4s ease;
    font-weight: 300;
    text-transform: uppercase;
    letter-spacing: 2px;
    text-decoration: none;
    display: block;
    text-align: center;
}

.checkout-btn-primary {
    background-color: #000000;
    color: #ffffff;
}

.checkout-btn-primary:hover {
    background-color: #333333;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.checkout-btn-secondary {
    background-color: transparent;
    color: #666666;
    border: 1px solid #e0e0e0;
}

.checkout-btn-secondary:hover {
    border-color: #000000;
    color: #000000;
    transform: translateY(-2px);
}

.checkout-note {
    text-align: center;
    color: #999999;
    font-size: 0.8rem;
    margin-top: 40px;
    font-weight: 300;
    letter-spacing: 0.5px;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .checkout-page-wrapper {
        padding: 60px 20px;
    }
    
    .checkout-main-container {
        grid-template-columns: 1fr;
        gap: 60px;
    }
    
    .checkout-page-title {
        font-size: 1.6rem;
        letter-spacing: 3px;
        margin-bottom: 60px;
    }
    
    .checkout-summary-section {
        position: static;
        order: -1;
    }
    
    .checkout-summary-actions {
        flex-direction: column;
    }
}

@media (max-width: 480px) {
    .checkout-page-wrapper {
        padding: 40px 15px;
    }
    
    .checkout-page-title {
        font-size: 1.4rem;
        letter-spacing: 2px;
    }
    
    .checkout-summary-section {
        padding: 30px 20px;
    }
}
</style>

<div class="checkout-page-wrapper">
    <h1 class="checkout-page-title">Checkout</h1>

    <form id="checkout-form" action="place_order.php" method="post" enctype="multipart/form-data">
        <div class="checkout-main-container">
            <div class="checkout-shipping-section">
                <h2 class="checkout-section-title">Shipping Address</h2>
                <?php if (empty($user_addresses)): ?>
                    <p class="checkout-no-address">Please add a shipping address in your <a href="add_address_inline.php">profile</a>.</p>
                <?php else: ?>
                    <div class="checkout-address-list">
                        <?php foreach ($user_addresses as $address): ?>
                            <div class="checkout-address-item <?php echo $address['is_default'] ? 'default' : ''; ?>">
                                <input type="radio" name="shipping_address" id="address-<?php echo htmlspecialchars($address['id']); ?>" value="<?php echo htmlspecialchars($address['id']); ?>" <?php echo $address['is_default'] ? 'checked' : ''; ?> required>
                                <label for="address-<?php echo htmlspecialchars($address['id']); ?>">
                                    <pre><?php echo htmlspecialchars($address['address_line1']); ?>
<?php if (!empty($address['address_line2'])): ?><?php echo htmlspecialchars($address['address_line2']); ?>
<?php endif; ?><?php echo htmlspecialchars($address['city']); ?>, <?php echo htmlspecialchars($address['state_province']); ?> <?php echo htmlspecialchars($address['postal_code']); ?>
<?php echo htmlspecialchars($address['country']); ?>
Contact: <?php echo htmlspecialchars($address['contact_number']); ?></pre>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <h2 class="checkout-section-title">Payment Method</h2>
                <div class="checkout-payment-methods">
                    <div class="checkout-payment-method">
                        <input type="radio" name="payment_method" id="cod" value="cod" checked required>
                        <label for="cod">Cash on Delivery</label>
                    </div>
                    <div class="checkout-payment-method">
                        <input type="radio" name="payment_method" id="bank_transfer" value="bank_transfer" required>
                        <label for="bank_transfer">Bank Transfer</label>
                    </div>
                    <div class="checkout-payment-method">
                        <input type="radio" name="payment_method" id="gcash" value="gcash" required>
                        <label for="gcash">GCash</label>
                    </div>
                </div>
                
                <div class="checkout-payment-instructions" id="payment-instructions-images">
                    <img src="images/gcash-sample.jpg" alt="Gcash Payment Instructions" id="gcash-instructions" style="display: none;">
                    <img src="images/bpi-sample.jpg" alt="Bank Transfer Payment Instructions" id="bpi-instructions" style="display: none;">
                </div>
                
                <div class="checkout-proof-upload" id="proof-of-payment-group" style="display: none;">
                    <label for="proof_of_payment">Upload Proof of Payment</label>
                    <input type="file" id="proof_of_payment" name="proof_of_payment" accept="image/*">
                    <small>Accepted formats: JPG, PNG, GIF</small>
                </div>
            </div>
            
            <div class="checkout-summary-section">
                <h2 class="checkout-summary-title">Order Summary</h2>
                <div class="checkout-summary-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="checkout-summary-item">
                            <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'images/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="checkout-summary-item-details">
                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                <p>Qty: <?php echo htmlspecialchars($item['quantity']); ?></p>
                            </div>
                            <div class="checkout-summary-item-price">
                                P<?php echo htmlspecialchars(number_format($item['price'] * $item['quantity'], 2)); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="checkout-summary-total">
                    <span>Total</span>
                    <span>P<?php echo htmlspecialchars(number_format($total_amount, 2)); ?></span>
                </div>
                <div class="checkout-summary-actions">
                    <?php if (!empty($cart_items) && !empty($user_addresses)): ?>
                        <button type="submit" form="checkout-form" class="checkout-btn checkout-btn-primary">Place Order</button>
                    <?php endif; ?>
                    <a href="cart.php" class="checkout-btn checkout-btn-secondary">Back to Cart</a>
                </div>
            </div>
        </div>
        
        <p class="checkout-note">
            Note: Delivery fees are not included in the total amount and will be collected upon delivery.
        </p>
    </form>
</div>

</div> <!-- Close container from header.php -->

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

    const paymentMethodRadios = document.querySelectorAll('input[name="payment_method"]');
    const proofOfPaymentGroup = document.getElementById('proof-of-payment-group');
    const gcashInstructions = document.getElementById('gcash-instructions');
    const bpiInstructions = document.getElementById('bpi-instructions');

    function toggleProofOfPayment() {
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
        if (!selectedMethod) return;
        
        const methodValue = selectedMethod.value;
    
        if (proofOfPaymentGroup) {
            if (methodValue === 'bank_transfer' || methodValue === 'gcash') {
                proofOfPaymentGroup.style.display = 'block';
            } else {
                proofOfPaymentGroup.style.display = 'none';
            }
        }

        if (methodValue === 'gcash') {
            if (gcashInstructions) gcashInstructions.style.display = 'block';
            if (bpiInstructions) bpiInstructions.style.display = 'none';
        } else if (methodValue === 'bank_transfer') {
            if (gcashInstructions) gcashInstructions.style.display = 'none';
            if (bpiInstructions) bpiInstructions.style.display = 'block';
        } else {
            if (gcashInstructions) gcashInstructions.style.display = 'none';
            if (bpiInstructions) bpiInstructions.style.display = 'none';
        }
    }

    paymentMethodRadios.forEach(radio => {
        radio.addEventListener('change', toggleProofOfPayment);
    });

    // Initialize on page load
    toggleProofOfPayment();
});
</script>

<?php include 'footer.php'; ?> 