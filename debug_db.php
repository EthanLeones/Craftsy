<?php
require_once 'config/database.php';
try {
    $conn = getDBConnection();
    echo 'Database connection: OK' . PHP_EOL;
    
    // Simulate the exact logic from products.php
    $search = ''; // No search initially
    $sort = ''; // No sort initially
    $page = 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    $orderBy = 'created_at DESC';
    $searchParams = [];
    $countParams = [];
    $productsParams = [];

    // Determine active status based on sort
    $activeStatus = ($sort === 'is_active') ? 0 : 1;
    $whereClause = 'active = ?';
    $baseParams = [$activeStatus];

    // Handle search
    if (!empty($search)) {
        $whereClause .= ' AND (name LIKE ? OR category LIKE ? OR description LIKE ?)';
        $searchParam = '%' . $search . '%';
        $searchParams = [$searchParam, $searchParam, $searchParam];
    }

    // Combine base params with search params
    $countParams = array_merge($baseParams, $searchParams);
    $productsParams = array_merge($baseParams, $searchParams);
    
    echo 'WHERE clause: ' . $whereClause . PHP_EOL;
    echo 'Count params: ' . print_r($countParams, true);
    echo 'Products params: ' . print_r($productsParams, true);
    
    // Test count query
    $countQuery = "SELECT COUNT(*) FROM products WHERE $whereClause";
    $stmt_total = $conn->prepare($countQuery);
    
    // Bind parameters for count query
    foreach ($countParams as $index => $param) {
        if ($index === 0) {
            // First parameter is always the active status (integer)
            $stmt_total->bindValue($index + 1, $param, PDO::PARAM_INT);
        } else {
            // Search parameters are strings
            $stmt_total->bindValue($index + 1, $param, PDO::PARAM_STR);
        }
    }
    
    $stmt_total->execute();
    $totalResults = $stmt_total->fetchColumn();
    echo 'Total results: ' . $totalResults . PHP_EOL;
    
    // Test products query
    $productsQuery = "SELECT * FROM products WHERE $whereClause ORDER BY $orderBy LIMIT ? OFFSET ?";
    $stmt_products = $conn->prepare($productsQuery);

    // Bind search and filter parameters
    foreach ($productsParams as $index => $param) {
        if ($index === 0) {
            // First parameter is always the active status (integer)
            $stmt_products->bindValue($index + 1, $param, PDO::PARAM_INT);
        } else {
            // Search parameters are strings
            $stmt_products->bindValue($index + 1, $param, PDO::PARAM_STR);
        }
    }

    // Bind pagination parameters (continue the index sequence)
    $nextIndex = count($productsParams) + 1;
    $stmt_products->bindValue($nextIndex, $limit, PDO::PARAM_INT);
    $stmt_products->bindValue($nextIndex + 1, $offset, PDO::PARAM_INT);
    $stmt_products->execute();
    $products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);
    
    echo 'Products found: ' . count($products) . PHP_EOL;
    if (count($products) > 0) {
        echo 'First product: ' . $products[0]['name'] . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
?>
