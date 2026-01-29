// Main JavaScript - ShopHub E-Commerce Platform

// CSRF Token Helper
function getCSRFToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

// Format currency
function formatCurrency(amount) {
    return '₹' + parseFloat(amount).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Add to cart
function addToCart(productId, quantity = 1, size = '', color = '') {
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    if (size) formData.append('size', size);
    if (color) formData.append('color', color);

    fetch('/api/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Product added to cart!', 'success');
            updateCartBadge();
        } else {
            showNotification(data.message || 'Failed to add product', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

// Remove from cart
function removeFromCart(itemId) {
    if (!confirm('Remove this item from cart?')) return;

    const formData = new FormData();
    formData.append('action', 'remove');
    formData.append('item_id', itemId);

    fetch('/api/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showNotification('Failed to remove item', 'error');
        }
    });
}

// Update cart quantity
function updateQuantity(itemId, quantity) {
    if (quantity < 1) {
        removeFromCart(itemId);
        return;
    }

    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('item_id', itemId);
    formData.append('quantity', quantity);

    fetch('/api/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showNotification('Failed to update quantity', 'error');
        }
    });
}

// Add to wishlist
function addToWishlist(productId) {
    const formData = new FormData();
    formData.append('product_id', productId);

    fetch('/api/wishlist.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const btn = document.querySelector(`[data-product="${productId}"].btn-wishlist`);
            if (btn) {
                btn.classList.add('active');
                showNotification('Added to wishlist!', 'success');
            }
        } else {
            showNotification(data.message || 'Failed to add to wishlist', 'error');
        }
    });
}

// Remove from wishlist
function removeFromWishlist(productId) {
    const formData = new FormData();
    formData.append('action', 'remove');
    formData.append('product_id', productId);

    fetch('/api/wishlist.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showNotification('Failed to remove from wishlist', 'error');
        }
    });
}

// Update cart badge
function updateCartBadge() {
    fetch('/api/get-cart-count.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.cart-badge');
            if (badge) {
                badge.textContent = data.count;
                if (data.count > 0) {
                    badge.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                }
            }
        });
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show`;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    const container = document.querySelector('.notification-container') || document.body;
    container.insertBefore(notification, container.firstChild);

    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Validate email
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Validate phone
function validatePhone(phone) {
    const re = /^[6-9]\d{9}$/;
    const digits = phone.replace(/\D/g, '');
    return re.test(digits);
}

// Validate password
function validatePassword(password) {
    return password.length >= 6;
}

// Sanitize input
function sanitizeInput(input) {
    const div = document.createElement('div');
    div.textContent = input;
    return div.innerHTML;
}

// Gallery zoom
function setupGalleryZoom() {
    const galleryThumbnails = document.querySelectorAll('.gallery-thumbnail');
    const galleryMain = document.querySelector('.gallery-main img');

    galleryThumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            if (galleryMain) {
                galleryMain.src = this.querySelector('img').src;
                galleryThumbnails.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });
}

// Quantity selector
function setupQuantitySelector() {
    const minusBtn = document.querySelector('.qty-minus');
    const plusBtn = document.querySelector('.qty-plus');
    const qtyInput = document.querySelector('.qty-input');

    if (minusBtn && qtyInput) {
        minusBtn.addEventListener('click', () => {
            let qty = parseInt(qtyInput.value) || 1;
            if (qty > 1) {
                qtyInput.value = qty - 1;
            }
        });
    }

    if (plusBtn && qtyInput) {
        plusBtn.addEventListener('click', () => {
            let qty = parseInt(qtyInput.value) || 1;
            qtyInput.value = qty + 1;
        });
    }
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    let isValid = true;
    const inputs = form.querySelectorAll('[required]');

    inputs.forEach(input => {
        let error = false;

        if (!input.value.trim()) {
            error = true;
        } else if (input.type === 'email' && !validateEmail(input.value)) {
            error = true;
        } else if (input.name === 'phone' && !validatePhone(input.value)) {
            error = true;
        } else if (input.name === 'password' && !validatePassword(input.value)) {
            error = true;
        }

        if (error) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });

    return isValid;
}

// Initialize tooltips and popovers
function initializeBootstrapComponents() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
}

// Smooth scroll
function smoothScroll(target) {
    const element = document.querySelector(target);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
}

// Lazy load images
function setupLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        observer.unobserve(img);
                    }
                }
            });
        });

        document.querySelectorAll('img[data-src]').forEach(img => imageObserver.observe(img));
    }
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Search with debounce
const debouncedSearch = debounce(function(query) {
    if (query.length < 2) return;

    fetch(`/api/search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            const resultsContainer = document.getElementById('searchResults');
            if (resultsContainer && data.results) {
                resultsContainer.innerHTML = '';
                data.results.forEach(product => {
                    const item = document.createElement('a');
                    item.href = `/pages/product.php?id=${product.id}`;
                    item.className = 'list-group-item list-group-item-action';
                    item.textContent = product.name;
                    resultsContainer.appendChild(item);
                });
            }
        });
}, 300);

// Setup search autocomplete
function setupSearchAutocomplete() {
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            debouncedSearch(this.value);
        });
    }
}

// Document ready
document.addEventListener('DOMContentLoaded', function() {
    setupGalleryZoom();
    setupQuantitySelector();
    initializeBootstrapComponents();
    setupLazyLoading();
    setupSearchAutocomplete();

    // Add form validation listeners
    const forms = document.querySelectorAll('form[data-validate="true"]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this.id)) {
                e.preventDefault();
                showNotification('Please fill all required fields correctly', 'error');
            }
        });
    });
});

// Handle visibility change for performance
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        // Pause animations and background tasks
    } else {
        // Resume
    }
});
