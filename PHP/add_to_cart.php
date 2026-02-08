<?php
/**
 * Add to Cart Handler
 * Adds products to user's shopping cart (stored in session)
 * 
 * POST Parameters:
 * - id: Product ID (integer)
 * - qty: Quantity to add (integer, default: 1)
 * 
 * Returns JSON response with updated cart count
 */

session_start();

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Extract and validate product ID
    $id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : null;
    if ($id === false || $id === null || $id <= 0) {
        throw new Exception('Invalid product ID');
    }
    
    // Extract and validate quantity
    $qty = isset($_POST['qty']) ? filter_var($_POST['qty'], FILTER_VALIDATE_INT) : 1;
    if ($qty === false || $qty <= 0) {
        $qty = 1;
    }
    
    // Initialize cart session
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Add/update product in cart
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] += $qty;
    } else {
        $_SESSION['cart'][$id] = $qty;
    }
    
    // Calculate total cart count
    $totalCount = 0;
    foreach ($_SESSION['cart'] as $itemQty) {
        if (is_numeric($itemQty)) {
            $totalCount += (int)$itemQty;
        }
    }
    
    // Check if this is an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    if ($isAjax) {
        // Return JSON response for AJAX requests
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'count' => $totalCount,
            'id' => $id,
            'message' => 'Product added to cart'
        ]);
    } else {
        // Redirect for non-AJAX requests
        $redirect = $_POST['redirect'] ?? 'index.php';
        header('Location: ' . htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8'));
    }
    
} catch (Exception $e) {
    // Handle errors
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    if ($isAjax) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    } else {
        // Fallback for non-AJAX errors
        http_response_code(400);
        header('Location: index.php');
    }
}
?>
