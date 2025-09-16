// Global variables
let products = [];
let cart = [];
let currentCategory = 'all';

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    checkLogin();
    loadProducts();
    updateCartCount();
});

// Load products from backend
function loadProducts() {
    fetch('backend/products.php')
        .then(response => response.json())
        .then(data => {
            products = data;
            displayProducts(products);
        })
        .catch(error => {
            console.error('Error loading products:', error);
        });
}

// Display products in the product section
function displayProducts(productsArray) {
    const productsContainer = document.getElementById('products');
    productsContainer.innerHTML = '';
    
    productsArray.forEach(product => {
        const productCard = document.createElement('div');
        productCard.className = 'card';
        productCard.innerHTML = `
            <img src="images/${product.image}" alt="${product.name}" onclick="showProductDetail(${product.id})">
            <h3>${product.name}</h3>
            <p>₹${product.price} / kg</p>
            <button onclick="addToCart(${product.id})">Add to Cart</button>
        `;
        productsContainer.appendChild(productCard);
    });
}

// Other functions from the previous response (addToCart, filterCategory, etc.)
// Include all the JavaScript functions from the previous response here