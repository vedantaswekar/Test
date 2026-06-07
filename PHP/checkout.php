<?php
session_start();

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header('Location: cart.php');
    exit;
}

// Load product details
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

$total = 0;
foreach ($cart as $id => $qty) {
    $p = getProduct($products, $id);
    if (!$p) continue;
    $total += ((float)$p['price']) * (int)$qty;
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Checkout</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="header">
    <div class="logo">🛍️ FakeStore</div>
  </header>

  <main class="container">
    <div class="main-content" style="width:100%">
      <h2>Checkout</h2>

      <form action="place_order.php" method="post">
        <div style="display:flex;gap:16px;flex-wrap:wrap">
          <div style="flex:1;min-width:280px">
            <h4 style="margin-bottom:12px;font-weight:700">Shipping Details</h4>
            
            <label class="mini-label">Full name *</label>
            <input name="name" required style="width:100%;padding:10px;border:1px solid var(--border);border-radius:6px;margin-bottom:12px;font-family:inherit">

            <label class="mini-label">Email *</label>
            <input name="email" type="email" required style="width:100%;padding:10px;border:1px solid var(--border);border-radius:6px;margin-bottom:12px;font-family:inherit">

            <label class="mini-label">Shipping address *</label>
            <textarea name="address" rows="4" required style="width:100%;padding:10px;border:1px solid var(--border);border-radius:6px;margin-bottom:12px;font-family:inherit"></textarea>

            <label class="mini-label">Phone *</label>
            <input name="phone" type="tel" required style="width:100%;padding:10px;border:1px solid var(--border);border-radius:6px;margin-bottom:12px;font-family:inherit">

            <label class="mini-label">Delivery preference</label>
            <select name="delivery" style="width:100%;padding:10px;border:1px solid var(--border);border-radius:6px;margin-bottom:12px;font-family:inherit">
              <option value="standard">Standard Delivery (5-7 days)</option>
              <option value="express">Express Delivery (2-3 days) +₹50</option>
              <option value="overnight">Overnight Delivery +₹150</option>
            </select>

            <label class="mini-label">Payment method *</label>
            <select name="payment" required style="width:100%;padding:10px;border:1px solid var(--border);border-radius:6px;margin-bottom:12px;font-family:inherit">
              <option value="cod">Cash on Delivery</option>
              <option value="card">Debit/Credit Card (Demo)</option>
              <option value="upi">UPI Payment (Demo)</option>
            </select>
          </div>

          <div style="width:320px">
            <h4 style="margin-bottom:12px;font-weight:700">Order Summary</h4>
            <div style="border:1px solid var(--border);padding:12px;border-radius:8px;background:var(--card-bg)">
              <?php foreach ($cart as $id => $qty): $p = getProduct($products, $id); if (!$p) continue; ?>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border-light)">
                  <div style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px"><?= htmlspecialchars($p['title']) ?></div>
                  <div style="font-size:13px;font-weight:600">₹<?= number_format(((float)$p['price'])*(int)$qty,2) ?></div>
                </div>
              <?php endforeach; ?>
              <hr>
              <div style="display:flex;justify-content:space-between;font-weight:800;font-size:16px;margin:8px 0">Subtotal <span>₹<?= number_format($total,2) ?></span></div>
              <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--text-light)">Shipping <span>(To be added)</span></div>
              <hr>
              <div style="display:flex;justify-content:space-between;font-weight:800;font-size:17px;color:var(--accent)">Total <span>₹<?= number_format($total,2) ?></span></div>
              
              <div style="margin-top:14px;font-size:11px;color:var(--success);background:var(--success-light);padding:8px;border-radius:6px;line-height:1.4">
                ✓ <strong>Order confirmation</strong> will be sent to your email<br>
                ✓ <strong>Estimated delivery</strong> based on selected method<br>
                ✓ <strong>Easy returns</strong> within 7 days
              </div>
            </div>
            <div style="margin-top:12px;text-align:right">
              <button type="submit" class="filter-btn primary" style="width:100%">Place Order</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </main>
</body>
</html>
