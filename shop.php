<?php
require_once 'includes/session.php';
require_once 'config/database.php';
$page_title = 'Product Catalog';
include 'header.php';

$products = [];
// Define static categories as the categories table will be dropped
$categories = [
    ['name' => 'Classic', 'link' => 'shop.php?category=Classic'],
    ['name' => 'Pouch', 'link' => 'shop.php?category=Pouch'],
    ['name' => 'Clutch', 'link' => 'shop.php?category=Clutch'],
    ['name' => 'Handy', 'link' => 'shop.php?category=Handy'],
    ['name' => 'Sling', 'link' => 'shop.php?category=Sling'],
    ['name' => 'Carry All', 'link' => 'shop.php?category=Carry All'],
    ['name' => 'Accessories', 'link' => 'shop.php?category=Accessories'],
];

$sql = "SELECT * FROM products WHERE stock_quantity > 0";
$params = [];

// Handle category filter from URL
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $selected_category = $_GET['category'];
    // Validate the category against the static list to prevent SQL injection or unexpected behavior
    $valid_categories = array_column($categories, 'name');
    if (in_array($selected_category, $valid_categories)) {
        $sql .= " AND category = ?";
        $params[] = $selected_category;
    } else {
        // Handle invalid category, maybe show an error or ignore the filter
        // For now, we'll just ignore the filter if it's invalid
    }
}

$sql .= " ORDER BY created_at DESC";

try {
    $conn = getDBConnection();
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error fetching products for shop: " . $e->getMessage());
    $_SESSION['alert'] = ['type' => 'danger', 'message' => 'An error occurred while loading products.'];
}

?>

<style>
    .shop-content {
        display: flex;
        gap: 30px; /* Space between sidebar and product list */
        flex-wrap: wrap; /* Allow wrapping on smaller screens */
        align-items: flex-start; /* Align items to the top */
    }

    .sidebar {
        flex: 0 0 200px; /* Fixed width sidebar */
        padding: 20px;
        background-color: #f8f4fa; /* Very light purple background */
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .sidebar h3 {
        color: #231942; /* Dark color for heading */
        margin-top: 0;
        margin-bottom: 15px;
        text-align: center;
    }

    .sidebar ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar li {
        margin-bottom: 10px;
    }

    .sidebar a {
        display: block; /* Make links full width */
        padding: 10px;
        background-color: #e0b1cb; /* Light pink button color */
        color: #231942; /* Dark text color */
        text-decoration: none;
        border-radius: 5px;
        text-align: center;
        transition: background-color 0.3s ease;
    }

    .sidebar a:hover {
        background-color: #be95c4; /* Medium pink on hover */
    }

     /* Style for the active category link (will need PHP to apply class) */
     .sidebar a.active {
         background-color: #9f86c0; /* Medium purple for active state */
         color: white;
         font-weight: bold;
     }


    .product-list {
        flex: 1; /* Take up remaining space */
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); /* Responsive grid */
        gap: 20px; /* Space between product items */
    }

    .product-item {
        border: 1px solid #e0b1cb; /* Light pink border */
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        background-color: #fff; /* White background */
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
         display: flex;
         flex-direction: column;
         justify-content: space-between; /* Space out content */
    }

     .product-item img {
         max-width: 100%;
         height: 200px; /* Fixed height for images */
         object-fit: cover; /* Cover the area, cropping if necessary */
         border-radius: 4px;
         margin-bottom: 15px;
     }

     .product-item h4 {
         color: #231942; /* Dark title color */
         margin-top: 0;
         margin-bottom: 5px;
          font-size: 1.1em;
     }

     .product-item p {
         color: #5e548e; /* Medium purple for price/stock */
         font-size: 1em;
         margin-bottom: 10px;
     }

      .product-item .view-details-button {
          display: inline-block;
          padding: 10px 15px;
          background-color: #9f86c0; /* Medium purple button */
          color: white;
          text-decoration: none;
          border-radius: 5px;
          transition: background-color 0.3s ease;
          margin-top: auto; /* Push button to the bottom */
      }

       .product-item .view-details-button:hover {
           background-color: #5e548e; /* Darker purple on hover */
       }


</style>

        <h1 class="page-title">Product Catalog</h1>

        <div class="shop-content">
            <div class="sidebar">
                <h3>Categories</h3>
                <ul>
                    <?php foreach($categories as $category): ?>
                        <li><a href="<?php echo htmlspecialchars($category['link']); ?>"><?php echo htmlspecialchars($category['name']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
                <!-- Add filter by size later -->
            </div>

            <div class="product-list">
                <?php if (empty($products)): ?>
                    <p style="text-align: center; grid-column: 1 / -1;">No products available at the moment.</p>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product-item">
                            <a href="product_details.php?id=<?php echo htmlspecialchars($product['id']); ?>">
                                <img src="<?php echo htmlspecialchars($product['image_url'] ?? 'images/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </a>
                            <h4><?php echo htmlspecialchars($product['name']); ?> - In Stock: <?php echo htmlspecialchars($product['stock_quantity']); ?></h4>
                            <p>P<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></p>

                             <!-- Link to product details page -->
                            <a href="product_details.php?id=<?php echo htmlspecialchars($product['id']); ?>" class="view-details-button">View Details</a>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

</div> <!-- Close container from header.php -->

<?php include 'footer.php'; ?>

<?php
// Check for session alert message and display as JavaScript alert
// ... existing code ...
?>

<script>
// The JavaScript for add to cart form submission will be moved to the product details page
// document.addEventListener('DOMContentLoaded', function() {
//     document.querySelectorAll('.add-to-cart-form').forEach(form => {
//         form.addEventListener('submit', function(event) {
//             event.preventDefault();

//             const formData = new FormData(this);

//             fetch('add_to_cart.php', {
//                 method: 'POST',
//                 body: formData
//             })
//             .then(response => response.json())
//             .then(data => {
//                 if (data.success) {
//                     alert(data.message); // Or a more user-friendly notification
//                     // Update cart count in header if needed
//                     const cartCountSpan = document.querySelector('.header-icons .cart-count');
//                     if (cartCountSpan && data.cart_count !== undefined) {
//                          cartCountSpan.textContent = data.cart_count;
//                     }
//                 } else {
//                     alert('Failed to add to cart: ' + data.message);
//                 }
//             })
//             .catch(error => {
//                 console.error('Error:', error);
//                 alert('An error occurred while adding to cart.');
//             });
//         });
//     });
// });
</script> 