<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart.php');
    exit;
}

$posted = $_POST['qty'] ?? [];
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) $_SESSION['cart'] = [];

foreach ($posted as $id => $qty) {
    $id = (int)$id;
    $qty = (int)$qty;
    if ($qty <= 0) {
        unset($_SESSION['cart'][$id]);
    } else {
        $_SESSION['cart'][$id] = $qty;
    }
}
// If AJAX request, return JSON with totals and item subtotals
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');

    // Load product prices to calculate subtotals
    $cache = __DIR__ . '/products_cache.json';
    $products = [];
    if (file_exists($cache)) {
        $data = json_decode(file_get_contents($cache), true);
        $products = $data['products'] ?? [];
    }

    $count = 0;
    $total = 0.0;
    $items = [];
    $priceMap = [];
    foreach ($products as $p) {
        $priceMap[(int)$p['id']] = (float)$p['price'];
    }

    foreach ($_SESSION['cart'] as $id => $q) {
        $count += (int)$q;
        $price = isset($priceMap[(int)$id]) ? $priceMap[(int)$id] : 0.0;
        $subtotal = $price * (int)$q;
        $items[(int)$id] = ['qty' => (int)$q, 'subtotal' => $subtotal];
        $total += $subtotal;
    }

    echo json_encode([
        'success' => true,
        'count' => $count,
        'total' => $total,
        'items' => $items
    ]);
    exit;
}

header('Location: cart.php');
exit;
