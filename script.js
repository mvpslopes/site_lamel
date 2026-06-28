// Product Data
const products = [
    {
        id: 1,
        name: "Body Tule",
        description: "Body em tule com design xadrez elegante e sofisticado",
        price: 69.99,
        size: "Tamanho único",
        image: "produtos/Body Tule/body_tule (1).jpeg",
        images: [
            "produtos/Body Tule/body_tule (1).jpeg",
            "produtos/Body Tule/body_tule (2).jpeg",
            "produtos/Body Tule/body_tule (3).jpeg"
        ]
    },
    {
        id: 2,
        name: "Conjunto Bella",
        description: "Blusa social + calça pantalona. Tecido: Viscolinho. Veste até o tamanho 44",
        price: 199.90,
        size: "Tamanho único (até 44)",
        image: "produtos/Conjunto Bella/cojunto_bella (1).jpeg",
        images: [
            "produtos/Conjunto Bella/cojunto_bella (1).jpeg",
            "produtos/Conjunto Bella/cojunto_bella (2).jpeg",
            "produtos/Conjunto Bella/cojunto_bella (3).jpeg",
            "produtos/Conjunto Bella/cojunto_bella (4).jpeg",
            "produtos/Conjunto Bella/cojunto_bella (5).jpeg"
        ]
    },
    {
        id: 3,
        name: "T-shirt Premium",
        description: "Camiseta premium com acabamento de alta qualidade",
        price: 79.99,
        size: "Tamanho único",
        image: "produtos/T-shirt Premium/t-shirt_premium (1).jpeg",
        images: [
            "produtos/T-shirt Premium/t-shirt_premium (1).jpeg",
            "produtos/T-shirt Premium/t-shirt_premium (2).jpeg"
        ]
    },
    {
        id: 4,
        name: "Vestido Ayla",
        description: "Vestido com bojo, elástico e regulagem. Tecido: Viscolinho. Veste até o tamanho 42/44",
        price: 179.99,
        size: "Tamanho único (até 42/44)",
        image: "produtos/Vestido Ayla/vestido_ayla (1).jpeg",
        images: [
            "produtos/Vestido Ayla/vestido_ayla (1).jpeg",
            "produtos/Vestido Ayla/vestido_ayla (2).jpeg",
            "produtos/Vestido Ayla/vestido_ayla (3).jpeg",
            "produtos/Vestido Ayla/vestido_ayla (4).jpeg"
        ]
    },
    {
        id: 5,
        name: "Vestido Fluyte",
        description: "Linha premium. Vestido em seda com estampa diferenciada. Tamanho único veste do P ao G1",
        price: 269.90,
        size: "Tamanho único (P ao G1)",
        image: "produtos/Vestido Fluyte/vestido_fluyte.jpeg",
        images: [
            "produtos/Vestido Fluyte/vestido_fluyte.jpeg"
        ]
    }
];

// Shopping Cart
let cart = [];

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Hide splash screen after animation
    setTimeout(() => {
        const splashScreen = document.getElementById('splash-screen');
        const mainContent = document.getElementById('main-content');
        if (splashScreen) {
            splashScreen.style.display = 'none';
        }
        if (mainContent) {
            mainContent.classList.remove('hidden');
        }
    }, 2500);

    // Load products
    renderProducts();

    // Load cart from localStorage
    loadCart();
});

// Render Products
function renderProducts() {
    const productsGrid = document.getElementById('products-grid');
    
    products.forEach(product => {
        const productCard = document.createElement('div');
        productCard.className = 'product-card';
        productCard.innerHTML = `
            <img src="${product.image}" alt="${product.name}" class="product-image">
            <div class="product-info">
                <h3 class="product-name">${product.name}</h3>
                <p class="product-description">${product.description}</p>
                <span class="product-size">${product.size}</span>
                <p class="product-price">R$ ${product.price.toFixed(2).replace('.', ',')}</p>
                <button class="add-to-cart" onclick="addToCart(${product.id})">Adicionar ao Carrinho</button>
            </div>
        `;
        productsGrid.appendChild(productCard);
    });
}

// Add to Cart
function addToCart(productId) {
    const product = products.find(p => p.id === productId);
    const existingItem = cart.find(item => item.id === productId);

    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            image: product.image,
            quantity: 1
        });
    }

    saveCart();
    updateCartUI();
    showNotification(`${product.name} adicionado ao carrinho!`);
}

// Remove from Cart
function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    saveCart();
    updateCartUI();
}

// Update Quantity
function updateQuantity(productId, change) {
    const item = cart.find(item => item.id === productId);
    
    if (item) {
        item.quantity += change;
        
        if (item.quantity <= 0) {
            removeFromCart(productId);
        } else {
            saveCart();
            updateCartUI();
        }
    }
}

// Save Cart to localStorage
function saveCart() {
    localStorage.setItem('lamel-cart', JSON.stringify(cart));
}

// Load Cart from localStorage
function loadCart() {
    const savedCart = localStorage.getItem('lamel-cart');
    if (savedCart) {
        cart = JSON.parse(savedCart);
        updateCartUI();
    }
}

// Update Cart UI
function updateCartUI() {
    const cartCount = document.getElementById('cart-count');
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');

    // Update cart count
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCount.textContent = totalItems;

    // Update cart items
    if (cart.length === 0) {
        cartItems.innerHTML = `
            <div class="empty-cart">
                <div class="empty-cart-icon">🛒</div>
                <p>Seu carrinho está vazio</p>
            </div>
        `;
    } else {
        cartItems.innerHTML = cart.map(item => `
            <div class="cart-item">
                <img src="${item.image}" alt="${item.name}" class="cart-item-image">
                <div class="cart-item-info">
                    <h4 class="cart-item-name">${item.name}</h4>
                    <p class="cart-item-price">R$ ${item.price.toFixed(2).replace('.', ',')}</p>
                    <div class="cart-item-quantity">
                        <button class="quantity-btn" onclick="updateQuantity(${item.id}, -1)">-</button>
                        <span class="quantity-value">${item.quantity}</span>
                        <button class="quantity-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
                    </div>
                    <button class="remove-item" onclick="removeFromCart(${item.id})">Remover</button>
                </div>
            </div>
        `).join('');
    }

    // Update total
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    cartTotal.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
}

// Toggle Cart
function toggleCart() {
    const cartSidebar = document.getElementById('cart-sidebar');
    const cartOverlay = document.getElementById('cart-overlay');
    
    cartSidebar.classList.toggle('open');
    cartOverlay.classList.toggle('open');
}

// Checkout via WhatsApp
function checkoutWhatsApp() {
    if (cart.length === 0) {
        showNotification('Seu carrinho está vazio!');
        return;
    }

    const phoneNumber = '5531999582428'; // (31) 99958-2428
    
    // Build message with emojis
    let message = '👋 *Olá! Gostaria de fazer um pedido na LaMel*\n\n';
    message += '📋 *Meus Produtos:*\n';
    message += '━━━━━━━━━━━━━━━━━━\n';
    
    cart.forEach((item, index) => {
        const subtotal = item.price * item.quantity;
        message += `${index + 1}. ✨ *${item.name}*\n`;
        message += `   💰 R$ ${item.price.toFixed(2).replace('.', ',')} x ${item.quantity}\n`;
        message += `   📦 Subtotal: R$ ${subtotal.toFixed(2).replace('.', ',')}\n\n`;
    });
    
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    message += '━━━━━━━━━━━━━━━━━━\n';
    message += `💵 *Total: R$ ${total.toFixed(2).replace('.', ',')}*\n\n`;
    message += '📍 *Informações de Contato:*\n';
    message += '📱 Telefone: (31) 99958-2428\n';
    message += '🏪 Endereço: Shopping Centro- 28, Loja 7 - Ouro Branco\n\n';
    message += '🙏 Aguardo confirmação do pedido!';
    
    // Encode message for URL
    const encodedMessage = encodeURIComponent(message);
    
    // Open WhatsApp
    const whatsappURL = `https://wa.me/${phoneNumber}?text=${encodedMessage}`;
    window.open(whatsappURL, '_blank');
    
    // Clear cart after checkout
    cart = [];
    saveCart();
    updateCartUI();
    toggleCart();
    
    showNotification('Pedido enviado para WhatsApp!');
}

// Show Notification
function showNotification(message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #622331;
        color: white;
        padding: 15px 25px;
        border-radius: 5px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
        font-size: 14px;
    `;
    notification.textContent = message;
    
    // Add animation keyframes
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(notification);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Smooth scroll for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Scroll animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-in');
        }
    });
}, observerOptions);

// Observe elements for animation
document.addEventListener('DOMContentLoaded', () => {
    const animatedElements = document.querySelectorAll('.product-card, .contact-card, .section-title');
    animatedElements.forEach(el => observer.observe(el));
});

// Header scroll effect
let lastScroll = 0;
window.addEventListener('scroll', () => {
    const header = document.querySelector('.header');
    const currentScroll = window.pageYOffset;
    
    if (currentScroll > 100) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
    
    lastScroll = currentScroll;
});
