<?php
session_start();

$ordersFile = __DIR__ . '/orders.json';
$orders = [];

if (file_exists($ordersFile)) {
    $content = file_get_contents($ordersFile);
    if (!empty($content)) {
        $orders = json_decode($content, true) ?? [];
    }
}

// Reverse to show newest first
$orders = array_reverse($orders);

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Orders</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="header">
    <div class="logo">🛍️ FakeStore</div>
    <div style="display:flex;gap:12px;align-items:center">
      <a href="index.php" style="color:inherit;text-decoration:none">Back to Shop</a>
    </div>
  </header>

  <main class="container">
    <div class="main-content" style="width:100%">
      <h2>All Orders</h2>

      <?php if (empty($orders)): ?>
        <div style="text-align:center;padding:40px;color:var(--text-light)">
          <p>No orders yet.</p>
          <a href="index.php" class="filter-btn">Start Shopping</a>
        </div>
      <?php else: ?>
        <div style="display:grid;gap:16px">
          <?php foreach ($orders as $order): ?>
            <div style="border:1px solid var(--border);padding:16px;border-radius:8px;background:var(--card-bg)">
              <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:12px;margin-bottom:12px">
                <div>
                  <div style="font-size:11px;color:var(--text-light);text-transform:uppercase;font-weight:600">Order ID</div>
                  <div style="font-weight:600;font-family:monospace;font-size:13px"><?= htmlspecialchars($order['id']) ?></div>
                </div>
                <div>
                  <div style="font-size:11px;color:var(--text-light);text-transform:uppercase;font-weight:600">Customer</div>
                  <div style="font-weight:600"><?= htmlspecialchars($order['name']) ?></div>
                </div>
                <div>
                  <div style="font-size:11px;color:var(--text-light);text-transform:uppercase;font-weight:600">Date</div>
                  <div><?= htmlspecialchars($order['created_at']) ?></div>
                </div>
                <div style="text-align:right">
                  <div style="font-size:11px;color:var(--text-light);text-transform:uppercase;font-weight:600">Total</div>
                  <div style="font-weight:700;font-size:16px;color:var(--accent)">₹<?= number_format($order['total'],2) ?></div>
                </div>
              </div>

              <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;font-size:13px">
                <div>
                  <div style="font-size:11px;color:var(--text-light);text-transform:uppercase;font-weight:600;margin-bottom:4px">Email</div>
                  <div><?= htmlspecialchars($order['email']) ?></div>
                </div>
                <div>
                  <div style="font-size:11px;color:var(--text-light);text-transform:uppercase;font-weight:600;margin-bottom:4px">Phone</div>
                  <div><?= htmlspecialchars($order['phone'] ?? 'N/A') ?></div>
                </div>
              </div>

              <div style="margin-bottom:12px;font-size:13px">
                <div style="font-size:11px;color:var(--text-light);text-transform:uppercase;font-weight:600;margin-bottom:4px">Address</div>
                <div style="white-space:pre-wrap"><?= htmlspecialchars($order['address']) ?></div>
              </div>

              <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px;font-size:12px">
                <div style="background:var(--border-light);padding:8px;border-radius:6px">
                  <div style="color:var(--text-light);margin-bottom:2px">Delivery</div>
                  <div style="font-weight:600"><?= htmlspecialchars($order['delivery'] ?? 'standard') ?></div>
                </div>
                <div style="background:var(--border-light);padding:8px;border-radius:6px">
                  <div style="color:var(--text-light);margin-bottom:2px">Payment</div>
                  <div style="font-weight:600;text-transform:uppercase"><?= htmlspecialchars($order['payment']) ?></div>
                </div>
                <div style="background:var(--border-light);padding:8px;border-radius:6px">
                  <div style="color:var(--text-light);margin-bottom:2px">Status</div>
                  <div style="font-weight:600;color:var(--accent)"><?= htmlspecialchars($order['status'] ?? 'pending') ?></div>
                </div>
              </div>

              <div style="border-top:1px solid var(--border-light);padding-top:12px">
                <div style="font-size:11px;color:var(--text-light);text-transform:uppercase;font-weight:600;margin-bottom:8px">Items (<?= count($order['items']) ?>)</div>
                <div style="display:grid;gap:6px;font-size:12px">
                  <?php foreach ($order['items'] as $it): ?>
                    <div style="display:flex;justify-content:space-between">
                      <div><?= htmlspecialchars($it['title']) ?> ×<?= (int)$it['qty'] ?></div>
                      <div>₹<?= number_format($it['subtotal'],2) ?></div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>

              <?php if (isset($order['subtotal'])): ?>
                <div style="border-top:1px solid var(--border-light);padding-top:12px;margin-top:12px;font-size:12px">
                  <div style="display:flex;justify-content:space-between;padding:4px 0">
                    <span>Subtotal:</span>
                    <span>₹<?= number_format($order['subtotal'],2) ?></span>
                  </div>
                  <div style="display:flex;justify-content:space-between;padding:4px 0">
                    <span>Shipping:</span>
                    <span>₹<?= number_format($order['shipping'] ?? 0,2) ?></span>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>
