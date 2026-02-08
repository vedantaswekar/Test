# 🎯 FakeStore Product Filter System - COMPLETE GUIDE

## How It Works (Step-by-Step Flow)

```
1. User visits shop.php
   ↓
2. Page loads HTML + JavaScript
   ↓
3. JavaScript calls fetch('products_api.php')
   ↓
4. PHP returns JSON: { products: [...], count: 20 }
   ↓
5. JavaScript displays products on page using template
   ↓
6. User types in search box / changes filters
   ↓
7. JavaScript filters the products CLIENT-SIDE (NO page reload)
   ↓
8. Products update instantly!
```

---

## File Breakdown

### 1. **products_api.php** (PHP Backend - Returns JSON)
```
What it does:
- Reads products_cache.json file
- Gets filter parameters from URL query string
- Filters products based on: search, category, price, rating
- Returns JSON response with filtered products

Example URL:
/products_api.php?search=backpack&category=men's%20clothing&price=200&rating=4

Example Response:
{
  "success": true,
  "count": 3,
  "products": [
    { "id": 1, "title": "...", "price": 109.95, "rating": {...} },
    ...
  ]
}
```

### 2. **shop.php** (HTML - Page Structure)
```
What it does:
- Displays the page layout
- Includes filter controls (search box, category dropdown, price slider, rating dropdown)
- Has a container for products (<main class="products">)
- Loads JavaScript file

Structure:
┌─────────────────────────────┐
│  HEADER (Logo + Cart Count) │
├──────────┬──────────────────┤
│ FILTERS  │  PRODUCTS GRID   │
│          │                  │
│ Search   │ [Product Cards]  │
│ Category │ [Product Cards]  │
│ Price    │ [Product Cards]  │
│ Rating   │ [Product Cards]  │
└──────────┴──────────────────┘
```

### 3. **products.js** (JavaScript - Main Logic)

#### A. Load Products (AJAX Fetch)
```javascript
// Step 1: When page loads
document.addEventListener('DOMContentLoaded', loadProducts);

// Step 2: Fetch from PHP
fetch('products_api.php')
  .then(resp => resp.json())
  .then(data => allProducts = data.products)
  .then(() => displayProducts(allProducts))
```

#### B. Display Products
```javascript
// Step 3: Create HTML for each product
products.forEach(product => {
  html += `
    <article class="card">
      <img src="${product.image}">
      <h4>${product.title}</h4>
      <div class="price">₹${product.price}</div>
      <div class="rating">⭐ ${product.rating.rate}</div>
      <button>Add to Cart</button>
    </article>
  `;
});

// Step 4: Insert HTML into page
document.querySelector('.products').innerHTML = html;
```

#### C. Apply Filters
```javascript
// Step 5: When user types/selects filters
searchInput.addEventListener('keyup', applyFilters);
categorySelect.addEventListener('change', applyFilters);
priceRange.addEventListener('input', applyFilters);
ratingSelect.addEventListener('change', applyFilters);

// Step 6: Filter products CLIENT-SIDE (instant!)
const filtered = allProducts.filter(product => {
  if (search && product.title.toLowerCase().indexOf(search) === -1) return false;
  if (category && product.category !== category) return false;
  if (parseFloat(product.price) > maxPrice) return false;
  if (product.rating.rate < minRating) return false;
  return true;
});

// Step 7: Display filtered products
displayProducts(filtered);
```

---

## Complete Filter Flow

### Scenario: User searches for "backpack"

```
1. User types "backpack" in search box
   ↓
2. JavaScript 'keyup' event fires
   ↓
3. applyFilters() function runs
   ↓
4. Gets all filter values:
   - search = "backpack"
   - category = "" (all)
   - maxPrice = 1000
   - minRating = 0
   ↓
5. Filters allProducts array:
   ✓ Product 1: "Fjallraven - Foldsack No. 1 Backpack"
     - Title contains "backpack" ✓
     - Show this product
   ✗ Product 2: "Mens Casual Premium Slim Fit T-Shirts"
     - Title doesn't contain "backpack" ✗
     - Hide this product
   ↓
6. displayProducts() called with filtered results
   ↓
7. HTML updated with only matching products
   ↓
8. ALL INSTANT - NO PAGE RELOAD!
```

---

## Code Flow Diagram

```
┌─ Index.html ─────────────────────────┐
│                                       │
│  <body>                               │
│    <filters>                          │
│      <input id="searchInput">         │◄─────┐
│      <select id="categorySelect">     │◄─────┼─ User Interacts
│      <input id="priceRange">          │◄─────┤  (typing/selecting)
│      <select id="ratingSelect">       │◄─────┘
│    </filters>                         │
│                                       │
│    <main class="products">            │◄────┐
│      <!-- Products inserted here -->  │     │
│    </main>                            │     │
│  </body>                              │     │
│                                       │     │
│  <script src="products.js">           │     │
└───────────────────────────────────────┘     │
                                              │
┌─ products.js ─────────────────────────┐    │
│                                        │    │
│  1. loadProducts()                     │    │
│     → fetch products_api.php           │    │
│     → store in allProducts             │    │
│     → displayProducts(allProducts)     │    │
│                                        │    │
│  2. setupEventListeners()              │    │
│     → searchInput.addEventListener()   │◄───┴─ Update Filters
│     → categorySelect.addEventListener()│
│     → priceRange.addEventListener()    │
│     → ratingSelect.addEventListener()  │
│                                        │
│  3. applyFilters()                     │
│     → Filter allProducts locally       │
│     → displayProducts(filtered)        │◄─── Display Results
│                                        │
└────────────────────────────────────────┘


┌─ products_api.php ────────────────────┐
│                                       │
│  Read products_cache.json             │
│  ↓                                    │
│  If filters provided:                 │
│    - Search product titles            │
│    - Filter by category               │
│    - Filter by price                  │
│    - Filter by rating                 │
│  ↓                                    │
│  Return JSON: {                       │
│    success: true,                     │
│    count: 5,                          │
│    products: [...]                    │
│  }                                    │
│                                       │
└───────────────────────────────────────┘
```

---

## Key Features

### ✅ Client-Side Filtering (NO PAGE RELOAD)
- User types/selects → JavaScript instantly filters from memory
- Smooth, responsive experience
- No server calls needed for filtering

### ✅ Fallback to Server (if needed)
- You can also use `products_api.php?search=x&category=y` directly in URL
- Server will filter and return JSON
- Good for mobile/sharing filter links

### ✅ Real-Time Search
- Users see results as they type (keyup event)
- Includes price slider with instant feedback
- Category and rating dropdowns update instantly

### ✅ Clear Filters Button
- Resets all filters to defaults
- Shows all products again

---

## How to Use

### 1. Visit the Shop:
```
http://localhost/Test/PHP/shop.php
```

### 2. Products should load automatically

### 3. Search/Filter as you type:
- Type "shirt" → See only shirts
- Select "electronics" → See only electronics
- Move price slider → Filter by max price
- Select "4⭐ & above" → See only high-rated products

### 4. Combination filters work:
- Search "jacket" + Category "women's clothing" = Women's jackets only
- Category "electronics" + Price ₹100 = Electronics under ₹100

---

## Technical Notes

### Why Client-Side Filtering?
- **Fast**: No server round-trip, instant results
- **Smooth**: No page flicker/reload
- **Better UX**: Feels like a real app

### Why Keep products_api.php?
- Provides initial data via AJAX
- Can be reused for other features (pagination, etc.)
- Shows how to build a REST API in PHP

### Security:
- `escapeHtml()` prevents XSS attacks
- Filters work on read-only data (allProducts)
- Session cart is server-side secure

---

## Next Steps (Optional Enhancements)

1. **Add sorting**: By price, rating, newest
2. **Add pagination**: Load 12 products per page
3. **Remember filters**: Save user's filters in localStorage
4. **Wishlist**: Add to favorites feature
5. **Product details**: Click product to see details
6. **URL sharing**: Share filtered results via URL parameters

