<?php
/**
 * Products Page with Filters
 */
$pageTitle = 'Products';
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/classes.php';

$conn = getConnection();
$product = new Product($conn);

// Get filters
$page = intval($_GET['page'] ?? 1);
$search = sanitize($_GET['search'] ?? '');
$category = intval($_GET['category'] ?? 0);
$sortBy = sanitize($_GET['sort'] ?? 'newest');
$minPrice = floatval($_GET['min_price'] ?? 0);
$maxPrice = floatval($_GET['max_price'] ?? 100000);

$filters = [
    'search' => $search,
    'category_id' => $category,
    'sort' => $sortBy,
    'min_price' => $minPrice,
    'max_price' => $maxPrice
];

// Get products
$products = $product->getProducts($page, PRODUCTS_PER_PAGE, $filters);
$totalProducts = $product->getTotalCount($filters);
$totalPages = ceil($totalProducts / PRODUCTS_PER_PAGE);

// Get categories
$categories = $conn->query("SELECT * FROM categories WHERE status = 'active'")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

    <main class="main-content">
        <div class="container-fluid px-4">
            <h1 class="my-4">
                <i class="fas fa-cubes me-2" style="color: var(--primary-color);"></i>
                Products
            </h1>

            <div class="row g-4">
                <!-- Filters Sidebar -->
                <div class="col-lg-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Filters</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET">
                                <!-- Search -->
                                <div class="mb-3">
                                    <label class="form-label">Search</label>
                                    <input type="text" class="form-control" name="search" 
                                           value="<?php echo htmlspecialchars($search); ?>"
                                           placeholder="Search products...">
                                </div>

                                <!-- Category -->
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select class="form-select" name="category">
                                        <option value="0">All Categories</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>" 
                                                    <?php echo $category === $cat['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Price Range -->
                                <div class="mb-3">
                                    <label class="form-label">Price Range</label>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <input type="number" class="form-control" name="min_price" 
                                                   value="<?php echo $minPrice; ?>" placeholder="Min">
                                        </div>
                                        <div class="col-6">
                                            <input type="number" class="form-control" name="max_price" 
                                                   value="<?php echo $maxPrice; ?>" placeholder="Max">
                                        </div>
                                    </div>
                                </div>

                                <!-- Sort -->
                                <div class="mb-3">
                                    <label class="form-label">Sort By</label>
                                    <select class="form-select" name="sort">
                                        <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest</option>
                                        <option value="price_asc" <?php echo $sortBy === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                                        <option value="price_desc" <?php echo $sortBy === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                                        <option value="rating" <?php echo $sortBy === 'rating' ? 'selected' : ''; ?>>Rating</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                                <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-outline-secondary w-100 mt-2">Clear</a>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="col-lg-9">
                    <?php if (empty($products)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-cube"></i>
                            </div>
                            <h3>No Products Found</h3>
                            <p class="empty-state-text">Try adjusting your filters or search terms</p>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-4">
                            Showing <?php echo ($page - 1) * PRODUCTS_PER_PAGE + 1; ?> to 
                            <?php echo min($page * PRODUCTS_PER_PAGE, $totalProducts); ?> of 
                            <?php echo $totalProducts; ?> products
                        </p>

                        <div class="row g-4 product-grid">
                            <?php foreach ($products as $prod): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="product-card fade-in">
                                        <div class="product-image position-relative">
                                            <img src="<?php echo htmlspecialchars(getProductImage($prod['id'], $conn)); ?>" 
                                                 alt="<?php echo htmlspecialchars($prod['name']); ?>">
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
                                                <button class="btn-wishlist" onclick="addToWishlist(<?php echo $prod['id']; ?>)">
                                                    <i class="fas fa-heart"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-5">
                                <ul class="pagination">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query(array_filter($filters)); ?>">
                                                <i class="fas fa-chevron-left"></i> Previous
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter($filters)); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query(array_filter($filters)); ?>">
                                                Next <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
