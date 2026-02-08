<?php
/**
 * Products API Endpoint
 * Returns array of products from cache with optional filtering
 * 
 * Query Parameters:
 * - search: Product title search (case-insensitive substring match)
 * - category: Category filter (exact match)
 * - price: Maximum price filter
 * - rating: Minimum rating filter
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=3600'); // Cache for 1 hour

try {
    // Load products from cache
    $cacheFile = __DIR__ . '/products_cache.json';
    
    if (!file_exists($cacheFile)) {
        throw new Exception('Products cache file not found');
    }
    
    $jsonData = @file_get_contents($cacheFile);
    if ($jsonData === false) {
        throw new Exception('Unable to read products cache');
    }
    
    $data = json_decode($jsonData, true);
    if (!is_array($data) || !isset($data['products'])) {
        throw new Exception('Invalid cache data format');
    }
    
    $products = array_values($data['products']); // Re-index array
    
    // Extract and validate filter parameters
    $filters = [
        'search' => trim($_GET['search'] ?? ''),
        'category' => trim($_GET['category'] ?? ''),
        'price' => isset($_GET['price']) && is_numeric($_GET['price']) ? (float)$_GET['price'] : null,
        'rating' => isset($_GET['rating']) && is_numeric($_GET['rating']) ? (float)$_GET['rating'] : null
    ];
    
    // Apply filters
    $filtered = array_filter($products, function($product) use ($filters) {
        // Search filter (case-insensitive substring)
        if ($filters['search'] !== '') {
            if (stripos($product['title'] ?? '', $filters['search']) === false) {
                return false;
            }
        }
        
        // Category filter (exact match)
        if ($filters['category'] !== '') {
            if (($product['category'] ?? '') !== $filters['category']) {
                return false;
            }
        }
        
        // Price filter
        if ($filters['price'] !== null) {
            if ((float)($product['price'] ?? 0) > $filters['price']) {
                return false;
            }
        }
        
        // Rating filter
        if ($filters['rating'] !== null) {
            if ((float)($product['rating']['rate'] ?? 0) < $filters['rating']) {
                return false;
            }
        }
        
        return true;
    });
    
    // Return successful response
    echo json_encode([
        'success' => true,
        'count' => count($filtered),
        'products' => array_values($filtered), // Re-index filtered array
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'count' => 0,
        'products' => []
    ]);
}
?>
