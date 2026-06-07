<?php
session_start();

// Load products cache
$cache = __DIR__ . '/products_cache.json';
$products = [];
if (file_exists($cache)) {
    $data = json_decode(file_get_contents($cache), true);
    $products = $data['products'] ?? [];
}

$cart = $_SESSION['cart'] ?? [];

function getProduct($products, $id) {
    foreach ($products as $p) {
        if (($p['id'] ?? 0) == $id) return $p;
    }
    return null;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Your Cart</title>
    <link rel="stylesheet" href="style_update.css">
</head>
<body>
    <header class="header">
        <div class="logo">🛍️ FakeStore</div>
        <?php
        $cartCount = 0; foreach ($cart as $q) $cartCount += $q;
        ?>
        <a class="cart" href="index.php">🛒 Cart <span id="cartCount"><?= (int)$cartCount ?></span></a>
    </header>

    <main class="container">
        <div class="main-content" style="width:100%">
            <h2>Your Cart</h2>

            <?php if (empty($cart)): ?>
                <div class="no-products">Your cart is empty — <a href="index.php">shop now</a>.</div>
            <?php else: ?>
                <form id="cartForm" action="update_cart.php" method="post">
                    <table style="width:100%;border-collapse:collapse;margin-top:12px">
                        <thead>
                            <tr>
                                <th style="text-align:left;padding:8px">Product</th>
                                <th style="padding:8px">Price</th>
                                <th style="padding:8px">Qty</th>
                                <th style="padding:8px">Subtotal</th>
                                <th style="padding:8px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $total = 0; foreach ($cart as $id => $qty):
                            $p = getProduct($products, $id);
                            if (!$p) continue;
                            $price = (float)$p['price'];
                            $subtotal = $price * (int)$qty;
                            $total += $subtotal;
                        ?>
                            <tr>
                                <td style="padding:8px;vertical-align:middle">
                                    <img src="<?= htmlspecialchars($p['image']) ?>" alt="" style="height:48px;object-fit:contain;margin-right:8px;vertical-align:middle"> 
                                    <?= htmlspecialchars($p['title']) ?>
                                </td>
                                <td style="padding:8px;text-align:center">₹<?= number_format($price,2) ?></td>
                                <td style="padding:8px;text-align:center">
                                    <input class="qty-input" data-id="<?= (int)$id ?>" type="number" name="qty[<?= (int)$id ?>]" value="<?= (int)$qty ?>" min="0" style="width:68px;padding:6px">
                                </td>
                                <td class="item-subtotal" data-id="<?= (int)$id ?>" style="padding:8px;text-align:center">₹<?= number_format($subtotal,2) ?></td>
                                <td style="padding:8px;text-align:center">
                                    <button type="button" class="remove-btn" data-id="<?= (int)$id ?>" style="background:#ef4444;color:white;border:none;padding:6px 10px;border-radius:4px;cursor:pointer;font-size:12px;font-weight:600">Remove</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div style="margin-top:16px;display:flex;gap:12px;align-items:center;justify-content:space-between">
                        <div>
                            <button type="submit" class="filter-btn">Update Cart</button>
                            <a href="index.php" class="filter-btn" style="background:var(--border);color:var(--text);text-decoration:none;padding:12px;border-radius:8px;display:inline-block;margin-left:8px">Continue Shopping</a>
                        </div>
                            <div id="cartTotal" style="font-weight:800;font-size:18px">Total: ₹<?= number_format($total,2) ?></div>
                    </div>
                </form>

                <div style="margin-top:18px;text-align:right">
                    <a href="checkout.php" class="filter-btn primary">Proceed to Checkout</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <div id="toast" aria-live="polite" style="position:fixed;right:18px;bottom:18px;z-index:9999"></div>

    <script>
    // AJAX cart update handler + toast
    (function(){
        const form = document.getElementById('cartForm');
        if (!form) return;

        function showToast(msg){
            const t = document.getElementById('toast');
            const el = document.createElement('div');
            el.className = 'toast';
            el.textContent = msg;
            t.appendChild(el);
            setTimeout(()=>{ el.classList.add('visible'); }, 10);
            setTimeout(()=>{ el.classList.remove('visible'); setTimeout(()=>el.remove(),300); }, 3000);
        }

        // Handle remove buttons
        document.querySelectorAll('.remove-btn').forEach(btn => {
            btn.addEventListener('click', function(e){
                e.preventDefault();
                const id = this.getAttribute('data-id');
                const data = new FormData();
                data.append('qty', { [id]: 0 });
                // Set quantity to 0 for removal
                const qtyInputs = {};
                document.querySelectorAll('.qty-input').forEach(inp => {
                    qtyInputs[inp.getAttribute('data-id')] = inp.value;
                });
                qtyInputs[id] = 0;
                // Submit form with removed item
                const form = document.getElementById('cartForm');
                const formData = new FormData(form);
                formData.set(`qty[${id}]`, 0);
                fetch('update_cart.php', { method: 'POST', body: formData, headers: {'X-Requested-With':'XMLHttpRequest'} })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        document.querySelector(`tr:has(.remove-btn[data-id="${id}"])`).remove();
                        const countEl = document.querySelector('#cartCount');
                        if (countEl) countEl.textContent = res.count;
                        const totalEl = document.getElementById('cartTotal');
                        if (totalEl) totalEl.textContent = 'Total: ₹' + Number(res.total).toFixed(2);
                        showToast('Item removed');
                        // Check if cart is empty
                        if (document.querySelectorAll('td[class="item-subtotal"]').length === 0) {
                            location.reload();
                        }
                    }
                }).catch(err => showToast('Error removing item'));
            });
        });

        form.addEventListener('submit', function(e){
            e.preventDefault();
            const data = new FormData(form);
            fetch('update_cart.php', { method: 'POST', body: data, headers: {'X-Requested-With':'XMLHttpRequest'} })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    // update header cart count if present
                    const countEl = document.querySelector('#cartCount');
                    if (countEl) countEl.textContent = res.count;

                    // update per-item subtotals
                    if (res.items) {
                        Object.keys(res.items).forEach(id => {
                            const it = res.items[id];
                            const cell = document.querySelector('.item-subtotal[data-id="'+id+'"]');
                            if (cell) cell.textContent = '₹' + Number(it.subtotal).toFixed(2);
                        });
                    }

                    // update total
                    const totalEl = document.getElementById('cartTotal');
                    if (totalEl) totalEl.textContent = 'Total: ₹' + Number(res.total).toFixed(2);

                    showToast('Cart updated');
                } else {
                    showToast('Failed to update cart');
                }
            }).catch(err => { console.error(err); showToast('Error updating cart'); });
        });
    })();
    </script>
</body>
</html>
