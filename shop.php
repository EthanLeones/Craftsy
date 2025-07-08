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

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $selected_category = $_GET['category'];
    $valid_categories = array_column($categories, 'name');
    if (in_array($selected_category, $valid_categories)) {
        $sql .= " AND category = ?";
        $params[] = $selected_category;
    } else {
        // Handle invalid category, maybe show an error or ignore the filter
        // For now, we'll just ignore the filter if it's invalid
        error_log("Invalid category filter: " . htmlspecialchars($selected_category));
        $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Invalid category selected.'];
        // Optionally redirect to shop.php without the category filter
        header('Location: shop.php');
        exit();
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
        gap: 30px; 
        flex-wrap: wrap;
        align-items: flex-start;
    }

    .sidebar {
        flex: 0 0 200px;
        padding: 20px;
        background-color: #f8f4fa;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .sidebar h3 {
        color: #231942; 
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
        display: block;
        padding: 10px;
        background-color: #e0b1cb;
        color: #231942;
        text-decoration: none;
        border-radius: 5px;
        text-align: center;
        transition: background-color 0.3s ease;
    }

    .sidebar a:hover {
        background-color: #be95c4; 
    }

     .sidebar a.active {
         background-color: #9f86c0; 
         color: white;
         font-weight: bold;
     }


    .product-list {
        flex: 1;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
        gap: 20px; 
    }

    .product-item {
        border: 1px solid #e0b1cb; 
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        background-color: #fff; 
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
         display: flex;
         flex-direction: column;
         justify-content: space-between;
    }

     .product-item img {
         max-width: 100%;
         height: 200px; 
         object-fit: cover; 
         border-radius: 4px;
         margin-bottom: 15px;
     }

     .product-item h4 {
         color: #231942;
         margin-top: 0;
         margin-bottom: 5px;
          font-size: 1.1em;
     }

     .product-item p {
         color: #5e548e; 
         font-size: 1em;
         margin-bottom: 10px;
     }

      .product-item .view-details-button {
          display: inline-block;
          padding: 10px 15px;
          background-color: #9f86c0; 
          color: white;
          text-decoration: none;
          border-radius: 5px;
          transition: background-color 0.3s ease;
          margin-top: auto; 
      }

       .product-item .view-details-button:hover {
           background-color: #5e548e;
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

                            <a href="product_details.php?id=<?php echo htmlspecialchars($product['id']); ?>" class="view-details-button">View Details</a>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

</div> <!-- Close container from header.php -->

<?php include 'footer.php'; ?>

<?php

?>

<script>
</script> 