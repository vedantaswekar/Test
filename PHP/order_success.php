<?php
session_start();
$order = $_SESSION['last_order'] ?? null;
if (!$order) {
    header('Location: index.php');
    exit;
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Order Placed</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="header">
    <div class="logo">🛍️ FakeStore</div>
  </header>

  <main class="container">
    <div class="main-content" style="width:100%;max-width:900px">
      <div style="text-align:center;padding:24px;background:rgba(76,175,80,0.1);border-radius:12px;margin-bottom:24px">
        <h2 style="color:var(--success);margin:0;font-size:28px">✓ Order Confirmed!</h2>
        <p style="color:var(--text-light);margin:8px 0 0 0">Thank you for your purchase</p>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px">
        <div style="border:1px solid var(--border);padding:16px;border-radius:8px;background:var(--card-bg)">
          <h4 style="margin-top:0">Order Details</h4>
          <div style="display:flex;justify-content:space-between;padding:6px 0">
            <span>Order ID:</span>
            <strong style="font-family:monospace"><?= htmlspecialchars($order['id']) ?></strong>
          </div>
          <div style="display:flex;justify-content:space-between;padding:6px 0">
            <span>Date:</span>
            <span><?= htmlspecialchars($order['created_at']) ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:6px 0">
            <span>Status:</span>
            <span style="background:var(--accent-light);color:var(--accent);padding:2px 8px;border-radius:4px;font-size:12px;font-weight:600">Pending</span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:6px 0">
            <span>Payment:</span>
            <span style="text-transform:uppercase;font-size:12px;font-weight:600"><?= htmlspecialchars($order['payment']) ?></span>
          </div>
        </div>

        <div style="border:1px solid var(--border);padding:16px;border-radius:8px;background:var(--card-bg)">
          <h4 style="margin-top:0">Shipping Details</h4>
          <div style="line-height:1.6;font-size:14px">
            <div style="font-weight:600"><?= htmlspecialchars($order['name']) ?></div>
            <div style="color:var(--text-light);font-size:13px"><?= htmlspecialchars($order['email']) ?></div>
            <div style="color:var(--text-light);font-size:13px">📞 <?= htmlspecialchars($order['phone']) ?></div>
            <div style="color:var(--text-light);font-size:13px;margin-top:6px">📍 <?= htmlspecialchars($order['address']) ?></div>
          </div>
        </div>
      </div>

      <div style="border:1px solid var(--border);padding:16px;border-radius:8px;background:var(--card-bg);margin-bottom:24px">
        <h4 style="margin-top:0;margin-bottom:12px">Order Items</h4>
        <div style="display:grid;gap:8px">
          <?php foreach ($order['items'] as $it): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px;border:1px solid var(--border-light);border-radius:6px;background:white">
              <div>
                <div style="font-weight:600;margin-bottom:2px"><?= htmlspecialchars($it['title']) ?></div>
                <div style="font-size:12px;color:var(--text-light)">Qty: <?= (int)$it['qty'] ?> × ₹<?= number_format($it['price'],2) ?></div>
              </div>
              <div style="font-weight:700;text-align:right">₹<?= number_format($it['subtotal'],2) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div style="border:1px solid var(--border);padding:16px;border-radius:8px;background:var(--card-bg);margin-bottom:24px;max-width:400px;margin-left:auto">
        <h4 style="margin-top:0;margin-bottom:12px">Payment Summary</h4>
        <div style="border-bottom:1px solid var(--border-light);padding-bottom:12px">
          <div style="display:flex;justify-content:space-between;padding:6px 0">
            <span>Subtotal:</span>
            <span>₹<?= number_format($order['subtotal'] ?? 0,2) ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:6px 0">
            <span>Shipping (<?= htmlspecialchars($order['delivery'] ?? 'standard') ?>):</span>
            <span>₹<?= number_format($order['shipping'] ?? 0,2) ?></span>
          </div>
        </div>
        <div style="display:flex;justify-content:space-between;padding:12px 0;font-weight:800;font-size:16px;color:var(--accent)">
          Total: <span>₹<?= number_format($order['total'],2) ?></span>
        </div>
      </div>

      <div style="message-container;text-align:center">
        <div style="background:var(--success-light);border:1px solid var(--success);padding:12px 16px;border-radius:8px;color:var(--success);margin-bottom:16px;font-size:13px;line-height:1.4">
          <strong>✓ Order Confirmation</strong> has been sent to <?= htmlspecialchars($order['email']) ?><br>
          Check your inbox and spam folder for tracking updates
        </div>
        <a href="index.php" class="filter-btn primary" style="display:inline-block">Continue Shopping</a>
      </div>
    </div>
  </main>
</body>
</html>
