<?php
require_once 'config/database.php';
try {
    $conn = getDBConnection();
    
    // Test with search
    $search = 'Red'; // Search for "Red"
    $sort = '';
    
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
    
    echo 'Search test for: "' . $search . '"' . PHP_EOL;
    echo 'WHERE clause: ' . $whereClause . PHP_EOL;
    echo 'Params: ' . print_r($countParams, true);
    
    // Test count query
    $countQuery = "SELECT COUNT(*) FROM products WHERE $whereClause";
    $stmt_total = $conn->prepare($countQuery);
    
    foreach ($countParams as $index => $param) {
        if ($index === 0) {
            $stmt_total->bindValue($index + 1, $param, PDO::PARAM_INT);
        } else {
            $stmt_total->bindValue($index + 1, $param, PDO::PARAM_STR);
        }
    }
    
    $stmt_total->execute();
    $totalResults = $stmt_total->fetchColumn();
    echo 'Search results: ' . $totalResults . PHP_EOL;
    
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
?>
