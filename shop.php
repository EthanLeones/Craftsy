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
    .bodyin {
        background-color: #3f1a41 !important;
        min-height: 100vh;
        margin: 0;
        padding: 0;
    }

    .shop-content {
        background-color: #3f1a41;
        display: flex;
        flex-direction: row;
        padding: 20px;
        gap: 1rem;
        flex-wrap: wrap;
        max-width: 1800px;
        margin: 0 auto;
        justify-content: center;
    }

    .sidebar {
        flex: 0 0 260px;
        padding: 28px 22px 28px 22px;
        background: #fff;
        box-shadow: 0 4px 24px rgba(158, 134, 192, 0.10);
        font-family: 'Montserrat', Arial, sans-serif;
        display: flex;
        flex-direction: column;
        min-height: 400px;
        margin-top: 18px;
        border-radius: 0;
    }

    .sidebar h3 {
        color: #3f1a41;
        margin-top: 0;
        margin-bottom: 18px;
        text-align: left;
        font-size: 2em;
        letter-spacing: 1px;
        font-weight: 400;
    }

    .sidebar ul {
        list-style: none;
        padding: 0;
        margin: 0 0 24px 0;
    }

    .sidebar ul li a {
        font-size: 1.25em;
        /* Make category names larger */
    }

    .sidebar li {
        margin-bottom: 10px;
    }

    .sidebar a {
        display: block;
        padding: 12px 16px;
        color: #3f1a41;
        text-decoration: none;
        border-radius: 7px;
        text-align: left;
        font-weight: 500;
        transition: background 0.3s, color 0.3s;
        box-shadow: none;
        letter-spacing: 0.5px;
    }

    .sidebar a:hover,
    .sidebar a.active {
        font-weight: bold;
    }

    .sidebar a.active {
        font-weight: bold;
        text-decoration: underline;
    }

    .sidebar .shop-search-bar {
        margin-bottom: 24px;
        display: flex;
        flex-direction: row;
        align-items: center;
        background: #e0e0e0;
        border-radius: 8px;
        padding: 4px 10px;
        box-shadow: none;
        justify-content: center;
        border: none;
        gap: 0;
    }

    .sidebar .shop-search-bar input[type="text"] {
        padding: 8px 12px 8px 32px;
        border: none;
        border-radius: 8px;
        font-size: 1em;
        width: 170px;
        background: transparent;
        outline: none;
        color: #5e548e;
        box-shadow: none;
        background-image: url('data:image/svg+xml;utf8,<svg fill="rgb(94,84,142)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M12.9 14.32a8 8 0 1 1 1.41-1.41l4.39 4.39a1 1 0 0 1-1.41 1.41l-4.39-4.39zm-4.9-1.32a6 6 0 1 0 0-12 6 6 0 0 0 0 12z"/></svg>');
        background-repeat: no-repeat;
        background-position: 8px center;
        background-size: 18px 18px;
    }

    .sidebar .shop-search-bar button {
        display: none;
    }

    .product-list {
        flex: 1;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        background: #fff;
        border-radius: 0;
        padding: 30px 30px 30px 30px;
        margin-top: 18px;
        min-height: 400px;
    }

    .product-item {
        padding: 18px;
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        cursor: pointer;
        border-radius: 0;
        background-color: #fff;
        box-shadow: none;
    }


    .product-item img {
        max-width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 15px;
        transition: transform 0.2s;
        cursor: pointer;
    }

    .product-item img:hover {
        transform: scale(1.04);
    }

    .product-desc {
        display: flex;
        flex-direction: column;
        align-items: center;
        font-family: 'Montserrat', Arial, sans-serif;
        margin-top: 10px;
        margin-bottom: 0;
        line-height: 1;
    }

    .product-name {
        font-size: 0.8em;
        letter-spacing: 0.12em;
        font-weight: 200;
        margin-bottom: 8px;
        text-align: center;
    }

    .product-stock {
        font-size: 0.8em;
        letter-spacing: 0.15em;
        font-weight: 200;
        margin-bottom: 8px;
        text-align: center;
    }

    .product-price {
        font-size: 0.8em;
        letter-spacing: 0.12em;
        font-weight: 200;
        margin-bottom: 0;
        text-align: center;
    }

    /* Responsive sidebar and layout */
    @media (max-width: 1100px) {
        .shop-content {
            flex-direction: column;
            padding: 10px;
            gap: 0;
        }

        .sidebar {
            flex: unset;
            width: 100%;
            max-width: 100%;
            margin-bottom: 18px;
            border-radius: 0;
            box-shadow: none;
            padding: 18px 10px;
        }

        .product-list {
            padding: 16px 4vw;
            margin-top: 0;
        }
    }

    /* Responsive product grid */
    @media (max-width: 900px) {
        .product-list {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
            padding: 10px 2vw;
        }

        .product-item img {
            height: 140px;
        }
    }

    @media (max-width: 600px) {
        .sidebar h3 {
            font-size: 1.2em;
        }

        .sidebar ul li a {
            font-size: 1em;
            padding: 10px 8px;
        }

        .product-list {
            grid-template-columns: 1fr;
            padding: 6px 0;
            gap: 10px;
        }

        .product-item {
            padding: 10px 4px;
        }

        .product-item img {
            height: 100px;
        }

        .sidebar .shop-search-bar input[type="text"] {
            width: 100%;
            font-size: 0.95em;
        }
    }
</style>

<div class="bodyin">
    <div class="shop-content">
        <div class="sidebar">
            <form class="shop-search-bar" method="get" action="shop.php">
                <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search_query); ?>">
                <?php if (isset($_GET['category'])): ?>
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($_GET['category']); ?>">
                <?php endif; ?>
                <button type="submit"><span style="font-size:1.1em;">&#128269;</span></button>
            </form>
            <h3>CATEGORIES</h3>
            <ul>
                <li>
                    <a href="shop.php"
                        class="<?php echo !isset($_GET['category']) ? 'active' : ''; ?>">
                        All
                    </a>
                </li>
                <?php foreach ($categories as $category): ?>
                    <li>
                        <a href="<?php echo htmlspecialchars($category['link']); ?>"
                            class="<?php echo (isset($_GET['category']) && $_GET['category'] === $category['name']) ? 'active' : ''; ?>">
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
                    <div class="product-item" onclick="window.location.href='product_details.php?id=<?php echo htmlspecialchars($product['id']); ?>'">
                        <img src="<?php echo htmlspecialchars($product['image_url'] ?? 'images/placeholder.png'); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                            onclick="event.stopPropagation(); window.location.href='product_details.php?id=<?php echo htmlspecialchars($product['id']); ?>'">
                        <div class="product-desc">
                            <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="product-stock">In Stock - <?php echo htmlspecialchars($product['stock_quantity']); ?></div>
                            <div class="product-price">P<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>

<script>
    function showToast(message, type = 'success') {
        // Simple toast implementation for shop page
        const toast = document.createElement('div');
        toast.className = 'toast ' + type;
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#27ae60' : '#e74c3c'};
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            z-index: 10001;
            transition: all 0.3s ease;
            transform: translateX(100%);
        `;
        document.body.appendChild(toast);
        
        // Show toast
        setTimeout(() => {
            toast.style.transform = 'translateX(0)';
        }, 100);
        
        // Hide and remove toast
        setTimeout(() => {
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => document.body.removeChild(toast), 300);
        }, 3000);
    }

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