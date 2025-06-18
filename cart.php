<?php
require_once 'includes/session.php';
require_once 'config/database.php';
requireLogin();
$page_title = 'Shopping Cart';
include 'header.php';

$cart_items = [];
$total_amount = 0;

$user_id = getCurrentUserId();

if ($user_id) {
    try {
        $conn = getDBConnection();

        // Fetch cart items for the current user, joining with products table
        $stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.image_url FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
        $stmt->execute([$user_id]);
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate total amount
        $total_amount = array_sum(array_map(function($item) { return $item['price'] * $item['quantity']; }, $cart_items));

    } catch (PDOException $e) {
        error_log("Error fetching cart items: " . $e->getMessage());
        // Handle error gracefully
    }
}

?>

        <h1 class="page-title">Shopping Cart</h1>

        <div class="cart-container">
            <?php if (empty($cart_items)): ?>
                <p style="text-align: center;">Your cart is empty.</p>
            <?php else: ?>
                <div class="cart-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item" data-item-id="<?php echo htmlspecialchars($item['product_id']); ?>">
                            <div class="item-details">
                                <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'images/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <div>
                                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p>P<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></p>
                                </div>
                            </div>
                            <div class="item-quantity">
                                <label for="quantity-<?php echo htmlspecialchars($item['product_id']); ?>">Quantity:</label>
                                <input type="number" id="quantity-<?php echo htmlspecialchars($item['product_id']); ?>" name="quantity" value="<?php echo htmlspecialchars($item['quantity']); ?>" min="1" class="quantity-input">
                            </div>
                            <div class="item-total">
                                P<?php echo htmlspecialchars(number_format($item['price'] * $item['quantity'], 2)); ?>
                            </div>
                            <div class="item-actions">
                                <button class="remove-item">Remove</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <div class="cart-total">
                        <span>Total:</span>
                        <span id="cart-total-amount">P<?php echo htmlspecialchars(number_format($total_amount, 2)); ?></span>
                    </div>
                    <div class="cart-actions">
                        <a href="shop.php" class="button">Continue Shopping</a>
                        <a href="checkout.php" class="button primary">Proceed to Checkout</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

</div> <!-- Close container from header.php -->

<?php include 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cartItemsContainer = document.querySelector('.cart-items');
    const cartTotalAmountSpan = document.getElementById('cart-total-amount');

    // Function to update cart item total and overall total
    function updateCartTotals() {
        let newTotalAmount = 0;
        document.querySelectorAll('.cart-item').forEach(itemElement => {
            const quantityInput = itemElement.querySelector('.quantity-input');
            // Parse price, removing 'P' and commas
            const pricePerItemText = itemElement.querySelector('.item-details p').textContent.replace('P', '').replace(',', '');
            const pricePerItem = parseFloat(pricePerItemText);
            const quantity = parseInt(quantityInput.value);
            const itemTotalElement = itemElement.querySelector('.item-total');

            if (!isNaN(quantity) && quantity >= 0) {
                const itemTotal = pricePerItem * quantity;
                itemTotalElement.textContent = 'P' + itemTotal.toFixed(2);
                newTotalAmount += itemTotal;
            }
        });
        cartTotalAmountSpan.textContent = 'P' + newTotalAmount.toFixed(2);

        // Show/hide checkout button based on total amount
        const checkoutButton = document.querySelector('.cart-actions .primary');
        if (newTotalAmount > 0) {
            checkoutButton.style.display = 'inline-block';
        } else {
            checkoutButton.style.display = 'none';
        }
    }

    // Event listener for quantity changes
    cartItemsContainer.addEventListener('change', function(event) {
        if (event.target.classList.contains('quantity-input')) {
            const quantityInput = event.target;
            const itemElement = quantityInput.closest('.cart-item');
            const productId = itemElement.dataset.itemId;
            const newQuantity = parseInt(quantityInput.value);

            if (!isNaN(newQuantity) && newQuantity >= 0) {
                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('quantity', newQuantity);

                fetch('update_cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Cart updated successfully:', data.message);
                        updateCartTotals(); // Update totals on success
                        const cartCountSpan = document.querySelector('.header-icons .cart-count');
                         if (cartCountSpan && data.cart_count !== undefined) {
                              cartCountSpan.textContent = data.cart_count;
                         }
                        // If quantity became 0, remove the item element
                        if (newQuantity === 0) {
                            itemElement.remove();
                        }
                    } else {
                        alert('Failed to update cart: ' + data.message);
                        // Revert quantity input on failure
                        // You might need to store the original quantity before the change
                        location.reload(); // Simple reload for now on failure
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the cart.');
                    location.reload(); // Simple reload for now on error
                });
            } else {
                alert('Please enter a valid quantity.');
                location.reload(); // Simple reload for now on invalid input
            }
        }
    });

    // Event listener for remove button clicks
    cartItemsContainer.addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-item')) {
            const removeButton = event.target;
            const itemElement = removeButton.closest('.cart-item');
            const productId = itemElement.dataset.itemId;

            if (confirm('Are you sure you want to remove this item from your cart?')) {
                const formData = new FormData();
                formData.append('product_id', productId);

                fetch('remove_from_cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Item removed successfully:', data.message);
                        itemElement.remove(); // Remove the item element from the DOM
                        updateCartTotals(); // Update totals after removing item
                        const cartCountSpan = document.querySelector('.header-icons .cart-count');
                         if (cartCountSpan && data.cart_count !== undefined) {
                              cartCountSpan.textContent = data.cart_count;
                         }
                    } else {
                        alert('Failed to remove item: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while removing the item.');
                });
            }
        }
    });

    // Initial calculation of totals on page load
    updateCartTotals(); // Call initially to set correct total and button state
});
</script> 