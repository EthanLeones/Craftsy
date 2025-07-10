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

        <h1 class="page-title">Checkout</h1>

        <!-- Start of the form -->
        <form id="checkout-form" action="place_order.php" method="post" enctype="multipart/form-data">
            <div class="checkout-container">
                <div class="checkout-shipping">
                    <h2>Shipping Address</h2>
                     <?php if (empty($user_addresses)): ?>
                         <p style="text-align: center;">Please add a shipping address in your <a href="add_address_inline.php">profile</a>.</p>
                     <?php else: ?>
                        <div class="address-list">
                            <?php foreach ($user_addresses as $address): ?>
                                <div class="address-item <?php echo $address['is_default'] ? 'default' : ''; ?>">
                                    <input type="radio" name="shipping_address" id="address-<?php echo htmlspecialchars($address['id']); ?>" value="<?php echo htmlspecialchars($address['id']); ?>" <?php echo $address['is_default'] ? 'checked' : ''; ?> required>
                                    <label for="address-<?php echo htmlspecialchars($address['id']); ?>">
                                        <pre><?php echo htmlspecialchars($address['address_line1']); ?><br><?php echo htmlspecialchars($address['address_line2']); ?><br><?php echo htmlspecialchars($address['city']); ?>, <?php echo htmlspecialchars($address['state_province']); ?> <?php echo htmlspecialchars($address['postal_code']); ?><br><?php echo htmlspecialchars($address['country']); ?><br>Contact: <?php echo htmlspecialchars($address['contact_number']); ?></pre>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <h2>Payment Method</h2>
                    <div class="payment-methods">
                        <div class="payment-method">
                            <input type="radio" name="payment_method" id="cod" value="cod" checked required>
                            <label for="cod">Cash on Delivery</label>
                        </div>
                        <div class="payment-method">
                            <input type="radio" name="payment_method" id="bank_transfer" value="bank_transfer" required>
                            <label for="bank_transfer">Bank Transfer</label>
                        </div>
                        <div class="payment-method">
                            <input type="radio" name="payment_method" id="gcash" value="gcash" required>
                            <label for="gcash">Gcash</label>
                        </div>
                        <div id="payment-instructions-images">
                            <img src="images/gcash-sample.jpg" alt="Gcash Payment Instructions" id="gcash-instructions" style="display: none; max-width: 100%; height: auto; margin-top: 15px; border: 1px solid #ccc; border-radius: 4px;">
                            <img src="images/bpi-sample.jpg" alt="Bank Transfer Payment Instructions" id="bpi-instructions" style="display: none; max-width: 100%; height: auto; margin-top: 15px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div class="form-group" id="proof-of-payment-group" style="display: none;">
                            <label for="proof_of_payment">Upload Proof of Payment:</label>
                            <input type="file" id="proof_of_payment" name="proof_of_payment" accept="image/*">
                            <small>Accepted formats: JPG, PNG, GIF</small>
                        </div>
                    </div>
                </div>
                
                <div class="checkout-summary">
                    <h2>Order Summary</h2>
                    <div class="summary-items">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="summary-item">
                                <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'images/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <div class="item-details">
                                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p>Qty: <?php echo htmlspecialchars($item['quantity']); ?></p>
                                </div>
                                <div class="item-price">
                                    P<?php echo htmlspecialchars(number_format($item['price'] * $item['quantity'], 2)); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="summary-total">
                        <span>Total:</span>
                        <span>P<?php echo htmlspecialchars(number_format($total_amount, 2)); ?></span>
                    </div>
                     <div class="summary-actions">
                         <a href="cart.php" class="button">Back to Cart</a>
                          <?php if (!empty($cart_items) && !empty($user_addresses)): ?>
                             <button type="submit" form="checkout-form" class="button primary">Place Order</button>
                         <?php endif; ?>
                     </div>
                </div>
            </div>
            <p style="text-align: center; margin-top: 20px; font-size: 0.9em; color: #555;">
                Note: Delivery fees are not included in the total amount and will be collected upon delivery.
            </p>
        </form> <!-- End of the form -->

</div> <!-- Close container from header.php -->

<?php include 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethodRadios = document.querySelectorAll('input[name="payment_method"]');
    const proofOfPaymentGroup = document.getElementById('proof-of-payment-group');
    const gcashInstructions = document.getElementById('gcash-instructions');
    const bpiInstructions = document.getElementById('bpi-instructions');

    function toggleProofOfPayment() {
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
        
    
        if (proofOfPaymentGroup) {
            if (selectedMethod === 'bank_transfer' || selectedMethod === 'gcash') {
                proofOfPaymentGroup.style.display = 'block';
            } else {
                proofOfPaymentGroup.style.display = 'none';
            }
        }


        if (selectedMethod === 'gcash') {
            if (gcashInstructions) gcashInstructions.style.display = 'block';
            if (bpiInstructions) bpiInstructions.style.display = 'none';
        } else if (selectedMethod === 'bank_transfer') {
            if (gcashInstructions) gcashInstructions.style.display = 'none';
            if (bpiInstructions) bpiInstructions.style.display = 'block';
        } else { // For Cash on Delivery or any other method
            if (gcashInstructions) gcashInstructions.style.display = 'none';
            if (bpiInstructions) bpiInstructions.style.display = 'none';
        }
    }

    paymentMethodRadios.forEach(radio => {
        radio.addEventListener('change', toggleProofOfPayment);
    });

    toggleProofOfPayment();
});
</script>

<?php

?> 