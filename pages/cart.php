<?php
$pageTitle = 'Cart';
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/config/constants.php';

$conn = getConnection();
$cartItems = [];
$total = 0;
$subtotal = 0;
$shippingCost = 50;

if (isLoggedIn()) {
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("
        SELECT c.id, c.product_id, c.quantity, c.selected_size, c.selected_color,
               p.name, p.price, p.discount_percentage
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $cartItems = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($cartItems as $item) {
        $price = getProductPrice($item);
        $itemTotal = $price * $item['quantity'];
        $subtotal += $itemTotal;
    }
}

$total = $subtotal + $shippingCost;

if ($subtotal >= 500) {
    $shippingCost = 0;
    $total = $subtotal;
}
?>

    <main class="main-content">
        <div class="container-fluid px-4">
            <h1 class="my-4">Shopping Cart</h1>

            <?php if (empty($cartItems)): ?>
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <h3>Your cart is empty</h3>
                            <p class="empty-state-text">Add products to your cart and come back here.</p>
                            <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-primary">
                                Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="cart-container">
                    <div class="cart-items">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item">
                                <div class="cart-item-image">
                                    <img src="<?php echo htmlspecialchars(getProductImage($item['product_id'], $conn)); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="cart-item-details">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <?php if (!empty($item['selected_size'])): ?>
                                        <p class="cart-item-meta">Size: <?php echo htmlspecialchars($item['selected_size']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($item['selected_color'])): ?>
                                        <p class="cart-item-meta">Color: <?php echo htmlspecialchars($item['selected_color']); ?></p>
                                    <?php endif; ?>
                                    <p class="cart-item-price">
                                        <?php echo formatCurrency(getProductPrice($item)); ?>
                                    </p>
                                </div>
                                <div class="cart-item-actions" style="flex-direction: column; align-items: flex-end; gap: 0.5rem;">
                                    <div class="d-flex align-items-center gap-2">
                                        <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(<?php echo $item['id']; ?>, parseInt(document.getElementById('qty-<?php echo $item['id']; ?>').value) - 1)">-</button>
                                        <input type="number" id="qty-<?php echo $item['id']; ?>" 
                                               class="qty-input" value="<?php echo $item['quantity']; ?>" min="1" max="99">
                                        <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(<?php echo $item['id']; ?>, parseInt(document.getElementById('qty-<?php echo $item['id']; ?>').value) + 1)">+</button>
                                    </div>
                                    <p class="mb-0 fw-bold">
                                        <?php echo formatCurrency(getProductPrice($item) * $item['quantity']); ?>
                                    </p>
                                    <button class="remove-btn" onclick="removeFromCart(<?php echo $item['id']; ?>)">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="cart-summary">
                        <h4 class="mb-3">Order Summary</h4>
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span><?php echo formatCurrency($subtotal); ?></span>
                        </div>
                        <?php if ($shippingCost > 0): ?>
                            <div class="summary-row">
                                <span>Shipping</span>
                                <span><?php echo formatCurrency($shippingCost); ?></span>
                            </div>
                        <?php else: ?>
                            <div class="summary-row">
                                <span>Shipping</span>
                                <span class="text-success fw-bold">FREE</span>
                            </div>
                        <?php endif; ?>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span><?php echo formatCurrency($total); ?></span>
                        </div>

                        <?php if ($subtotal < 500): ?>
                            <p class="small text-muted mb-3">
                                <i class="fas fa-info-circle"></i> Free shipping on orders above ₹500
                            </p>
                        <?php endif; ?>

                        <?php if (isLoggedIn()): ?>
                            <a href="<?php echo SITE_URL; ?>/pages/checkout.php" class="checkout-btn">
                                <i class="fas fa-lock me-1"></i> Proceed to Checkout
                            </a>
                        <?php else: ?>
                            <a href="<?php echo SITE_URL; ?>/pages/login.php" class="checkout-btn">
                                <i class="fas fa-sign-in-alt me-1"></i> Login to Checkout
                            </a>
                        <?php endif; ?>

                        <a href="<?php echo SITE_URL; ?>/pages/products.php" class="btn btn-outline-secondary w-100 mt-2">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

<?php
$conn->close();
require_once dirname(__DIR__) . '/includes/footer.php';
?>
