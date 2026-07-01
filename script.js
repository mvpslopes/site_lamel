// Product Data (fallback local quando a API não estiver disponível)
const fallbackProducts = [
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
        ],
        badge: "Novo"
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
        ],
        badge: "Mais Vendido"
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
        ],
        badge: "Exclusivo"
    }
];

let products = [...fallbackProducts];
let collections = [];
let productsGridClickBound = false;

function normalizeId(id) {
    const n = Number(id);
    return Number.isNaN(n) ? id : n;
}

function findProduct(productId) {
    const id = normalizeId(productId);
    return products.find(p => normalizeId(p.id) === id);
}

/**
 * Fatores de total na maquininha (referência: R$ 200,00).
 * 6x–12x: valores exatos da foto; 2x–5x: interpolados entre à vista e 6x.
 */
const INSTALLMENT_TOTAL_FACTORS = {
    1: 1.0000,
    2: 1.02864,
    3: 1.05728,
    4: 1.08592,
    5: 1.11456,
    6: 1.14320,
    7: 1.16720,
    8: 1.16730,
    9: 1.19690,
    10: 1.20650,
    11: 1.20660,
    12: 1.22110
};

function getInstallmentQuote(price, months) {
    const n = Math.min(12, Math.max(1, Math.round(months)));
    const factor = INSTALLMENT_TOTAL_FACTORS[n] || 1;
    const total = Math.round(price * factor * 100) / 100;
    const perMonth = Math.round((total / n) * 100) / 100;

    return { months: n, perMonth, total, factor };
}

function formatInstallmentLine(price, months) {
    const quote = getInstallmentQuote(price, months);
    if (months === 1) {
        return `${formatCartMoney(quote.total)} à vista`;
    }
    return `${months}x de ${formatCartMoney(quote.perMonth)}`;
}

function getProductInstallmentSummary(price) {
    const quote12 = getInstallmentQuote(price, 12);
    return `ou até 12x de ${formatCartMoney(quote12.perMonth)}`;
}

function buildInstallmentTableHTML(price) {
    const rows = Array.from({ length: 12 }, (_, index) => {
        const months = index + 1;
        const quote = getInstallmentQuote(price, months);
        const totalCell = months === 1
            ? '<span class="installment-cash">à vista</span>'
            : formatCartMoney(quote.total);

        return `
            <tr>
                <td>${months}x</td>
                <td>${formatCartMoney(quote.perMonth)}</td>
                <td>${totalCell}</td>
            </tr>
        `;
    }).join('');

    return `
        <div class="installment-table-wrap">
            <p class="installment-table-title">Parcelamento no cartão</p>
            <table class="installment-table">
                <thead>
                    <tr>
                        <th>Parcelas</th>
                        <th>Valor</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
            <p class="installment-table-note">Valores calculados conforme tabela da maquininha (ref. R$ 200,00).</p>
        </div>
    `;
}

function buildInstallmentWhatsAppLines(price) {
    const lines = ['*Opções de parcelamento no cartão:*'];
    for (let months = 1; months <= 12; months++) {
        const quote = getInstallmentQuote(price, months);
        if (months === 1) {
            lines.push(`• À vista: ${formatCartMoney(quote.total)}`);
        } else {
            lines.push(`• ${months}x de ${formatCartMoney(quote.perMonth)} (total ${formatCartMoney(quote.total)})`);
        }
    }
    return lines;
}

function renderModalInstallmentTable(price) {
    const container = document.getElementById('modal-installment-table');
    if (container) {
        container.innerHTML = buildInstallmentTableHTML(price);
    }
}

// Hero Background Slideshow
let heroImages = [
    "produtos/Vestido Ayla/vestido_ayla (1).jpeg",
    "produtos/Conjunto Bella/cojunto_bella (1).jpeg",
    "produtos/Vestido Fluyte/vestido_fluyte.jpeg",
    "produtos/Body Tule/body_tule (1).jpeg",
    "produtos/T-shirt Premium/t-shirt_premium (1).jpeg"
];

let currentHeroImage = 0;
let heroInterval;

function initHeroSlideshow() {
    const heroBackground = document.getElementById('hero-background');
    const heroDots = document.getElementById('hero-dots');

    if (!heroBackground || !heroDots || !heroImages.length) return;

    heroBackground.innerHTML = '';
    heroDots.innerHTML = '';
    currentHeroImage = 0;
    if (heroInterval) clearInterval(heroInterval);
    
    // Create slide elements
    heroImages.forEach((imageUrl, index) => {
        const slide = document.createElement('div');
        slide.className = `hero-slide ${index === 0 ? 'active' : ''}`;
        slide.style.backgroundImage = `url('${imageUrl}')`;
        heroBackground.appendChild(slide);
    });
    
    // Create dots
    heroImages.forEach((_, index) => {
        const dot = document.createElement('div');
        dot.className = `hero-dot ${index === 0 ? 'active' : ''}`;
        dot.onclick = () => goToHeroSlide(index);
        heroDots.appendChild(dot);
    });
    
    // Start slideshow
    heroInterval = setInterval(nextHeroSlide, 5000);
}

function updateHeroSlides() {
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-dot');
    
    slides.forEach((slide, index) => {
        if (index === currentHeroImage) {
            slide.classList.add('active');
            slide.classList.remove('slide-out');
        } else if (index === (currentHeroImage - 1 + heroImages.length) % heroImages.length) {
            slide.classList.add('slide-out');
            slide.classList.remove('active');
        } else {
            slide.classList.remove('active', 'slide-out');
        }
    });
    
    // Update dots
    dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentHeroImage);
    });
}

function nextHeroSlide() {
    currentHeroImage = (currentHeroImage + 1) % heroImages.length;
    updateHeroSlides();
}

function goToHeroSlide(index) {
    currentHeroImage = index;
    updateHeroSlides();
    
    // Reset interval
    clearInterval(heroInterval);
    heroInterval = setInterval(nextHeroSlide, 5000);
}

// Shopping Cart
let cart = [];
let modalProduct = null;
let modalImageIndex = 0;

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

    bindProductsGridClick();
    initCheckoutFormHandlers();

    // Carregar produtos da API e inicializar vitrine
    loadCatalogData();

    // Load cart from localStorage
    loadCart();

    // Update footer year
    const footerYear = document.getElementById('footer-year');
    if (footerYear) {
        footerYear.textContent = new Date().getFullYear();
    }
});

async function loadCatalogData() {
    try {
        const response = await fetch('api/products.php');
        if (!response.ok) throw new Error('API indisponível');

        const data = await response.json();
        if (!data.success || !Array.isArray(data.products) || data.products.length === 0) {
            throw new Error('Catálogo vazio');
        }

        products = data.products.map(product => ({
            id: normalizeId(product.id),
            collectionId: product.collection_id ? normalizeId(product.collection_id) : null,
            name: product.name,
            description: product.description || '',
            price: Number(product.price),
            size: product.size || '',
            badge: product.badge || null,
            image: product.image,
            images: product.images && product.images.length ? product.images : [product.image]
        }));

        if (Array.isArray(data.collections)) {
            collections = data.collections.map(collection => ({
                id: normalizeId(collection.id),
                name: collection.name,
                slug: collection.slug,
                description: collection.description || '',
                coverImage: collection.cover_image || null
            }));
        }

        if (!collections.length && products.length) {
            collections = [{
                id: 'all',
                name: 'Catálogo',
                slug: 'catalogo',
                description: '',
                coverImage: null
            }];
        }

        if (Array.isArray(data.hero_images) && data.hero_images.length > 0) {
            heroImages = data.hero_images;
        }
    } catch (error) {
        products = [...fallbackProducts];
        collections = [{
            id: 'fallback',
            name: 'Destaques',
            slug: 'destaques',
            description: 'Peças exclusivas selecionadas para você.',
            coverImage: null
        }];
        heroImages = fallbackProducts
            .filter(product => product.image)
            .map(product => product.image);
    }

    initHeroSlideshow();
    renderCollectionsCatalog();
    openProductFromHash();
}

function bindProductsGridClick() {
    const catalog = document.getElementById('collections-catalog');
    if (!catalog || productsGridClickBound) return;

    productsGridClickBound = true;
    catalog.addEventListener('click', (event) => {
        const addBtn = event.target.closest('.add-to-cart');
        if (addBtn) {
            event.stopPropagation();
            const card = addBtn.closest('.product-card');
            if (card) addToCart(card.dataset.productId);
            return;
        }

        const favBtn = event.target.closest('.favorite-btn');
        if (favBtn) {
            event.stopPropagation();
            const card = favBtn.closest('.product-card');
            if (card) toggleFavorite(card.dataset.productId);
            return;
        }

        const card = event.target.closest('.product-card');
        if (card) openProductModal(card.dataset.productId);
    });
}

function createProductCard(product) {
    const secondaryImage = (product.images && product.images.length > 1) ? product.images[1] : product.image;
    const badgeHtml = product.badge ? `<span class="product-badge">${product.badge}</span>` : '';
    const installment = getProductInstallmentSummary(product.price);

    const productCard = document.createElement('div');
    productCard.className = 'product-card';
    productCard.dataset.productId = String(product.id);
    productCard.setAttribute('role', 'button');
    productCard.setAttribute('tabindex', '0');
    productCard.setAttribute('aria-label', `Ver detalhes de ${product.name}`);
    productCard.innerHTML = `
        <div class="product-image-container">
            ${badgeHtml}
            <img src="${product.image}" alt="${product.name}" class="product-image product-image-primary">
            <img src="${secondaryImage}" alt="${product.name}" class="product-image product-image-secondary">
            <span class="product-view-hint">Ver detalhes</span>
            <button type="button" class="favorite-btn" aria-label="Favoritar ${product.name}">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
            </button>
        </div>
        <div class="product-info">
            <h3 class="product-name">${product.name}</h3>
            <p class="product-description">${product.description}</p>
            <span class="product-size">${product.size}</span>
            <div class="product-price-section">
                <p class="product-price">R$ ${product.price.toFixed(2).replace('.', ',')}</p>
                    <p class="product-installment">${installment}</p>
            </div>
            <button type="button" class="add-to-cart">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
                Adicionar
            </button>
        </div>
    `;
    productCard.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            openProductModal(product.id);
        }
    });
    observer.observe(productCard);
    return productCard;
}

function renderCollectionsCatalog() {
    const catalog = document.getElementById('collections-catalog');
    if (!catalog) return;

    catalog.innerHTML = '';

    const sections = [];

    collections.forEach(collection => {
        const sectionProducts = products.filter(product => {
            if (collection.id === 'fallback' || collection.id === 'all') return true;
            return normalizeId(product.collectionId) === normalizeId(collection.id);
        });
        sections.push({ collection, products: sectionProducts });
    });

    const groupedIds = new Set(
        collections.filter(c => c.id !== 'fallback').map(c => normalizeId(c.id))
    );
    const uncategorized = products.filter(product => {
        return !product.collectionId || !groupedIds.has(normalizeId(product.collectionId));
    });

    if (uncategorized.length && collections.length && collections[0]?.id !== 'fallback') {
        sections.push({
            collection: {
                id: 'outros',
                name: 'Outros produtos',
                slug: 'outros',
                description: '',
                coverImage: null
            },
            products: uncategorized
        });
    }

    if (!sections.length && products.length) {
        sections.push({
            collection: { id: 'all', name: 'Catálogo', slug: 'catalogo', description: '', coverImage: null },
            products
        });
    }

    sections.forEach(({ collection, products: sectionProducts }) => {
        const block = document.createElement('article');
        block.className = 'collection-block';
        block.id = `colecao-${collection.slug}`;

        const coverHtml = collection.coverImage
            ? `<img src="${collection.coverImage}" alt="${collection.name}" class="collection-cover">`
            : '';

        const descriptionHtml = collection.description
            ? `<p class="collection-description">${collection.description}</p>`
            : '';

        block.innerHTML = `
            <div class="collection-block-header">
                <div class="collection-block-title-wrap">
                    ${coverHtml}
                    <div>
                        <h3 class="collection-title">${collection.name}</h3>
                        ${descriptionHtml}
                    </div>
                </div>
                <span class="collection-count">${sectionProducts.length} ${sectionProducts.length === 1 ? 'peça' : 'peças'}</span>
            </div>
        `;

        const grid = document.createElement('div');
        grid.className = 'products-grid';

        if (!sectionProducts.length) {
            grid.innerHTML = '<div class="collection-empty">Novidades em breve nesta coleção.</div>';
        } else {
            sectionProducts.forEach(product => grid.appendChild(createProductCard(product)));
        }

        block.appendChild(grid);
        catalog.appendChild(block);
    });

    if (!catalog.children.length) {
        catalog.innerHTML = '<div class="collection-empty">Nenhum produto disponível no momento.</div>';
    }
}

// Toggle Favorite
function toggleFavorite(productId) {
    const product = findProduct(productId);
    if (!product) return;
    showNotification(`${product.name} adicionado aos favoritos!`);
}

function openProductFromHash() {
    const match = window.location.hash.match(/^#produto-(\d+)$/i);
    if (match) {
        openProductModal(match[1]);
    }
}

// Product Modal
function openProductModal(productId) {
    const product = findProduct(productId);
    if (!product) return;

    modalProduct = product;
    modalImageIndex = 0;

    const modal = document.getElementById('product-modal');
    if (!modal) return;

    const badge = document.getElementById('modal-badge');
    const title = document.getElementById('modal-title');
    const description = document.getElementById('modal-description');
    const size = document.getElementById('modal-size');
    const price = document.getElementById('modal-price');
    const installment = document.getElementById('modal-installment');
    const addCartBtn = document.getElementById('modal-add-cart');

    if (!title || !description || !price) return;

    title.textContent = product.name;
    description.textContent = product.description || 'Sem descrição disponível.';
    if (size) size.textContent = product.size || '';
    price.textContent = formatCartMoney(product.price);
    if (installment) {
        installment.textContent = getProductInstallmentSummary(product.price);
    }
    renderModalInstallmentTable(product.price);

    if (product.badge && badge) {
        badge.textContent = product.badge;
        badge.classList.remove('hidden');
    } else if (badge) {
        badge.classList.add('hidden');
    }

    if (addCartBtn) {
        addCartBtn.onclick = () => {
            addToCart(product.id);
        };
    }

    renderModalGallery();
    updateModalImage();

    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    history.replaceState(null, '', `#produto-${product.id}`);
}

function closeProductModal() {
    const modal = document.getElementById('product-modal');
    if (!modal) return;

    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    closeGalleryLightbox();
    modalProduct = null;

    if (window.location.hash.match(/^#produto-\d+$/i)) {
        history.replaceState(null, '', window.location.pathname + window.location.search);
    }
}

function renderModalGallery() {
    const thumbnails = document.getElementById('modal-thumbnails');
    if (!modalProduct || !thumbnails) return;

    const images = modalProduct.images && modalProduct.images.length ? modalProduct.images : [modalProduct.image];
    thumbnails.innerHTML = images.map((src, index) => `
        <img src="${src}" alt="Foto ${index + 1}" class="modal-thumb ${index === modalImageIndex ? 'active' : ''}" onclick="setModalImage(${index})">
    `).join('');

    const prevBtn = document.getElementById('modal-prev');
    const nextBtn = document.getElementById('modal-next');
    if (prevBtn) prevBtn.style.display = images.length > 1 ? 'flex' : 'none';
    if (nextBtn) nextBtn.style.display = images.length > 1 ? 'flex' : 'none';
}

function updateModalImage() {
    if (!modalProduct) return;

    const images = modalProduct.images && modalProduct.images.length ? modalProduct.images : [modalProduct.image];
    const mainImage = document.getElementById('modal-main-image');
    if (mainImage) {
        mainImage.src = images[modalImageIndex];
        mainImage.alt = modalProduct.name;
    }

    document.querySelectorAll('.modal-thumb').forEach((thumb, index) => {
        thumb.classList.toggle('active', index === modalImageIndex);
    });
}

function setModalImage(index) {
    if (!modalProduct) return;
    const images = modalProduct.images && modalProduct.images.length ? modalProduct.images : [modalProduct.image];
    modalImageIndex = index;
    if (modalImageIndex < 0) modalImageIndex = images.length - 1;
    if (modalImageIndex >= images.length) modalImageIndex = 0;
    updateModalImage();
}

function changeModalImage(direction) {
    if (!modalProduct) return;
    setModalImage(modalImageIndex + direction);
}

function openGalleryLightbox() {
    if (!modalProduct) return;

    const lightbox = document.getElementById('gallery-lightbox');
    const lightboxImage = document.getElementById('lightbox-image');
    const images = modalProduct.images && modalProduct.images.length ? modalProduct.images : [modalProduct.image];

    lightboxImage.src = images[modalImageIndex];
    lightboxImage.alt = modalProduct.name;
    updateLightboxCounter();

    lightbox.classList.add('open');
    lightbox.setAttribute('aria-hidden', 'false');
}

function closeGalleryLightbox() {
    const lightbox = document.getElementById('gallery-lightbox');
    if (!lightbox) return;
    lightbox.classList.remove('open');
    lightbox.setAttribute('aria-hidden', 'true');
}

function changeLightboxImage(direction) {
    if (!modalProduct) return;

    const images = modalProduct.images && modalProduct.images.length ? modalProduct.images : [modalProduct.image];
    modalImageIndex += direction;
    if (modalImageIndex < 0) modalImageIndex = images.length - 1;
    if (modalImageIndex >= images.length) modalImageIndex = 0;

    const lightboxImage = document.getElementById('lightbox-image');
    lightboxImage.src = images[modalImageIndex];
    updateModalImage();
    updateLightboxCounter();
}

function updateLightboxCounter() {
    if (!modalProduct) return;
    const images = modalProduct.images && modalProduct.images.length ? modalProduct.images : [modalProduct.image];
    const counter = document.getElementById('lightbox-counter');
    if (counter) counter.textContent = `${modalImageIndex + 1} / ${images.length}`;
}

function goToHomePage() {
    closeProductModal();
    window.scrollTo({ top: 0, behavior: 'smooth' });
    history.replaceState(null, '', window.location.pathname + window.location.search);
}

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        const lightbox = document.getElementById('gallery-lightbox');
        if (lightbox && lightbox.classList.contains('open')) {
            closeGalleryLightbox();
            return;
        }
        const modal = document.getElementById('product-modal');
        if (modal && modal.classList.contains('open')) {
            closeProductModal();
        }
    }
    if (modalProduct) {
        const modal = document.getElementById('product-modal');
        const lightbox = document.getElementById('gallery-lightbox');
        if (lightbox && lightbox.classList.contains('open')) {
            if (event.key === 'ArrowLeft') changeLightboxImage(-1);
            if (event.key === 'ArrowRight') changeLightboxImage(1);
        } else if (modal && modal.classList.contains('open')) {
            if (event.key === 'ArrowLeft') changeModalImage(-1);
            if (event.key === 'ArrowRight') changeModalImage(1);
        }
    }
});

// Toggle Mobile Menu
function toggleMenu() {
    const navLinks = document.querySelector('.nav-links');
    navLinks.classList.toggle('mobile-active');
}

// Add to Cart
function addToCart(productId) {
    const product = findProduct(productId);
    if (!product) return;

    const id = normalizeId(product.id);
    const existingItem = cart.find(item => normalizeId(item.id) === id);

    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({
            id,
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
function formatCartMoney(value) {
    return `R$ ${Number(value).toFixed(2).replace('.', ',')}`;
}

function buildWhatsAppOrderMessage(cartItems, customer = {}) {
    const total = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const totalItems = cartItems.reduce((sum, item) => sum + item.quantity, 0);
    const isPix = (customer.payment || '').toLowerCase().includes('pix');
    const pixTotal = isPix ? total * 0.9 : total;
    const date = new Date().toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    const lines = [
        '*PEDIDO — LaMel Modas*',
        '',
        'Olá! Gostaria de finalizar meu pedido:',
        '',
        `*Resumo:* ${totalItems} ${totalItems === 1 ? 'item' : 'itens'}`,
        `*Data:* ${date}`,
        '',
        '*ITENS DO PEDIDO*',
        '──────────────────────────────'
    ];

    cartItems.forEach((item, index) => {
        const subtotal = item.price * item.quantity;
        lines.push(
            '',
            `*${index + 1}. ${item.name}*`,
            `   Quantidade: ${item.quantity}`,
            `   Valor unitário: ${formatCartMoney(item.price)}`,
            `   Subtotal: *${formatCartMoney(subtotal)}*`
        );
    });

    lines.push(
        '',
        '──────────────────────────────',
        `*TOTAL: ${formatCartMoney(total)}*`
    );

    if (isPix) {
        lines.push(`*TOTAL COM PIX (10% OFF): ${formatCartMoney(pixTotal)}*`);
    } else if ((customer.payment || '').toLowerCase().includes('cartão') || (customer.payment || '').toLowerCase().includes('cartao')) {
        const months = Number(customer.installments) || 12;
        const quote = getInstallmentQuote(total, months);
        lines.push('');
        lines.push('*PARCELAMENTO ESCOLHIDO NO CARTÃO:*');
        if (months === 1) {
            lines.push(`• À vista: ${formatCartMoney(quote.total)}`);
        } else {
            lines.push(`• ${months}x de ${formatCartMoney(quote.perMonth)}`);
            lines.push(`• Total parcelado: ${formatCartMoney(quote.total)}`);
        }
    }

    lines.push(
        '',
        '*DADOS PARA ENTREGA*',
        `Nome: ${customer.name || '-'}`,
        `Telefone: ${customer.phone || '-'}`,
        `Endereço: ${customer.address || '-'}`,
        `CEP: ${customer.cep || '-'}`,
        `Cidade: ${customer.city || '-'}`,
        '',
        '*PAGAMENTO*',
        customer.payment || '-',
        '',
        '*OBSERVAÇÕES*',
        customer.notes || 'Nenhuma',
        '',
        'Aguardo confirmação. Obrigada! ✨'
    );

    return lines.join('\n');
}

function getCartTotal() {
    return cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
}

function isCardPayment(paymentValue) {
    const value = (paymentValue || '').toLowerCase();
    return value.includes('cartão') || value.includes('cartao');
}

function renderCheckoutInstallmentSelect(total, selectedMonths) {
    const select = document.getElementById('checkout-installments');
    const preview = document.getElementById('checkout-installment-preview');
    if (!select) return;

    select.innerHTML = '<option value="">Selecione as parcelas</option>';

    for (let months = 1; months <= 12; months++) {
        const quote = getInstallmentQuote(total, months);
        const option = document.createElement('option');
        option.value = String(months);
        option.textContent = months === 1
            ? `À vista — ${formatCartMoney(quote.total)}`
            : `${months}x de ${formatCartMoney(quote.perMonth)} (total ${formatCartMoney(quote.total)})`;
        select.appendChild(option);
    }

    if (selectedMonths) {
        select.value = String(selectedMonths);
    }

    updateCheckoutInstallmentPreview(total, select.value);
}

function updateCheckoutInstallmentPreview(total, months) {
    const preview = document.getElementById('checkout-installment-preview');
    if (!preview || !months) {
        if (preview) preview.textContent = '';
        return;
    }

    const quote = getInstallmentQuote(total, Number(months));
    preview.textContent = months === '1'
        ? `Pagamento à vista: ${formatCartMoney(quote.total)}`
        : `${months}x de ${formatCartMoney(quote.perMonth)} — total ${formatCartMoney(quote.total)}`;
}

function toggleCheckoutInstallmentsField(form) {
    const field = document.getElementById('checkout-installments-field');
    const select = document.getElementById('checkout-installments');
    const paymentInput = form.querySelector('input[name="payment"]:checked');
    if (!field || !select) return;

    const show = paymentInput && isCardPayment(paymentInput.value);
    field.classList.toggle('hidden', !show);
    select.required = show;

    if (!show) {
        select.value = '';
        updateCheckoutInstallmentPreview(getCartTotal(), '');
    }
}

function initCheckoutFormHandlers() {
    const form = document.getElementById('checkout-form');
    if (!form || form.dataset.handlersBound === 'true') return;

    form.dataset.handlersBound = 'true';

    form.querySelectorAll('input[name="payment"]').forEach((input) => {
        input.addEventListener('change', () => {
            const total = getCartTotal();
            toggleCheckoutInstallmentsField(form);
            if (isCardPayment(input.value)) {
                renderCheckoutInstallmentSelect(total, form.installments.value || 12);
            }
        });
    });

    const select = document.getElementById('checkout-installments');
    if (select) {
        select.addEventListener('change', () => {
            updateCheckoutInstallmentPreview(getCartTotal(), select.value);
        });
    }
}

function loadCheckoutInfo() {
    try {
        return JSON.parse(localStorage.getItem('lamel-checkout-info') || '{}');
    } catch (error) {
        return {};
    }
}

function saveCheckoutInfo(data) {
    localStorage.setItem('lamel-checkout-info', JSON.stringify({
        name: data.name,
        phone: data.phone,
        address: data.address,
        cep: data.cep,
        city: data.city,
        payment: data.payment,
        installments: data.installments || null
    }));
}

function updateCheckoutSummary() {
    const summary = document.getElementById('checkout-order-summary');
    if (!summary || !cart.length) return;

    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const itemsCount = cart.reduce((sum, item) => sum + item.quantity, 0);

    summary.innerHTML = `
        <div class="checkout-summary-row">
            <span>${itemsCount} ${itemsCount === 1 ? 'item' : 'itens'} no carrinho</span>
            <strong>${formatCartMoney(total)}</strong>
        </div>
    `;
}

function openCheckoutModal() {
    if (cart.length === 0) {
        showNotification('Seu carrinho está vazio!');
        return;
    }

    const modal = document.getElementById('checkout-modal');
    const form = document.getElementById('checkout-form');
    if (!modal || !form) return;

    const saved = loadCheckoutInfo();
    form.name.value = saved.name || '';
    form.phone.value = saved.phone || '';
    form.address.value = saved.address || '';
    form.cep.value = saved.cep || '';
    form.city.value = saved.city || '';
    form.notes.value = '';

    if (saved.payment) {
        const paymentInput = form.querySelector(`input[name="payment"][value="${saved.payment}"]`);
        if (paymentInput) paymentInput.checked = true;
    } else {
        form.querySelectorAll('input[name="payment"]').forEach(input => { input.checked = false; });
    }

    initCheckoutFormHandlers();
    toggleCheckoutInstallmentsField(form);

    const total = getCartTotal();
    if (isCardPayment(saved.payment || form.querySelector('input[name="payment"]:checked')?.value)) {
        renderCheckoutInstallmentSelect(total, saved.installments || 12);
    } else {
        renderCheckoutInstallmentSelect(total, '');
    }

    updateCheckoutSummary();
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';

    const cartSidebar = document.getElementById('cart-sidebar');
    const cartOverlay = document.getElementById('cart-overlay');
    if (cartSidebar) cartSidebar.classList.remove('open');
    if (cartOverlay) cartOverlay.classList.remove('open');
}

function closeCheckoutModal() {
    const modal = document.getElementById('checkout-modal');
    if (!modal) return;

    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

function submitCheckoutWhatsApp(event) {
    event.preventDefault();

    if (cart.length === 0) {
        showNotification('Seu carrinho está vazio!');
        return;
    }

    const form = event.target;
    const paymentInput = form.querySelector('input[name="payment"]:checked');
    if (!paymentInput) {
        showNotification('Selecione a forma de pagamento.');
        return;
    }

    const customer = {
        name: form.name.value.trim(),
        phone: form.phone.value.trim(),
        address: form.address.value.trim(),
        cep: form.cep.value.trim(),
        city: form.city.value.trim(),
        payment: paymentInput.value,
        installments: form.installments?.value || '',
        notes: form.notes.value.trim()
    };

    if (!customer.name || !customer.phone || !customer.address || !customer.cep || !customer.city) {
        showNotification('Preencha todos os campos obrigatórios.');
        return;
    }

    if (isCardPayment(customer.payment) && !customer.installments) {
        showNotification('Selecione a quantidade de parcelas no cartão.');
        return;
    }

    saveCheckoutInfo(customer);

    const phoneNumber = '5531999582428';
    const message = buildWhatsAppOrderMessage(cart, customer);
    const whatsappURL = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;

    window.open(whatsappURL, '_blank');

    cart = [];
    saveCart();
    updateCartUI();
    closeCheckoutModal();

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
    const animatedElements = document.querySelectorAll('.contact-card, .section-title');
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

window.addEventListener('hashchange', openProductFromHash);

Object.assign(window, {
    openProductModal,
    closeProductModal,
    changeModalImage,
    setModalImage,
    openGalleryLightbox,
    closeGalleryLightbox,
    changeLightboxImage,
    goToHomePage,
    toggleMenu,
    toggleCart,
    addToCart,
    toggleFavorite,
    openCheckoutModal,
    closeCheckoutModal,
    submitCheckoutWhatsApp
});
