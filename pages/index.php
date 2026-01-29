<?php
$pageTitle = 'Home';
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/classes.php';

$conn = getConnection();
$product = new Product($conn);

// Get featured products
$featured = $product->getProducts(1, 8, []);
$trending = $product->getProducts(1, 8, ['sort' => 'rating']);
?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container-fluid px-4">
            <!-- Hero Banner -->
            <section class="hero-banner">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="hero-content">
                            <h1>Welcome to <?php echo SITE_NAME; ?></h1>
                            <p>Discover amazing products at unbeatable prices. Shop now and save big!</p>
                            <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-shopping-bag me-2"></i>Shop Now
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 text-center d-none d-lg-block">
                        <i class="fas fa-boxes" style="font-size: 10rem; opacity: 0.1;"></i>
                    </div>
                </div>
            </section>

            <!-- Featured Categories -->
            <section class="categories-section my-5">
                <h2 class="section-title">Featured Categories</h2>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="category-card bg-white rounded-3 p-4 text-center cursor-pointer hover-shadow">
                            <i class="fas fa-laptop" style="font-size: 3rem; color: var(--primary-color);"></i>
                            <h4 class="mt-3">Electronics</h4>
                            <p class="text-muted">Shop latest gadgets</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="category-card bg-white rounded-3 p-4 text-center cursor-pointer hover-shadow">
                            <i class="fas fa-tshirt" style="font-size: 3rem; color: var(--primary-color);"></i>
                            <h4 class="mt-3">Fashion</h4>
                            <p class="text-muted">Trendy clothing & accessories</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="category-card bg-white rounded-3 p-4 text-center cursor-pointer hover-shadow">
                            <i class="fas fa-home" style="font-size: 3rem; color: var(--primary-color);"></i>
                            <h4 class="mt-3">Home</h4>
                            <p class="text-muted">Everything for your home</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Featured Products -->
            <section class="products-section">
                <h2 class="section-title">Featured Products</h2>
                <div class="row g-4 product-grid">
                    <?php if (!empty($featured)): ?>
                        <?php foreach ($featured as $prod): ?>
                            <div class="col-md-6 col-lg-3">
                                <div class="product-card fade-in">
                                    <div class="product-image position-relative">
                                        <img src="<?php echo htmlspecialchars(getProductImage($prod['id'], $conn)); ?>" 
                                             alt="<?php echo htmlspecialchars($prod['name']); ?>"
                                             class="w-100 h-100">
                                        <?php if ($prod['discount_percentage'] > 0): ?>
                                            <div class="product-badge">
                                                <?php echo intval($prod['discount_percentage']); ?>% OFF
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-info">
                                        <h3 class="product-name"><?php echo htmlspecialchars($prod['name']); ?></h3>
                                        <div class="product-rating">
                                            <i class="fas fa-star"></i>
                                            <span><?php echo number_format($prod['rating'], 1); ?></span>
                                            <small class="text-muted">(<?php echo $prod['rating_count']; ?>)</small>
                                        </div>
                                        <div class="product-price">
                                            <span class="price-current">
                                                <?php echo formatCurrency(getProductPrice($prod)); ?>
                                            </span>
                                            <?php if ($prod['discount_percentage'] > 0): ?>
                                                <span class="price-original">
                                                    <?php echo formatCurrency($prod['price']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-actions">
                                            <button class="btn-cart" onclick="addToCart(<?php echo $prod['id']; ?>)">
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                            <button class="btn-wishlist" onclick="addToWishlist(<?php echo $prod['id']; ?>)" 
                                                    data-product="<?php echo $prod['id']; ?>">
                                                <i class="fas fa-heart"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <p class="text-muted">No products available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Best Selling Products -->
            <section class="products-section">
                <h2 class="section-title">Best Selling Products</h2>
                <div class="row g-4 product-grid">
                    <?php if (!empty($trending)): ?>
                        <?php foreach ($trending as $prod): ?>
                            <div class="col-md-6 col-lg-3">
                                <div class="product-card fade-in">
                                    <div class="product-image position-relative">
                                        <img src="<?php echo htmlspecialchars(getProductImage($prod['id'], $conn)); ?>" 
                                             alt="<?php echo htmlspecialchars($prod['name']); ?>"
                                             class="w-100 h-100">
                                        <?php if ($prod['discount_percentage'] > 0): ?>
                                            <div class="product-badge">
                                                <?php echo intval($prod['discount_percentage']); ?>% OFF
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-info">
                                        <h3 class="product-name"><?php echo htmlspecialchars($prod['name']); ?></h3>
                                        <div class="product-rating">
                                            <i class="fas fa-star"></i>
                                            <span><?php echo number_format($prod['rating'], 1); ?></span>
                                            <small class="text-muted">(<?php echo $prod['rating_count']; ?>)</small>
                                        </div>
                                        <div class="product-price">
                                            <span class="price-current">
                                                <?php echo formatCurrency(getProductPrice($prod)); ?>
                                            </span>
                                            <?php if ($prod['discount_percentage'] > 0): ?>
                                                <span class="price-original">
                                                    <?php echo formatCurrency($prod['price']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-actions">
                                            <button class="btn-cart" onclick="addToCart(<?php echo $prod['id']; ?>)">
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                            <button class="btn-wishlist" onclick="addToWishlist(<?php echo $prod['id']; ?>)" 
                                                    data-product="<?php echo $prod['id']; ?>">
                                                <i class="fas fa-heart"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Special Offers Banner -->
            <section class="offers-section my-5">
                <div class="alert alert-warning d-flex align-items-center gap-3" role="alert">
                    <i class="fas fa-tag fa-2x flex-shrink-0"></i>
                    <div>
                        <h4 class="mb-0">Special Offer!</h4>
                        <p class="mb-0">Get up to 50% off on selected items. Use coupon code: <strong>SAVE50</strong></p>
                    </div>
                </div>
            </section>
        </div>
    </main>

<?php
$conn->close();
require_once dirname(__DIR__) . '/includes/footer.php';
?>
