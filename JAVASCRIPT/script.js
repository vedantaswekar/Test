let products = [];
let cart = JSON.parse(localStorage.getItem("cart")) || [];

document.getElementById("cartCount").innerText = cart.length;

fetch("https://fakestoreapi.com/products")
  .then(res => res.json())
  .then(data => {
    products = data;
    loadCategories();
    displayProducts(products);
  });
function displayProducts(list) {
  const container = document.getElementById("products");
  container.innerHTML = "";

  list.forEach(product => {
    container.innerHTML += `
      <div class="card">
        <img src="${product.image}">
        <h4>${product.title}</h4>
        <p>₹${product.price}</p>
        <p>⭐ ${product.rating.rate}</p>
        <button onclick="addToCart(${product.id})">Add to Cart</button>
      </div>
    `;
  });
}
function loadCategories() {
  const select = document.getElementById("category");
  const categories = [...new Set(products.map(p => p.category))];

  categories.forEach(cat => {
    select.innerHTML += `<option value="${cat}">${cat}</option>`;
  });
}
document.querySelectorAll("#search, #category, #price, #rating")
  .forEach(el => el.addEventListener("input", applyFilters));

function applyFilters() {
  let search = document.getElementById("search").value.toLowerCase();
  let category = document.getElementById("category").value;
  let price = document.getElementById("price").value;
  let rating = document.getElementById("rating").value;

 document.getElementById("priceValue").innerText = `₹${price}`;


  let filtered = products.filter(p => {
    return (
      p.title.toLowerCase().includes(search) &&
      (category === "all" || p.category === category) &&
      p.price <= price &&
      p.rating.rate >= rating
    );
  });

  displayProducts(filtered);
}
function addToCart(id) {
  const product = products.find(p => p.id === id);
  cart.push(product);
  localStorage.setItem("cart", JSON.stringify(cart));
  document.getElementById("cartCount").innerText = cart.length;
}
