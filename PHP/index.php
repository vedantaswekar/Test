<?php
session_start();
$cartCount = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cartCount += $qty;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FakeStore - Products (Instant Filtering)</title>
    <link rel="stylesheet" href="style_update.css">
</head>
<body>
    <header class="header" role="banner">
        <div class="logo">🛍️ FakeStore</div>
        <div style="display:flex;gap:12px;align-items:center">
            <a href="admin_orders.php" style="color:inherit;text-decoration:none;font-size:12px;padding:4px 8px;border-radius:4px;border:1px solid var(--border);transition:all 0.2s" onclick="this.style.background='var(--accent)';this.style.color='white'">📋 Orders</a>
            <a class="cart" href="cart.php" aria-label="Shopping cart">
                🛒 Cart <span id="cartCount" aria-label="Items in cart"><?= (int)$cartCount ?></span>
            </a>
        </div>
    </header>

    <div class="container">
        <!-- FILTERS SIDEBAR -->
        <aside class="filters" role="complementary" aria-label="Product filters">
            <h2 class="filter-title">🔍 Filters</h2>
            
            <!-- SEARCH SECTION -->
            <section class="filter-section" aria-labelledby="searchHeading">
                <h3 id="searchHeading" class="filter-section-title">Search</h3>
                <div class="filter-group">
                    <input type="text" id="searchInput" placeholder="Search products..." aria-label="Search for products by name or description" aria-describedby="searchHelp">
                    <small id="searchHelp" class="sr-only">Type to search products instantly. Press Escape to clear.</small>
                </div>
            </section>

            <div class="filter-divider"></div>

            <!-- CATEGORY SECTION -->
            <section class="filter-section" aria-labelledby="categoryHeading">
                <h3 id="categoryHeading" class="filter-section-title">Category</h3>
                <div class="filter-group">
                    <select id="categorySelect" aria-label="Filter by product category">
                        <option value="">All Categories</option>
                        <option value="electronics">Electronics</option>
                        <option value="jewelery">Jewelery</option>
                        <option value="men's clothing">Men's Clothing</option>
                        <option value="women's clothing">Women's Clothing</option>
                    </select>
                </div>
            </section>

            <div class="filter-divider"></div>

            <!-- PRICE SECTION -->
            <section class="filter-section" aria-labelledby="priceHeading">
                <h3 id="priceHeading" class="filter-section-title">Price</h3>
                <div class="filter-group">
                    <label for="priceRange" class="mini-label">Max Price: <strong>₹<span id="priceValue">1000</span></strong></label>
                    <input type="range" id="priceRange" min="0" max="1000" value="1000" class="price-slider" aria-label="Filter by maximum price">
                    <div class="price-info" aria-live="polite">₹0 - ₹1000</div>
                </div>
            </section>

            <div class="filter-divider"></div>

            <!-- RATING SECTION -->
            <section class="filter-section" aria-labelledby="ratingHeading">
                <h3 id="ratingHeading" class="filter-section-title">Rating</h3>
                <div class="filter-group">
                    <select id="ratingSelect" aria-label="Filter by minimum rating">
                        <option value="">All Ratings</option>
                        <option value="3">⭐ 3 Stars & above</option>
                        <option value="4">⭐⭐ 4 Stars & above</option>
                    </select>
                </div>
            </section>

            <div class="filter-divider"></div>

            <!-- ACTIONS -->
            <button id="clearFilters" class="filter-btn primary" aria-label="Clear all filters and reset to default">🔄 Reset All</button>
        </aside>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <!-- PRODUCTS HEADER -->
            <div class="products-header">
                <div class="header-left">
                    <span class="results-count" id="resultsCount" aria-live="polite" aria-label="Number of products shown">Loading...</span>
                </div>
                <div class="header-right">
                    <div class="sort-control">
                        <label for="sortSelect" class="sort-label">Sort:</label>
                        <select id="sortSelect" aria-label="Sort products by popularity, price, rating, or name">
                            <option value="default">Most Popular</option>
                            <option value="price-low">Price: Low to High</option>
                            <option value="price-high">Price: High to Low</option>
                            <option value="rating">Rating: High to Low</option>
                            <option value="name">Name: A to Z</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- PRODUCTS GRID -->
            <main class="products" role="main" aria-label="Product list">
                <div class="loading">Loading products...</div>
            </main>
        </div>
    </div>

    <script>
        // ============================================
        // GLOBAL STATE & DOM ELEMENT CACHE
        // ============================================
        let allProducts = [];
        let filterTimeout = null; // Debounce timer

        const DOM = {
            searchInput: document.getElementById('searchInput'),
            categorySelect: document.getElementById('categorySelect'),
            priceRange: document.getElementById('priceRange'),
            priceValue: document.getElementById('priceValue'),
            priceInfo: document.querySelector('.price-info'),
            ratingSelect: document.getElementById('ratingSelect'),
            sortSelect: document.getElementById('sortSelect'),
            clearFiltersBtn: document.getElementById('clearFilters'),
            productsContainer: document.querySelector('.products'),
            resultsCount: document.getElementById('resultsCount'),
            cartCount: document.getElementById('cartCount')
        };

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadProducts();
            setupEventListeners();
        });

        // ============================================
        // STEP 1: FETCH PRODUCTS FROM API
        // ============================================
        function loadProducts() {
            showLoadingState();
            
            fetch('products_api.php')
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data.success && Array.isArray(data.products)) {
                        allProducts = data.products;
                        console.log(`✓ Loaded ${allProducts.length} products`);
                        displayProducts(allProducts);
                    } else {
                        throw new Error(data.error || 'Invalid response format');
                    }
                })
                .catch(error => {
                    console.error('❌ Load error:', error);
                    showError('Failed to load products. Please refresh the page.');
                });
        }

        // ============================================
        // STEP 2: DISPLAY PRODUCTS
        // ============================================
        function displayProducts(products) {
            // Update header with filter information
            updateResultsHeader(products);
            
            if (products.length === 0) {
                DOM.productsContainer.innerHTML = '<div class="no-products">😔 No products found. Try adjusting your filters.</div>';
                return;
            }

            // Build product cards
            const html = products.map(product => `
                <article class="card" role="article" aria-label="Product: ${escapeHtml(product.title)}">
                    <div class="card-image">
                        <img src="${escapeHtml(product.image)}" alt="${escapeHtml(product.title)}">
                    </div>
                    <div class="card-content">
                        <h4 class="card-title">${escapeHtml(product.title)}</h4>
                        <div class="card-meta">
                            <span class="price" aria-label="Price">₹${parseFloat(product.price).toFixed(2)}</span>
                            <span class="rating" aria-label="Rating ${product.rating.rate} out of 5">⭐ ${product.rating.rate}</span>
                        </div>
                        <div style="font-size:11px;color:var(--success);margin:8px 0;font-weight:600">✓ In Stock</div>
                        <div style="display:flex;gap:8px;align-items:center">
                            <input class="qty-input" type="number" min="1" max="10" value="1" data-id="${product.id}" style="width:56px;padding:6px;border:1px solid var(--border);border-radius:6px;text-align:center;font-weight:600;font-size:13px">
                            <button class="add-btn" data-id="${product.id}" data-title="${escapeHtml(product.title)}" aria-label="Add ${escapeHtml(product.title)} to cart">Add</button>
                        </div>
                    </div>
                </article>
            `).join('');

            DOM.productsContainer.innerHTML = html;
            attachAddToCartHandlers();
        }

        /**
         * Update header with current filter status
         */
        function updateResultsHeader(products) {
            const search = DOM.searchInput.value;
            const category = DOM.categorySelect.value;
            const rating = DOM.ratingSelect.value;
            
            let description = '';
            if (search) {
                description = `Results for "<strong>${escapeHtml(search)}</strong>"`;
            } else if (category) {
                description = `<strong>${capitalizeFirst(category)}</strong> Products`;
            } else {
                description = 'All Products';
            }
            
            DOM.resultsCount.innerHTML = `${description} <span class="product-count">(${products.length})</span>`;
        }

        // ============================================
        // HANDLE ADD TO CART
        // ============================================
        function attachAddToCartHandlers() {
            document.querySelectorAll('.add-btn').forEach(btn => {
                btn.addEventListener('click', handleAddToCart);
            });
        }

        async function handleAddToCart(e) {
            e.preventDefault();
            const btn = e.target;
            const productId = btn.getAttribute('data-id');
            const productTitle = btn.getAttribute('data-title');
            const originalText = btn.textContent;
            
            // Get quantity from nearby input
            const qtyInput = btn.closest('.card-content').querySelector('.qty-input');
            const qty = qtyInput ? Math.max(1, parseInt(qtyInput.value) || 1) : 1;

            btn.disabled = true;
            btn.textContent = '⏳ Adding...';

            try {
                const formData = new FormData();
                formData.append('id', productId);
                formData.append('qty', qty);

                const response = await fetch('add_to_cart.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) throw new Error('Network response failed');
                const data = await response.json();

                if (data.count !== undefined) {
                    DOM.cartCount.textContent = data.count;
                    btn.textContent = '✓ Added!';
                    btn.classList.add('success');
                    
                    // show toast
                    showToast(`Added ${qty} item${qty > 1 ? 's' : ''} to cart`);
                    
                    // Reset quantity input
                    if (qtyInput) qtyInput.value = 1;
                    
                    setTimeout(() => {
                        btn.textContent = originalText;
                        btn.classList.remove('success');
                        btn.disabled = false;
                    }, 1500);
                } else {
                    throw new Error('Invalid response');
                }
            } catch (error) {
                console.error('❌ Add to cart error:', error);
                btn.textContent = '❌ Error';
                btn.style.backgroundColor = '#ef4444';
                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.style.backgroundColor = '';
                    btn.disabled = false;
                }, 2000);
            }
        }

        // ============================================
        // STEP 3: APPLY FILTERS (DEBOUNCED SEARCH)
        // ============================================
        function applyFilters() {
            const search = DOM.searchInput.value.toLowerCase().trim();
            const category = DOM.categorySelect.value;
            const maxPrice = parseFloat(DOM.priceRange.value);
            const minRating = DOM.ratingSelect.value ? parseFloat(DOM.ratingSelect.value) : 0;
            const sortBy = DOM.sortSelect.value;

            // Filter products locally
            let filtered = allProducts.filter(product => {
                // Search filter (case-insensitive, substring match)
                if (search && !product.title.toLowerCase().includes(search)) return false;

                // Category filter
                if (category && product.category !== category) return false;

                // Price filter
                if (parseFloat(product.price) > maxPrice) return false;

                // Rating filter
                if (minRating && product.rating.rate < minRating) return false;

                return true;
            });

            // Apply sorting
            filtered = sortProducts(filtered, sortBy);
            displayProducts(filtered);
        }

        /**
         * Debounced version of applyFilters (delays execution)
         */
        function debouncedApplyFilters() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(applyFilters, 300);
        }

        // ============================================
        // SORT PRODUCTS
        // ============================================
        function sortProducts(products, sortBy) {
            const sorted = [...products]; // Avoid mutation
            
            const sortFunctions = {
                'price-low': (a, b) => parseFloat(a.price) - parseFloat(b.price),
                'price-high': (a, b) => parseFloat(b.price) - parseFloat(a.price),
                'rating': (a, b) => b.rating.rate - a.rating.rate,
                'name': (a, b) => a.title.localeCompare(b.title),
                'default': (a, b) => a.id - b.id
            };
            
            const compareFn = sortFunctions[sortBy] || sortFunctions['default'];
            return sorted.sort(compareFn);
        }

        // ============================================
        // STEP 4: SETUP EVENT LISTENERS
        // ============================================
        function setupEventListeners() {
            // Search: debounced to avoid excessive filtering
            DOM.searchInput.addEventListener('keyup', debouncedApplyFilters);
            DOM.searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    DOM.searchInput.value = '';
                    applyFilters();
                }
            });

            // Category selection
            DOM.categorySelect.addEventListener('change', applyFilters);

            // Price range
            DOM.priceRange.addEventListener('input', (e) => {
                const value = e.target.value;
                DOM.priceValue.textContent = value;
                if (DOM.priceInfo) DOM.priceInfo.textContent = `₹0 - ₹${value}`;
                applyFilters();
            });

            // Rating selection
            DOM.ratingSelect.addEventListener('change', applyFilters);

            // Sort selection
            DOM.sortSelect.addEventListener('change', applyFilters);

            // Clear filters button
            DOM.clearFiltersBtn.addEventListener('click', resetAllFilters);
        }

        function resetAllFilters() {
            DOM.searchInput.value = '';
            DOM.categorySelect.value = '';
            DOM.priceRange.value = '1000';
            DOM.priceValue.textContent = '1000';
            if (DOM.priceInfo) DOM.priceInfo.textContent = '₹0 - ₹1000';
            DOM.ratingSelect.value = '';
            DOM.sortSelect.value = 'default';
            applyFilters();
        }

        // ============================================
        // HELPER FUNCTIONS
        // ============================================
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function capitalizeFirst(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        function showLoadingState() {
            DOM.productsContainer.innerHTML = '<div class="loading"><span class="spinner"></span> Loading products...</div>';
        }

        function showError(message) {
            DOM.productsContainer.innerHTML = `<div class="error"><span>⚠️</span> ${escapeHtml(message)}</div>`;
        }

        // Toast helper
        function showToast(msg){
            let t = document.getElementById('toast');
            if (!t) {
                t = document.createElement('div'); t.id = 'toast'; t.setAttribute('aria-live','polite');
                t.style.position = 'fixed'; t.style.right = '18px'; t.style.bottom = '18px'; t.style.zIndex = 9999;
                document.body.appendChild(t);
            }
            const el = document.createElement('div'); el.className = 'toast'; el.textContent = msg;
            t.appendChild(el);
            setTimeout(()=> el.classList.add('visible'), 10);
            setTimeout(()=> { el.classList.remove('visible'); setTimeout(()=> el.remove(),300); }, 3000);
        }
    </script>
</body>
</html>
