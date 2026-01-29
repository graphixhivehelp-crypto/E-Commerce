<?php
/**
 * Checkout Page
 */
$pageTitle = 'Checkout';
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/classes.php';

requireLogin();

$conn = getConnection();
$userId = $_SESSION['user_id'];
$user = getCurrentUser();

// Get cart items
$stmt = $conn->prepare("
    SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.discount_percentage
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($cartItems)) {
    header("Location: " . SITE_URL . "/pages/cart.php");
    exit();
}

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $price = getProductPrice($item);
    $subtotal += $price * $item['quantity'];
}

$shippingCost = $subtotal >= 500 ? 0 : 50;
$total = $subtotal + $shippingCost;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = sanitize($_POST['payment_method'] ?? 'cod');
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $state = sanitize($_POST['state'] ?? '');
    $postal = sanitize($_POST['postal_code'] ?? '');

    if (empty($firstName) || empty($email) || empty($phone) || empty($address) || empty($city) || empty($state) || empty($postal)) {
        $error = 'Please fill all required fields';
    } else {
        // Create order
        $orderNumber = generateOrderNumber();
        $paymentStatus = $paymentMethod === 'cod' ? 'pending' : 'pending';
        $orderStatus = 'placed';

        $shippingData = json_encode([
            'name' => $firstName . ' ' . $lastName,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'postal_code' => $postal
        ]);

        $stmt = $conn->prepare("
            INSERT INTO orders (user_id, order_number, subtotal, shipping_cost, total_amount, 
                               payment_method, payment_status, order_status, shipping_address)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issddssss", $userId, $orderNumber, $subtotal, $shippingCost, 
                         $total, $paymentMethod, $paymentStatus, $orderStatus, $shippingData);

        if ($stmt->execute()) {
            $orderId = $stmt->insert_id;
            $stmt->close();

            // Add order items
            $stmt = $conn->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            foreach ($cartItems as $item) {
                $itemPrice = getProductPrice($item);
                $itemSubtotal = $itemPrice * $item['quantity'];
                $stmt->bind_param("iissid", $orderId, $item['product_id'], $item['name'], 
                                 $itemPrice, $item['quantity'], $itemSubtotal);
                $stmt->execute();
            }
            $stmt->close();

            // Clear cart
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();

            // Send order confirmation email
            $emailSubject = "Order Confirmation - " . $orderNumber;
            $emailBody = "<h2>Order Confirmed!</h2>";
            $emailBody .= "<p>Thank you for your order. Your order number is: <strong>" . $orderNumber . "</strong></p>";
            $emailBody .= "<p>Total Amount: <strong>" . formatCurrency($total) . "</strong></p>";
            $emailBody .= "<p>Payment Method: <strong>" . ucfirst(str_replace('_', ' ', $paymentMethod)) . "</strong></p>";
            
            sendEmail($email, $emailSubject, $emailBody);

            // Redirect to order confirmation
            $_SESSION['order_id'] = $orderId;
            header("Location: " . SITE_URL . "/pages/order-confirmation.php?order=" . $orderNumber);
            exit();
        } else {
            $error = 'Failed to create order. Please try again.';
        }
        $stmt->close();
    }
}

$conn->close();
?>

    <main class="main-content">
        <div class="container">
            <h1 class="my-4">Checkout</h1>

            <div class="row g-4">
                <div class="col-lg-8">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" data-validate="true" id="checkoutForm">
                        <!-- Shipping Address -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Shipping Address</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">First Name</label>
                                            <input type="text" class="form-control" name="first_name" 
                                                   value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" class="form-control" name="last_name" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email" 
                                                   value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Phone Number</label>
                                            <input type="tel" class="form-control" name="phone" 
                                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" name="address" rows="2" required></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">City</label>
                                            <input type="text" class="form-control" name="city" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">State</label>
                                            <input type="text" class="form-control" name="state" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">Postal Code</label>
                                            <input type="text" class="form-control" name="postal_code" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Payment Method</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="cod" value="cod" checked>
                                    <label class="form-check-label" for="cod">
                                        <strong>Cash on Delivery (COD)</strong>
                                        <p class="small text-muted mb-0">Pay when you receive your order</p>
                                    </label>
                                </div>
                                <hr>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="cashfree" value="cashfree">
                                    <label class="form-check-label" for="cashfree">
                                        <strong>Online Payment (Cashfree)</strong>
                                        <p class="small text-muted mb-0">Credit Card, Debit Card, UPI, Net Banking</p>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-check me-2"></i> Place Order
                        </button>
                    </form>
                </div>

                <!-- Order Summary Sidebar -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                                    <div>
                                        <p class="mb-0 small"><?php echo htmlspecialchars($item['name']); ?></p>
                                        <small class="text-muted">x<?php echo $item['quantity']; ?></small>
                                    </div>
                                    <p class="mb-0 fw-bold">
                                        <?php echo formatCurrency(getProductPrice($item) * $item['quantity']); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>

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
                                    <span class="text-success">FREE</span>
                                </div>
                            <?php endif; ?>
                            <div class="summary-row total">
                                <span>Total</span>
                                <span><?php echo formatCurrency($total); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
