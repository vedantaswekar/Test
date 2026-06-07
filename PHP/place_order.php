<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$payment = trim($_POST['payment'] ?? '');
$delivery = trim($_POST['delivery'] ?? 'standard');

if ($name === '' || $email === '' || $phone === '' || $address === '') {
    $_SESSION['order_error'] = 'Please fill all required fields.';
    header('Location: checkout.php');
    exit;
}

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header('Location: cart.php');
    exit;
}

// Load products
$cache = __DIR__ . '/products_cache.json';
$products = [];
if (file_exists($cache)) {
    $data = json_decode(file_get_contents($cache), true);
    $products = $data['products'] ?? [];
}

function getProduct($products, $id) {
    foreach ($products as $p) if (($p['id'] ?? 0) == $id) return $p;
    return null;
}

$items = [];
$subtotal = 0;
foreach ($cart as $id => $qty) {
    $p = getProduct($products, $id);
    if (!$p) continue;
    $price = (float)$p['price'];
    $itemSubtotal = $price * (int)$qty;
    $items[] = ['id' => $id, 'title' => $p['title'], 'price' => $price, 'qty' => (int)$qty, 'subtotal' => $itemSubtotal];
    $subtotal += $itemSubtotal;
}

// Calculate shipping based on delivery method
$shipping = match($delivery) {
    'express' => 50,
    'overnight' => 150,
    default => 0
};
$total = $subtotal + $shipping;

$order = [
    'id' => 'ORD' . date('YmdHis') . rand(100,999),
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'address' => $address,
    'delivery' => $delivery,
    'payment' => $payment,
    'items' => $items,
    'subtotal' => $subtotal,
    'shipping' => $shipping,
    'total' => $total,
    'status' => 'pending',
    'created_at' => date('Y-m-d H:i:s')
];

// Persist order to orders.json
$ordersFile = __DIR__ . '/orders.json';
$orders = [];
if (file_exists($ordersFile)) {
    $existing = json_decode(file_get_contents($ordersFile), true);
    if (is_array($existing)) $orders = $existing;
}
$orders[] = $order;
file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT));

// Clear cart and store last order in session
$_SESSION['cart'] = [];
$_SESSION['last_order'] = $order;

header('Location: order_success.php');
exit;
