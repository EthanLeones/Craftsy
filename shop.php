<?php
require_once 'includes/session.php';
require_once 'config/database.php';
$page_title = 'Product Catalog';
include 'header.php';

$products = [];
$categories = [
    ['name' => 'Classic', 'link' => 'shop.php?category=Classic'],
    ['name' => 'Pouch', 'link' => 'shop.php?category=Pouch'],
    ['name' => 'Clutch', 'link' => 'shop.php?category=Clutch'],
    ['name' => 'Handy', 'link' => 'shop.php?category=Handy'],
    ['name' => 'Sling', 'link' => 'shop.php?category=Sling'],
    ['name' => 'Carry All', 'link' => 'shop.php?category=Carry All'],
    ['name' => 'Accessories', 'link' => 'shop.php?category=Accessories'],
];

$sql = "SELECT * FROM products WHERE stock_quantity > 0 AND active = 1";
$params = [];

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $selected_category = $_GET['category'];
    $valid_categories = array_column($categories, 'name');
    if (in_array($selected_category, $valid_categories)) {
        $sql .= " AND category = ?";
        $params[] = $selected_category;
    } else {
        error_log("Invalid category filter: " . htmlspecialchars($selected_category));
        $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Invalid category selected.'];
        header('Location: shop.php');
        exit();
    }
}

$search_query = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_query = trim($_GET['search']);
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
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
        flex: 0 0 260px;
        padding: 28px 22px 28px 22px;
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 4px 24px rgba(158, 134, 192, 0.10);
        display: flex;
        flex-direction: column;
        min-height: 400px;
        margin-top: 18px;
    }

    .sidebar h3 {
        color: #231942; 
        margin-top: 0;
        margin-bottom: 18px;
        text-align: left;
        font-size: 1.3em;
        letter-spacing: 1px;
        font-weight: 700;
    }

    .sidebar ul {
        list-style: none;
        padding: 0;
        margin: 0 0 24px 0;
    }

    .sidebar li {
        margin-bottom: 10px;
    }

    .sidebar a {
        display: block;
        padding: 12px 16px;
        background: #e0b1cb; /* Removed gradient, now solid color */
        color: #231942;
        text-decoration: none;
        border-radius: 7px;
        text-align: left;
        font-weight: 500;
        transition: background 0.3s, color 0.3s;
        box-shadow: 0 2px 8px #e0b1cb22;
        letter-spacing: 0.5px;
    }

    .sidebar a:hover, .sidebar a.active {
        background: #9f86c0; /* Removed gradient, now solid color */
        color: #fff;
        font-weight: bold;
    }

    .sidebar .shop-search-bar {
        margin-bottom: 24px;
        display: flex;
        flex-direction: row;
        gap: 0;
        align-items: center;
        background: #f8f4fa;
        border-radius: 7px;
        padding: 6px 8px;
        box-shadow: 0 1px 4px #e0b1cb11;
        justify-content: center; 
    }
    .sidebar .shop-search-bar input[type="text"] {
        padding: 8px 12px;
        border: none;
        border-radius: 5px 0 0 5px;
        font-size: 1em;
        width: 140px;
        background: transparent;
        outline: none;
        color: #231942;
        box-shadow: none;
    }
    .sidebar .shop-search-bar button {
        padding: 8px 16px;
        background: #9f86c0;
        color: #fff;
        border: none;
        border-radius: 0 5px 5px 0;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s;
        font-size: 1em;
    }
    .sidebar .shop-search-bar button:hover {
        background: #5e548e;
    }

    .product-list {
        flex: 1;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
        gap: 20px; 
    }

    .product-item {
        border: 1px solid #e0b1cb; 
        border-radius: 12px;
        padding: 18px;
        text-align: center;
        background-color: #fff; 
        box-shadow: 0 2px 8px rgba(158, 134, 192, 0.08);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: box-shadow 0.2s;
    }
    .product-item:hover {
        box-shadow: 0 8px 24px #be95c433;
    }

    .product-item img {
        max-width: 100%;
        height: 200px; 
        object-fit: cover; 
        border-radius: 8px;
        margin-bottom: 15px;
        box-shadow: 0 2px 8px #e0b1cb22;
    }

    .product-item h4 {
        color: #231942;
        margin-top: 0;
        margin-bottom: 5px;
        font-size: 1.1em;
        font-weight: 700;
        letter-spacing: 0.5px;
        word-break: break-word;
        white-space: normal; /* Allow text to wrap */
        overflow-wrap: break-word;
    }

    .product-item p {
        color: #5e548e; 
        font-size: 1em;
        margin-bottom: 10px;
        word-break: break-word;
        white-space: normal; /* Allow text to wrap */
        overflow-wrap: break-word;
    }

    .product-item .view-details-button {
        display: inline-block;
        padding: 10px 15px;
        background-color: #9f86c0; 
        color: white;
        text-decoration: none;
        border-radius: 6px;
        transition: background-color 0.3s ease;
        margin-top: auto; 
        font-weight: 600;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 8px #e0b1cb22;
    }

    .product-item .view-details-button:hover {
        background-color: #5e548e;
    }
</style>

<h1 class="page-title">Product Catalog</h1>

<div class="shop-content">
    <div class="sidebar">
        <form class="shop-search-bar" method="get" action="shop.php">
            <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search_query); ?>">
            <?php if (isset($_GET['category'])): ?>
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($_GET['category']); ?>">
            <?php endif; ?>
            <button type="submit"><span style="font-size:1.1em;">&#128269;</span></button>
        </form>
        <h3>Categories</h3>
        <ul>
            <?php foreach($categories as $category): ?>
                <li>
                    <a href="<?php echo htmlspecialchars($category['link']); ?>"
                        <?php if (isset($_GET['category']) && $_GET['category'] === $category['name']) echo 'class="active"'; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </a>
                </li>
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

</div>

<?php include 'footer.php'; ?>