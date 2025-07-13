<?php
require_once 'includes/session.php';
require_once 'config/database.php';
$page_title = 'Product Details';
include 'header.php';

$product = null;
$product_id = $_GET['id'] ?? null;

if ($product_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND stock_quantity > 0");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching product details: " . $e->getMessage());
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'An error occurred while loading product details.'];
        header('Location: shop.php');
        exit();
    }
}

if (!$product) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Product not found or out of stock.'];
    header('Location: shop.php');
    exit();
}

?>

<style>
    body {
        background: #f4f4f8;
        margin: 0;
        padding: 0;
        font-size: 1.1rem;
    }

    .product-detail-container {
        display: flex;
        flex-wrap: wrap;
        gap: 7rem;
        margin: 0 auto;
        margin-top: 0;
        background: none;
        box-shadow: none;
        padding: 48px 0 0 0;
        justify-content: center;
        max-width: 1200px;
        min-height: 70vh;
    }

    .product-image-area {
        flex: 0 0 370px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f7f0fa;
        border: none;
        border-radius: 0;
        min-width: 500px;
        min-height: 500px;
        max-width: 500px;
        max-height: 500px;
        box-sizing: border-box;
        padding: 0;
    }

    .product-image-area img {
        max-width: 95%;
        max-height: 95%;
        border-radius: 0;
        box-shadow: none;
        background: transparent;
        display: block;
        margin: 0 auto;
    }

    .product-details-area {
        flex: 1 1 370px;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        min-width: 320px;
        max-width: 540px;
        padding-top: 0;
    }

    .product-details-area h1 {
        color: #3f1a41;
        margin-top: 0;
        margin-bottom: 6px;
        font-size: 2.2em;
        font-weight: 500;
        letter-spacing: 1px;
        text-transform: uppercase;
        text-align: left;
    }

    .product-details-area .description {
        color: #3f1a41;
        margin-bottom: 10px;
        font-size: 1em;
        line-height: 1.4;
        text-align: left;
    }

    .product-details-area .price {
        color: #3f1a41;
        font-size: 1.1em;
        margin-bottom: 18px;
        display: block;
        font-weight: 400;
        text-align: left;
    }

    .divider {
        border: none;
        border-top: 1.5px solid #e0d6f7;
        margin: 18px 0 18px 0;
        width: 100%;
    }

    .add-to-cart-form {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 10px;
    }

    .add-to-cart-form label {
        display: none;
    }

    .quantity-controls {
        display: flex;
        align-items: center;
        background: #6c5070;
        border-radius: 10px;
        overflow: hidden;
        border: none;
        height: 35px;
        min-width: 135px;
        box-shadow: none;
        padding: 0;
    }

    .quantity-controls button {
        background: none;
        border: none;
        color: #fff;
        font-size: 1.4em;
        width: 44px;
        height: 35px;
        cursor: pointer;
        font-weight: 300;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0;
        padding: 0;
        user-select: none;
        outline: none;
        transition: background 0.2s;
    }

    .quantity-controls button:active,
    .quantity-controls button:focus {
        background: #5e4062;
    }

    .quantity-controls input[type="number"] {
        width: 44px;
        height: 35px;
        text-align: center;
        border: none;
        background: transparent;
        color: #fff;
        font-size: 1.1em;
        font-weight: 300;
        outline: none;
        appearance: textfield;
        padding: 0;
        margin: 0;
        border-radius: 5px;
    }

    .quantity-controls input[type="number"]::-webkit-outer-spin-button,
    .quantity-controls input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .quantity-controls input[type="number"] {
        -moz-appearance: textfield;
    }

    .add-to-cart-form button[type="submit"] {
        padding: 0 22px;
        height: 35px;
        background-color: #3f1a41;
        color: #fff;
        border: none;
        border-radius: 10px;
        font-weight: 500;
        font-size: 1em;
        cursor: pointer;
        transition: all 0.2s;
        letter-spacing: 0.5px;
        box-shadow: none;
        margin-left: 8px;
        text-transform: uppercase;
    }

    .add-to-cart-form button[type="submit"]:hover {
        background: #2d1230;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(63, 26, 65, 0.3);
    }

    .continue-shopping-button {
        display: inline-block;
        margin-top: 12px;
        padding: 0;
        background: none;
        color: #3f1a41;
        text-decoration: none;
        border: none;
        font-size: 0.97em;
        font-weight: 500;
        transition: color 0.2s;
        text-align: left;
    }

    .continue-shopping-button:hover {
        color: #be95c4;
        text-decoration: none;
    }

    .toast-container {
        position: fixed;
        top: 32px;
        right: 32px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 12px;
        pointer-events: none;
    }

    .toast {
        min-width: 220px;
        max-width: 350px;
        background: #fff;
        color:rgb(255, 255, 255);
        border-radius: 8px;
        box-shadow: 0 4px 16px rgba(63, 26, 65, 0.18);
        padding: 16px 24px;
        font-size: 1em;
        font-weight: 500;
        opacity: 0;
        transform: translateY(-20px);
        transition: opacity 0.3s, transform 0.3s;
        pointer-events: auto;
        border-left: 6px solid #be95c4;
    }

    .toast.success {
        border-left-color: #4bb543;
    }

    .toast.error {
        border-left-color: #e74c3c;
    }

    .toast.show {
        opacity: 1;
        transform: translateY(0);
    }

    @media (max-width: 600px) {
        .toast-container {
            top: 16px;
            right: 8px;
            left: 8px;
            align-items: center;
        }

        .toast {
            min-width: 0;
            max-width: 98vw;
            padding: 12px 16px;
        }
    }

    @media (max-width: 900px) {
        .product-detail-container {
            flex-direction: column;
            align-items: center;
            padding: 20px 0;
            gap: 24px;
        }

        .product-image-area,
        .product-details-area {
            max-width: 98vw;
            min-width: unset;
        }

        .product-image-area {
            min-width: 220px;
            max-width: 98vw;
            min-height: 220px;
            max-height: 320px;
        }
    }
</style>

<div class="product-detail-container">
    <div class="product-image-area">
        <img src="<?php echo htmlspecialchars($product['image_url'] ?? 'images/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
    </div>
    <div class="product-details-area">
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
        <div class="description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></div>
        <span class="price">P <?php echo htmlspecialchars(number_format($product['price'], 2)); ?></span>
        <hr class="divider">
        <?php if ($product['stock_quantity'] > 0): ?>
            <form id="add-to-cart-form" class="add-to-cart-form">
                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                <div class="quantity-controls">
                    <button type="button" onclick="changeQty(-1)">-</button>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo htmlspecialchars($product['stock_quantity']); ?>">
                    <button type="button" onclick="changeQty(1)">+</button>
                </div>
                <button type="submit">ADD TO CART</button>
            </form>
        <?php else: ?>
            <p style="color: red;">Out of Stock</p>
        <?php endif; ?>
        <a href="shop.php" class="continue-shopping-button">&lt; continue shopping</a>
    </div>
</div>

<div class="toast-container" id="toast-container"></div>

<?php include 'footer.php'; ?>

<script>
    function changeQty(delta) {
        const qtyInput = document.getElementById('quantity');
        let val = parseInt(qtyInput.value, 10) || 1;
        const min = parseInt(qtyInput.min, 10) || 1;
        const max = parseInt(qtyInput.max, 10) || 99;
        val += delta;
        if (val < min) val = min;
        if (val > max) val = max;
        qtyInput.value = val;
    }

    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        container.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => container.removeChild(toast), 300);
        }, 3000);
    }

    // Handle add to cart form submission with AJAX
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('add-to-cart-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);

                fetch('add_to_cart.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            // Update cart count if available
                            if (data.cart_count && document.querySelector('.cart-count')) {
                                document.querySelector('.cart-count').textContent = data.cart_count;
                            }
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('An error occurred while adding to cart.', 'error');
                    });
            });
        }
    });

    <?php
    // Check for session alert message and display as toast
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        echo "document.addEventListener('DOMContentLoaded', function() {
                showToast('" . addslashes($alert['message']) . "', '" . ($alert['type'] === 'success' ? 'success' : 'error') . "');
            });";
        unset($_SESSION['alert']);
    }
    ?>
</script>