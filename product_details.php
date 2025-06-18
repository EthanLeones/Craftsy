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
        // Handle error gracefully
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'An error occurred while loading product details.'];
         header('Location: shop.php'); // Redirect back to shop
         exit();
    }
}

// If product not found or out of stock
if (!$product) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Product not found or out of stock.'];
    header('Location: shop.php'); // Redirect back to shop
    exit();
}

?>

<style>
    .product-detail-container {
        display: flex;
        flex-wrap: wrap;
        gap: 40px;
        margin-top: 20px;
    }

    .product-image-area {
        flex: 1 1 400px; /* Grow/shrink, base width 400px */
        text-align: center;
    }

    .product-image-area img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .product-details-area {
        flex: 1 1 400px; /* Grow/shrink, base width 400px */
    }

    .product-details-area h1 {
        color: #231942; /* Dark title color */
        margin-top: 0;
        margin-bottom: 10px;
        font-size: 2em;
    }

    .product-details-area .price {
        color: #5e548e; /* Medium purple for price */
        font-size: 1.5em;
        margin-bottom: 15px;
        display: block;
    }

     .product-details-area .description {
         color: #231942; /* Dark text color */
         margin-bottom: 20px;
         line-height: 1.6;
     }

     .product-details-area .meta-info {
         background-color: #f8f4fa; /* Very light purple */
         padding: 15px;
         border-radius: 8px;
         margin-bottom: 20px;
     }

     .product-details-area .meta-info p {
         margin-bottom: 5px;
         color: #231942;
     }

     .product-details-area label {
         font-weight: bold;
         color: #5e548e; /* Medium purple */
         margin-right: 10px;
     }

    .add-to-cart-form label {
        display: block;
        margin-bottom: 5px;
        color: #231942;
    }

    .add-to-cart-form input[type="number"],
    .add-to-cart-form select {
        padding: 8px;
        border: 1px solid #be95c4; /* Medium pink border */
        border-radius: 4px;
        margin-bottom: 15px;
    }

    .add-to-cart-form button {
        display: inline-block;
        padding: 10px 20px;
        background-color: #9f86c0; /* Medium purple button */
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .add-to-cart-form button:hover {
        background-color: #5e548e; /* Darker purple on hover */
    }

     .continue-shopping-button {
         display: inline-block;
         margin-top: 20px;
         padding: 10px 20px;
         background-color: #e0b1cb; /* Light pink button */
         color: #231942; /* Dark text color */
         text-decoration: none;
         border-radius: 5px;
         transition: background-color 0.3s ease;
     }

     .continue-shopping-button:hover {
         background-color: #be95c4; /* Medium pink on hover */
     }


</style>

        <h1 class="page-title">Product Details</h1>

        <div class="product-detail-container">
            <div class="product-image-area">
                <img src="<?php echo htmlspecialchars($product['image_url'] ?? 'images/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>

            <div class="product-details-area">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                <span class="price">P<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></span>
                <p class="description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

                <div class="meta-info">
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category'] ?? 'N/A'); ?></p>
                    <p><strong>Stock:</strong> <?php echo htmlspecialchars($product['stock_quantity']); ?> available</p>
                </div>

                <?php if ($product['stock_quantity'] > 0): ?>
                    <form action="add_to_cart.php" method="post" class="add-to-cart-form">
                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                        <label for="quantity">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo htmlspecialchars($product['stock_quantity']); ?>">
                        <button type="submit">Add to Cart</button>
                    </form>
                <?php else: ?>
                    <p style="color: red;">Out of Stock</p>
                <?php endif; ?>

                <a href="shop.php" class="continue-shopping-button">Continue Shopping</a>
            </div>
        </div>

</div> <!-- Close container from header.php -->

<?php include 'footer.php'; ?>

<?php
// Check for session alert message and display as JavaScript alert
// ... existing code ...
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addToCartForm = document.querySelector('.add-to-cart-form');
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(this);

            fetch('add_to_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count in header if needed
                    const cartCountSpan = document.querySelector('.header-icons .cart-count');
                    if (cartCountSpan && data.cart_count !== undefined) {
                         cartCountSpan.textContent = data.cart_count;
                    }
                    // Optionally provide visual feedback to the user (e.g., a temporary "Added to Cart" message)
                } else {
                    console.error('Failed to add to cart:', data.message); // Keep logging to console
                     // Optionally display an error message on the page
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Optionally display an error message on the page
            });
        });
    }
});
</script> 