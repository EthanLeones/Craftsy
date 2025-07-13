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
        $stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.image_url, p.description FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
        $stmt->execute([$user_id]);
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_amount = array_sum(array_map(function ($item) {
            return $item['price'] * $item['quantity'];
        }, $cart_items));
    } catch (PDOException $e) {
        error_log("Error fetching cart items: " . $e->getMessage());
    }
}

?>

<style>
    body {
        background: #f8f9fa;
        font-family: 'Arial', sans-serif;
        color: #3f1a41;
    }

    .cart-page {
        max-width: 1400px;
        margin: 0 auto;
        padding: 60px 40px;
        background: white;
        min-height: 70vh;
    }

    .cart-title {
        text-align: center;
        font-size: 1.8rem;
        font-weight: 400;
        color: #3f1a41;
        margin-bottom: 50px;
        text-transform: uppercase;
        letter-spacing: 3px;
    }

    .cart-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 60px;
    }

    .cart-table-header {
        border-bottom: 1px solid #dee2e6;
    }

    .cart-table-header th {
        padding: 20px 20px;
        text-align: center;
        font-weight: 500;
        color: #3f1a41;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.9rem;
        background: #fafafa;
        border-bottom: 2px solid #3f1a41;
    }

    .cart-table-header th:first-child {
        text-align: left;
        width: 45%;
    }

    .cart-table-header th:nth-child(2) {
        width: 20%;
    }

    .cart-table-header th:nth-child(3) {
        width: 20%;
    }

    .cart-table-header th:nth-child(4) {
        width: 15%;
    }

    .cart-item-row {
        border-bottom: 1px solid #f1f3f4;
    }

    .cart-item-row:hover {
        background-color: #fafafa;
    }

    .cart-item-row td {
        padding: 30px 20px;
        vertical-align: middle;
        border: none;
    }

    .item-info {
        display: flex;
        align-items: flex-start;
        gap: 20px;
    }

    .item-image {
        width: 100px;
        height: 100px;
        object-fit: cover;
        flex-shrink: 0;
    }

    .item-details h4 {
        margin: 0 0 8px 0;
        font-size: 1rem;
        color: #3f1a41;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .item-details p {
        margin: 0 0 15px 0;
        color: #666666;
        font-size: 0.85rem;
        font-weight: 400;
        line-height: 1.4;
    }

    .price-cell,
    .total-cell {
        text-align: center;
        font-weight: 500;
        color: #3f1a41;
        font-size: 1rem;
    }

    .quantity-cell {
        text-align: center;
    }

    .quantity-controls {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0;

        width: fit-content;
        margin: 0 auto;
    }

    .quantity-input {
        width: 50px;
        padding: 8px 5px;
        border: 1px solid #3f1a41;
        border-left: none;
        border-right: none;
        text-align: center;
        font-size: 0.9rem;
        font-weight: 500;
        background: white;
        outline: none;
        appearance: none;
        -moz-appearance: textfield;
        color: #3f1a41;
    }

    .quantity-input::-webkit-outer-spin-button,
    .quantity-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .quantity-btn {
        width: 35px;
        height: 35px;
        background: white;
        color: #3f1a41;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .quantity-btn:hover {
        background: #f1f3f4;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }   

    .quantity-btn:first-child {
        border-right: none;
    }

    .quantity-btn:last-child {
        border-left: none;
    }

    .remove-btn {
        background: none;
        color: #e74c3c;
        border: none;
        padding: 0;
        cursor: pointer;
        font-size: 0.8rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        text-decoration: underline;
    }

    .remove-btn:hover {
        color: #c0392b;
        background-color: white;
        box-shadow: none;
    }


    .cart-footer {
        display: flex;
        justify-content: flex-end;
        align-items: flex-end;
        flex-direction: column;
        padding: 40px 0 0 0;
        border-top: 1px solid #dee2e6;
        gap: 20px;
    }

    .cart-total {
        font-size: 1.5rem;
        font-weight: 600;
        color: #3f1a41;
    }

    .cart-buttons {
        display: flex;
        gap: 30px;
    }

    .cart-btn {
        border: 1px solid #3f1a41;
        color: #3f1a41;
        padding: 15px 30px;
        text-decoration: none;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.2s;
        cursor: pointer;
        font-size: 0.85rem;
    }

    .cart-btn:hover {
        background: #2d1230;
        border-color: #2d1230;
        color: #ffffff;
        text-decoration: none;
        transform: translateY(-2px);
    }

    .empty-cart {
        text-align: center;
        padding: 100px 20px;
        color: #3f1a41;
        font-size: 1.1rem;
        font-weight: 400;
    }

    .empty-cart .cart-btn {
        margin-top: 20px;
        display: inline-block;
        background: #3f1a41;
        color: #ffffff;
        border: 1px solid #3f1a41;
        padding: 15px 30px;
    }

    .empty-cart .cart-btn:hover {
        background: #2d1230;
        border-color: #2d1230;
        text-decoration: none;
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        .cart-page {
            padding: 30px 15px;
            border: none;
        }

        .cart-table,
        .cart-table-header,
        .cart-item-row {
            display: block;
        }

        .cart-table-header {
            display: none;
        }

        .cart-item-row {
            margin-bottom: 30px;
            border: 1px solid #dee2e6;
            padding: 20px;
            background: white;
        }

        .cart-item-row td {
            display: block;
            padding: 10px 0;
            border: none;
        }

        .item-info {
            flex-direction: column;
            text-align: center;
            align-items: center;
        }

        .cart-footer {
            flex-direction: column;
            gap: 30px;
        }

        .cart-buttons {
            width: 100%;
            justify-content: center;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }
    }
</style>

<div class="cart-page">
    <h1 class="cart-title">Shopping Cart</h1>

    <?php if (empty($cart_items)): ?>
        <div class="empty-cart">
            <p>Your cart is empty.</p>
            <a href="shop.php" class="cart-btn">Start Shopping</a>
        </div>
    <?php else: ?>
        <table class="cart-table">
            <thead class="cart-table-header">
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr class="cart-item-row" data-item-id="<?php echo htmlspecialchars($item['product_id']); ?>">
                        <td>
                            <div class="item-info">
                                <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'images/placeholder.png'); ?>"
                                    alt="<?php echo htmlspecialchars($item['name']); ?>"
                                    class="item-image">
                                <div class="item-details">
                                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p><?php echo htmlspecialchars($item['description'] ?? 'description description'); ?></p>
                                    <button class="remove-btn remove-item">Remove</button>
                                </div>
                            </div>
                        </td>
                        <td class="price-cell">
                            P <?php echo htmlspecialchars(number_format($item['price'], 2)); ?>
                        </td>
                        <td class="quantity-cell">
                            <div class="quantity-controls">
                                <button class="quantity-btn decrease-qty">-</button>
                                <input type="number"
                                    class="quantity-input"
                                    value="<?php echo htmlspecialchars($item['quantity']); ?>"
                                    min="1"
                                    data-product-id="<?php echo htmlspecialchars($item['product_id']); ?>">
                                <button class="quantity-btn increase-qty">+</button>
                            </div>
                        </td>
                        <td class="total-cell item-total">
                            P <?php echo htmlspecialchars(number_format($item['price'] * $item['quantity'], 2)); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cart-footer">
            <div class="cart-total">
                P <span id="cart-total-amount"><?php echo htmlspecialchars(number_format($total_amount, 2)); ?></span>
            </div>
            <div class="cart-buttons">
                <a href="shop.php" class="cart-btn">Continue Shopping</a>
                <a href="checkout.php" class="cart-btn">Proceed to Checkout</a>
            </div>
        </div>
    <?php endif; ?>
</div>

</div> <!-- Close container from header.php -->

<?php include 'footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cartTable = document.querySelector('.cart-table tbody');
        const cartTotalAmountSpan = document.getElementById('cart-total-amount');

        function updateCartTotals() {
            let newTotalAmount = 0;
            document.querySelectorAll('.cart-item-row').forEach(itemElement => {
                const quantityInput = itemElement.querySelector('.quantity-input');
                const priceCell = itemElement.querySelector('.price-cell');
                const totalCell = itemElement.querySelector('.item-total');

                // Parse price, removing 'P' and commas
                const pricePerItemText = priceCell.textContent.replace('P ', '').replace(/,/g, '');
                const pricePerItem = parseFloat(pricePerItemText);
                const quantity = parseInt(quantityInput.value);

                if (!isNaN(quantity) && quantity >= 0 && !isNaN(pricePerItem)) {
                    const itemTotal = pricePerItem * quantity;
                    totalCell.textContent = 'P ' + itemTotal.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    newTotalAmount += itemTotal;
                }
            });

            cartTotalAmountSpan.textContent = newTotalAmount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            const checkoutButton = document.querySelector('.cart-btn[href="checkout.php"]');
            if (checkoutButton) {
                if (newTotalAmount > 0) {
                    checkoutButton.style.display = 'inline-block';
                } else {
                    checkoutButton.style.display = 'none';
                }
            }
        }

        // Handle quantity increase/decrease buttons
        if (cartTable) {
            cartTable.addEventListener('click', function(event) {
                if (event.target.classList.contains('increase-qty')) {
                    const quantityInput = event.target.parentNode.querySelector('.quantity-input');
                    quantityInput.value = parseInt(quantityInput.value) + 1;
                    updateQuantity(quantityInput);
                } else if (event.target.classList.contains('decrease-qty')) {
                    const quantityInput = event.target.parentNode.querySelector('.quantity-input');
                    const newValue = Math.max(1, parseInt(quantityInput.value) - 1);
                    quantityInput.value = newValue;
                    updateQuantity(quantityInput);
                } else if (event.target.classList.contains('remove-item')) {
                    removeItem(event.target);
                }
            });

            // Handle direct quantity input changes
            cartTable.addEventListener('change', function(event) {
                if (event.target.classList.contains('quantity-input')) {
                    updateQuantity(event.target);
                }
            });
        }

        function updateQuantity(quantityInput) {
            const itemElement = quantityInput.closest('.cart-item-row');
            const productId = itemElement.dataset.itemId;
            const newQuantity = parseInt(quantityInput.value);

            if (!isNaN(newQuantity) && newQuantity >= 1) {
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
                            updateCartTotals();
                            const cartCountSpan = document.querySelector('.header-icons .cart-count');
                            if (cartCountSpan && data.cart_count !== undefined) {
                                cartCountSpan.textContent = data.cart_count;
                            }
                        } else {
                            showToast('Failed to update cart: ' + data.message, 'error');
                            location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('An error occurred while updating the cart.', 'error');
                        location.reload();
                    });
            } else {
                quantityInput.value = 1;
                showToast('Please enter a valid quantity.', 'error');
            }
        }

        function removeItem(removeButton) {
            const itemElement = removeButton.closest('.cart-item-row');
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
                            itemElement.remove();
                            updateCartTotals();
                            const cartCountSpan = document.querySelector('.header-icons .cart-count');
                            if (cartCountSpan && data.cart_count !== undefined) {
                                cartCountSpan.textContent = data.cart_count;
                            }

                            // Check if cart is empty and reload if needed
                            if (document.querySelectorAll('.cart-item-row').length === 0) {
                                location.reload();
                            }
                        } else {
                            showToast('Failed to remove item: ' + data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('An error occurred while removing the item.', 'error');
                    });
            }
        }

        updateCartTotals();
    });
</script>